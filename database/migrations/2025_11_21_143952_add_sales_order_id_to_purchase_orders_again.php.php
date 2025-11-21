<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek dulu apakah column sudah ada
        if (!Schema::hasColumn('purchase_orders', 'sales_order_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('sales_order_id')
                      ->nullable()
                      ->after('created_by')
                      ->constrained('sales_orders')
                      ->onDelete('set null');
            });
            \Log::info('Added sales_order_id to purchase_orders table');
        } else {
            \Log::info('sales_order_id column already exists in purchase_orders');
        }
    }

    public function down(): void
    {
        // Jangan drop column di down(), biarkan aman
        \Log::info('Down migration skipped for sales_order_id safety');
    }
};