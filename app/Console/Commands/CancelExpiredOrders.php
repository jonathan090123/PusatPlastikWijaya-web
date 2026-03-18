<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature   = 'orders:cancel-expired';
    protected $description = 'Cancel unpaid orders older than 24 hours';

    public function handle(): void
    {
        $count = Order::whereIn('status', ['pending', 'waiting_payment'])
            ->where('created_at', '<', now()->subHours(2))
            ->update([
                'status'         => 'expired',
                'status_read_at' => null,
            ]);

        $this->info("Cancelled {$count} expired order(s).");
    }
}
