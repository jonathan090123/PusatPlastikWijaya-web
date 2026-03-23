<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->after('quantity')->comment('Satuan yang dipilih customer');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->after('product_price')->comment('Satuan yang dipilih customer');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
