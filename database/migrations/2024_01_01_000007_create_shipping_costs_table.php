<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shipping_costs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama metode pengiriman');
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->comment('Biaya tetap pengiriman');
            $table->string('estimation')->nullable()->comment('Estimasi waktu pengiriman, misal: 1-2 hari');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_costs');
    }
};
