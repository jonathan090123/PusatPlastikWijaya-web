<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 50)->nullable()->unique()->after('category_id');
            $table->string('unit', 20)->default('PCS')->after('slug')->comment('Satuan dasar: PCS, KG, PAK, ROL, BAL, SAP, BH, P100');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'unit']);
        });
    }
};
