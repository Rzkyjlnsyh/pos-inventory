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
    public function up()
    {
        // Ubah kolom status menjadi ENUM dengan nilai baru
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('pending', 'request_kain', 'proses_jahit', 'jadi', 'diterima_toko', 'di proses', 'selesai') DEFAULT 'pending'");
    }

    public function down()
    {
        // Kembalikan ke ENUM lama untuk rollback
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM('draft', 'pending', 'di proses', 'selesai') DEFAULT 'pending'");
    }
};
