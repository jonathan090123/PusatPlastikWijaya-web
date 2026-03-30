<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE point_histories MODIFY COLUMN type ENUM('earned', 'used', 'refunded') NOT NULL COMMENT 'Tipe: dapat poin, pakai poin, atau dikembalikan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE point_histories MODIFY COLUMN type ENUM('earned', 'used') NOT NULL COMMENT 'Tipe: dapat poin atau pakai poin'");
    }
};
