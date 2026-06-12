<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$clientKey    = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Tampilkan halaman pembayaran.
     * Jika pelanggan refresh setelah bayar, cek status Midtrans langsung.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'waiting_payment'])) {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Pesanan ini tidak memerlukan pembayaran.');
        }

        // Server-side deadline check: auto-expire if time has passed
        $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);
        if (now()->gt($deadline)) {
            $this->restoreStock($order);
            $this->refundPointsIfNeeded($order);
            $order->update(['status' => 'expired', 'status_read_at' => null]);
            return redirect()->route('orders.show', $order)
                ->with('info', 'Waktu pembayaran telah habis. Aktifkan kembali pesanan untuk melanjutkan.');
        }

        $order->load(['items.product', 'shippingCost', 'payment']);

        // Auto-detect: cek kembali status Midtrans saat pelanggan refresh halaman
        if ($order->payment && !$order->payment->isPaid()) {
            if ($this->checkMidtransAndMarkPaid($order, 'Auto status check on payment page load')) {
                return redirect()->route('orders.show', $order)
                    ->with('payment_success', true);
            }
        }

        return view('customer.payment.index', compact('order'));
    }

    /**
     *  cek status pembayaran saat popup Snap ditutup.
     */
    public function statusCheck(Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            return response()->json(['paid' => false]);
        }

        $order->load('payment');

        if ($order->payment && $order->payment->isPaid()) {
            return response()->json(['paid' => true, 'redirect' => route('orders.show', $order)]);
        }

        if ($this->checkMidtransAndMarkPaid($order, 'Status check AJAX')) {
            return response()->json(['paid' => true, 'redirect' => route('orders.show', $order)]);
        }

        return response()->json(['paid' => false]);
    }

    /**
     * generate Snap token Midtrans untuk metode pembayaran yang dipilih.
     */
    public function getToken(Request $request, Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Sesi tidak valid. Silakan login kembali.'], 403);
        }

        if (!in_array($order->status, ['pending', 'waiting_payment'])) {
            return response()->json(['error' => 'Pesanan tidak dapat dibayar.'], 422);
        }

        if ($this->expireOrderIfPaymentTimePassed($order)) {
            return response()->json(['error' => 'Waktu pembayaran telah habis.'], 422);
        }

        $method = $request->input('method', 'all'); // bca_va | gopay | qris | all
        $enabledPayments = $this->getEnabledPayments($method);
        $bankTransfer = $this->getBankTransferOptions($method);
        $midtransOrderId = 'PPW-' . $order->id . '-' . time();

        $snapToken = null;
        try {
            $snapToken = $this->createSnapToken($order, $enabledPayments, $bankTransfer, $midtransOrderId);
        } catch (\Exception $e) {
            Log::error('Snap token error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat token pembayaran. Coba lagi.'], 500);
        }

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'snap_token'         => $snapToken,
                'gross_amount'       => $order->total,
                'transaction_status' => 'pending',
                'payment_detail'     => ['midtrans_order_id' => $midtransOrderId],
            ]
        );

        if ($order->status === 'pending') {
            $order->update(['status' => 'waiting_payment', 'status_read_at' => null]);
        }

        return response()->json(['token' => $snapToken]);
    }

    private function createSnapToken(Order $order, array $enabledPayments = ['gopay', 'qris'], ?array $bankTransfer = null, ?string $midtransOrderId = null): string
    {
        $order->load(['items.product', 'user']);

        if (!$midtransOrderId) {
            $midtransOrderId = 'PPW-' . $order->id . '-' . time();
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'item_details'     => $this->getSnapItemDetails($order),
            'customer_details' => $this->getSnapCustomerDetails($order),
            'enabled_payments' => $enabledPayments,
            'callbacks'        => ['finish' => route('payment.finish', $order)],
        ];

        if ($bankTransfer) {
            $params['bank_transfer'] = $bankTransfer;
        }

        return Snap::getSnapToken($params);
    }

    private function getSnapItemDetails(Order $order): array
    {
        $details = [];

        foreach ($order->items as $item) {
            $details[] = [
                'id'       => 'PROD-' . $item->product_id,
                'price'    => (int) $item->product_price,
                'quantity' => $item->quantity,
                'name'     => mb_substr($item->product_name, 0, 50),
            ];
        }

        if ($order->discount_amount > 0) {
            $details[] = [
                'id'       => 'DISCOUNT',
                'price'    => -1 * (int) $order->discount_amount,
                'quantity' => 1,
                'name'     => 'Diskon Voucher',
            ];
        }

        if ($order->points_discount > 0) {
            $details[] = [
                'id'       => 'POINTS',
                'price'    => -1 * (int) $order->points_discount,
                'quantity' => 1,
                'name'     => 'Diskon Poin',
            ];
        }

        if ($order->shipping_fee > 0) {
            $details[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $order->shipping_fee,
                'quantity' => 1,
                'name'     => $order->shipping_name,
            ];
        }

        return $details;
    }

    private function getSnapCustomerDetails(Order $order): array
    {
        return [
            'first_name' => $order->user->name,
            'email'      => $order->user->email,
            'phone'      => $order->recipient_phone,
            'shipping_address' => [
                'first_name' => $order->recipient_name,
                'phone'      => $order->recipient_phone,
                'address'    => $order->shipping_address,
            ],
        ];
    }

    /**
     * Finish callback Midtrans.
     * Redirect ke order dan cek status langsung jika diperlukan.
     */
    public function finish(Request $request, Order $order)
    {
        if (!Auth::check() || $order->user_id !== Auth::id()) {
            return redirect()->route('orders.index');
        }

        // Already paid? Skip API check.
        $order->load('payment');
        if ($order->payment && $order->payment->isPaid()) {
            return redirect()->route('orders.show', $order)
                ->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
        }

        // Check transaction status directly from Midtrans API

        $midtransOrderId = data_get($order->payment, 'payment_detail.midtrans_order_id', $order->invoice_number);

        try {
            $status = Transaction::status($midtransOrderId);
            $payment = $this->saveMidtransPayment($order, $status, $midtransOrderId);
            $this->processTransactionStatus($order, $payment, $status->transaction_status ?? null, $status->fraud_status ?? null);

            if ($payment->isPaid()) {
                return redirect()->route('orders.show', $order)
                    ->with('payment_success', true);
            }
        } catch (\Exception $e) {
            Log::warning('Midtrans status check failed: ' . $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('info', 'Status pembayaran sedang diverifikasi.');
    }

    /**
     * Handler webhook Midtrans.
     * Terima notifikasi server-to-server dan update order/payment.
     */
    public function webhook(Request $request)
    {
        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $paymentType       = $notification->payment_type;
            $orderId           = $notification->order_id;  // PPW-{id}-{time} format
            $transactionId     = $notification->transaction_id;
            $fraudStatus       = $notification->fraud_status ?? null;

            Log::info('Midtrans Webhook', [
                'order_id'    => $orderId,
                'status'      => $transactionStatus,
                'type'        => $paymentType,
                'fraud'       => $fraudStatus,
            ]);

            // Support both new format (PPW-{id}-{time}) and legacy (invoice_number)
            $order = null;
            if (preg_match('/^PPW-(\d+)-/', $orderId, $m)) {
                $order = Order::find((int) $m[1]);
            }
            if (!$order) {
                $order = Order::where('invoice_number', $orderId)->first();
            }

            if (!$order) {
                Log::warning('Midtrans webhook: order not found', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Don't process if order already completed/cancelled
            if (in_array($order->status, ['completed', 'cancelled'])) {
                return response()->json(['message' => 'OK']);
            }

            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_type'       => $paymentType,
                    'transaction_id'     => $transactionId,
                    'transaction_status' => $transactionStatus,
                    'gross_amount'       => $notification->gross_amount,
                    'payment_detail'     => json_decode(json_encode($notification->getResponse()), true),
                ]
            );

            $this->processTransactionStatus($order, $payment, $transactionStatus, $fraudStatus);

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function getMidtransOrderId(Order $order): ?string
    {
        return data_get($order->payment, 'payment_detail.midtrans_order_id');
    }

    private function isPaidTransaction(?string $transactionStatus, ?string $fraudStatus): bool
    {
        return $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');
    }

    private function checkMidtransAndMarkPaid(Order $order, string $logContext = ''): bool
    {
        $midtransOrderId = $this->getMidtransOrderId($order);
        if (!$midtransOrderId) {
            return false;
        }

        try {
            $tx = Transaction::status($midtransOrderId);
            if ($this->isPaidTransaction($tx->transaction_status ?? null, $tx->fraud_status ?? null)) {
                $this->markAsPaid($order, $order->payment);
                return true;
            }
        } catch (\Exception $e) {
            Log::info($logContext . ': ' . $e->getMessage());
        }

        return false;
    }

    private function expireOrderIfPaymentTimePassed(Order $order): bool
    {
        $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);
        if (!now()->gt($deadline)) {
            return false;
        }

        $this->restoreStock($order);
        $this->refundPointsIfNeeded($order);
        $order->update(['status' => 'expired', 'status_read_at' => null]);

        return true;
    }

    private function getEnabledPayments(string $method): array
    {
        return match ($method) {
            'bca_va' => ['bank_transfer'],
            'gopay' => ['gopay', 'qris'],
            default => ['gopay', 'qris', 'bank_transfer'],
        };
    }

    private function getBankTransferOptions(string $method): ?array
    {
        return $method === 'bca_va' ? ['bank' => ['bca']] : null;
    }

    private function saveMidtransPayment(Order $order, $status, string $midtransOrderId): Payment
    {
        $transactionStatus = $status->transaction_status ?? null;
        return Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_type' => $status->payment_type ?? null,
                'transaction_id' => $status->transaction_id ?? null,
                'transaction_status' => $transactionStatus,
                'gross_amount' => $status->gross_amount ?? $order->total,
                'payment_detail' => array_merge(
                    json_decode(json_encode($status), true) ?? [],
                    ['midtrans_order_id' => $midtransOrderId]
                ),
            ]
        );
    }

    private function processTransactionStatus(Order $order, Payment $payment, ?string $transactionStatus, ?string $fraudStatus): void
    {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $this->markAsPaid($order, $payment);
            }

            return;
        }

        if ($transactionStatus === 'settlement') {
            $this->markAsPaid($order, $payment);
            return;
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $payment->update(['transaction_status' => $transactionStatus]);

            if (in_array($transactionStatus, ['cancel', 'deny']) && in_array($order->status, ['pending', 'waiting_payment'])) {
                $this->restoreStock($order);
                $this->refundPointsIfNeeded($order);
                $order->update([
                    'status' => 'cancelled',
                    'status_read_at' => null,
                ]);
            }

            return;
        }

        if ($transactionStatus === 'pending') {
            $payment->update(['transaction_status' => 'pending']);
            if ($order->status === 'pending') {
                $order->update([
                    'status' => 'waiting_payment',
                    'status_read_at' => null,
                ]);
            }
        }
    }

    private function markAsPaid(Order $order, Payment $payment): void
    {
        $payment->update([
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        // Skip if already processing 
        if (in_array($order->status, ['processing', 'ready_for_pickup', 'shipped', 'completed'])) {
            return;
        }

        // Stock was already reserved at checkout — no deduction needed here
        $order->update([
            'status'         => 'processing',
            'status_read_at' => null,
        ]);
    }

    private function refundPointsIfNeeded(Order $order): void
    {
        if (($order->points_used ?? 0) <= 0) return;

        $alreadyRefunded = PointHistory::where('order_id', $order->id)
            ->where('type', 'refunded')
            ->exists();
        if ($alreadyRefunded) return;

        $order->loadMissing('user');
        $order->user->increment('points', $order->points_used);

        PointHistory::create([
            'user_id'     => $order->user_id,
            'order_id'    => $order->id,
            'type'        => 'refunded',
            'amount'      => $order->points_used,
            'description' => 'Poin dikembalikan (pesanan ' . $order->invoice_number . ' dibatalkan/kadaluarsa)',
        ]);
    }

    private function restoreStock(Order $order): void
    {
        $order->load('items.product.productUnits');
        foreach ($order->items as $item) {
            if (!$item->product) continue;
            $conv = 1;
            if ($item->unit && $item->unit !== $item->product->unit) {
                $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                if ($pu) $conv = (int) $pu->conversion_value;
            }
            $item->product->increment('stock', $item->quantity * $conv);
        }
    }
}
