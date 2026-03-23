<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('unit', 20)->comment('Nama satuan: BAL, IKT, DOS, PRS, ROL, PAK, etc.');
            $table->integer('conversion_value')->comment('Jumlah satuan dasar per 1 satuan ini');
            $table->decimal('price', 12, 2)->comment('Harga jual untuk satuan ini');
            $table->timestamps();

            $table->unique(['product_id', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
