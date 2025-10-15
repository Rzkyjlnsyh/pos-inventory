<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah deadline di sales_orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('order_date');
        });

        // Tambah deadline di purchase_orders  
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('deadline')->nullable()->after('order_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('deadline');
        });
    }
};