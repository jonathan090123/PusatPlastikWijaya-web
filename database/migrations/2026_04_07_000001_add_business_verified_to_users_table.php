<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null = bukan akun bisnis, pending = menunggu verifikasi admin, approved = diverifikasi, rejected = ditolak
            $table->enum('business_verified', ['pending', 'approved', 'rejected'])
                  ->nullable()
                  ->default(null)
                  ->after('business_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('business_verified');
        });
    }
};
