<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('customer_type', ['personal', 'business'])->default('personal')->after('address');
            $table->string('business_name', 255)->nullable()->after('customer_type');
            $table->string('business_type', 100)->nullable()->after('business_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['customer_type', 'business_name', 'business_type']);
        });
    }
};
