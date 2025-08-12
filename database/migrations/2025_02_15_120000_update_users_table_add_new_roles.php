<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom usertype dari varchar menjadi enum dengan 5 role bisnis
        DB::statement("ALTER TABLE users MODIFY COLUMN usertype ENUM('owner', 'finance', 'kepala_toko', 'admin', 'editor') NOT NULL DEFAULT 'editor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ke enum lama (hanya jika tidak ada data dengan role baru)
        DB::statement("ALTER TABLE users MODIFY COLUMN usertype ENUM('owner') NOT NULL DEFAULT 'owner'");
    }
};