<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'waiting_payment',
            'paid',
            'processing',
            'ready_for_pickup',
            'shipped',
            'completed',
            'cancelled',
            'expired'
        ) NOT NULL DEFAULT 'waiting_payment'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'waiting_payment',
            'paid',
            'processing',
            'ready_for_pickup',
            'shipped',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'waiting_payment'");
    }
};
