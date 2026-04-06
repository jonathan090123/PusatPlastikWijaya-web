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
     * Show payment page — generate or reuse SNAP token.
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

        $order->load(['items.product', 'shippingCost']);

        return view('customer.payment.index', compact('order'));
    }

    /**
     * AJAX: generate a fresh Snap token for the chosen payment method.
     */
    public function getToken(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        if (!in_array($order->status, ['pending', 'waiting_payment'])) {
            return response()->json(['error' => 'Pesanan tidak dapat dibayar.'], 422);
        }

        // Server-side deadline check
        $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);
        if (now()->gt($deadline)) {
            $this->restoreStock($order);
            $this->refundPointsIfNeeded($order);
            $order->update(['status' => 'expired', 'status_read_at' => null]);
            return response()->json(['error' => 'Waktu pembayaran telah habis.'], 422);
        }

        $method = $request->input('method', 'all'); // bca_va | gopay | qris | all

        $enabledPayments = match($method) {
            'bca_va' => ['bank_transfer'],
            'gopay'  => ['gopay', 'qris'],
            default  => ['gopay', 'qris', 'bank_transfer'],
        };

        $bankTransfer = $method === 'bca_va' ? ['bank' => ['bca']] : null;
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

        // Use a unique order_id every time to avoid Midtrans "already taken" error
        if (!$midtransOrderId) {
            $midtransOrderId = 'PPW-' . $order->id . '-' . time();
        }

        $itemDetails = [];

        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id'       => 'PROD-' . $item->product_id,
                'price'    => (int) $item->product_price,
                'quantity' => $item->quantity,
                'name'     => mb_substr($item->product_name, 0, 50),
            ];
        }

        // Discount as negative item
        if ($order->discount_amount > 0) {
            $itemDetails[] = [
                'id'       => 'DISCOUNT',
                'price'    => -1 * (int) $order->discount_amount,
                'quantity' => 1,
                'name'     => 'Diskon Voucher',
            ];
        }

        // Points discount as negative item
        if ($order->points_discount > 0) {
            $itemDetails[] = [
                'id'       => 'POINTS',
                'price'    => -1 * (int) $order->points_discount,
                'quantity' => 1,
                'name'     => 'Diskon Poin',
            ];
        }

        // Shipping fee
        if ($order->shipping_fee > 0) {
            $itemDetails[] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $order->shipping_fee,
                'quantity' => 1,
                'name'     => $order->shipping_name,
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'item_details'  => $itemDetails,
            'customer_details' => [
                'first_name' => $order->user->name,
                'email'      => $order->user->email,
                'phone'      => $order->recipient_phone,
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone'      => $order->recipient_phone,
                    'address'    => $order->shipping_address,
                ],
            ],
            'enabled_payments' => $enabledPayments,
            'callbacks' => [
                'finish' => route('payment.finish', $order),
            ],
        ];

        if ($bankTransfer) {
            $params['bank_transfer'] = $bankTransfer;
        }

        return Snap::getSnapToken($params);
    }

    /**
     * Finish callback — redirect from Midtrans after payment.
     * Actively checks transaction status via Midtrans API (needed for localhost).
     */
    public function finish(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Already paid? Skip API check.
        $order->load('payment');
        if ($order->payment && $order->payment->isPaid()) {
            return redirect()->route('orders.show', $order)
                ->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
        }

        // Check transaction status directly from Midtrans API
        // Use the stored midtrans_order_id (which may differ from invoice_number)
        $midtransOrderId = data_get($order->payment, 'payment_detail.midtrans_order_id', $order->invoice_number);

        try {
            $status = Transaction::status($midtransOrderId);
            $transactionStatus = $status->transaction_status ?? null;
            $paymentType       = $status->payment_type ?? null;
            $fraudStatus       = $status->fraud_status ?? null;
            $transactionId     = $status->transaction_id ?? null;

            if ($transactionStatus) {
                $detail = array_merge(
                    json_decode(json_encode($status), true) ?? [],
                    ['midtrans_order_id' => $midtransOrderId]
                );

                $payment = Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'payment_type'       => $paymentType,
                        'transaction_id'     => $transactionId,
                        'transaction_status' => $transactionStatus,
                        'gross_amount'       => $status->gross_amount ?? $order->total,
                        'payment_detail'     => $detail,
                    ]
                );

                $isPaid = ($transactionStatus === 'settlement')
                       || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

                if ($isPaid) {
                    $this->markAsPaid($order, $payment);
                    return redirect()->route('orders.show', $order)
                        ->with('payment_success', true);
                }

                if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    if (in_array($order->status, ['pending', 'waiting_payment'])) {
                        $this->restoreStock($order);
                        $this->refundPointsIfNeeded($order);
                        $order->update(['status' => 'cancelled', 'status_read_at' => null]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Midtrans status check failed: ' . $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('info', 'Status pembayaran sedang diverifikasi.');
    }

    /**
     * Midtrans webhook/notification handler (no auth, no CSRF).
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
                    'payment_detail'     => json_decode($notification->getResponse(), true),
                ]
            );

            // Map Midtrans status → order status
            if ($transactionStatus === 'capture') {
                // Card payment — check fraud status
                if ($fraudStatus === 'accept') {
                    $this->markAsPaid($order, $payment);
                }
            } elseif ($transactionStatus === 'settlement') {
                // Non-card payment confirmed
                $this->markAsPaid($order, $payment);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $payment->update(['transaction_status' => $transactionStatus]);
                // Only cancel if not yet processing (stock not yet deducted)
                if (in_array($order->status, ['pending', 'waiting_payment'])) {
                    $this->restoreStock($order); // return reserved stock
                    $this->refundPointsIfNeeded($order);
                    $order->update([
                        'status'         => 'cancelled',
                        'status_read_at' => null,
                    ]);
                }
            } elseif ($transactionStatus === 'pending') {
                $payment->update(['transaction_status' => 'pending']);
                if ($order->status === 'pending') {
                    $order->update([
                        'status'         => 'waiting_payment',
                        'status_read_at' => null,
                    ]);
                }
            }

            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    private function markAsPaid(Order $order, Payment $payment): void
    {
        $payment->update([
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // Skip if already processing or beyond (idempotent)
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
