<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'waiting_payment',
            'paid',
            'processing',
            'ready_for_pickup',
            'shipped',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'waiting_payment',
            'paid',
            'processing',
            'shipped',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }
};
