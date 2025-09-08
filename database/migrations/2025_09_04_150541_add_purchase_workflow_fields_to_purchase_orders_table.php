<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add purchase type field
            $table->enum('purchase_type', ['kain', 'produk_jadi'])->after('supplier_id');
            
            // Add workflow tracking fields
            $table->timestamp('payment_at')->nullable()->after('received_by');
            $table->unsignedBigInteger('payment_by')->nullable()->after('payment_at');
            
            $table->timestamp('kain_diterima_at')->nullable()->after('payment_by');
            $table->unsignedBigInteger('kain_diterima_by')->nullable()->after('kain_diterima_at');
            
            $table->timestamp('printing_at')->nullable()->after('kain_diterima_by');
            $table->unsignedBigInteger('printing_by')->nullable()->after('printing_at');
            
            $table->timestamp('jahit_at')->nullable()->after('printing_by');
            $table->unsignedBigInteger('jahit_by')->nullable()->after('jahit_at');
            
            $table->timestamp('selesai_at')->nullable()->after('jahit_by');
            $table->unsignedBigInteger('selesai_by')->nullable()->after('selesai_at');
            
            // Add foreign key constraints
            $table->foreign('payment_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('kain_diterima_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('printing_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('jahit_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('selesai_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['payment_by']);
            $table->dropForeign(['kain_diterima_by']);
            $table->dropForeign(['printing_by']);
            $table->dropForeign(['jahit_by']);
            $table->dropForeign(['selesai_by']);
            
            // Drop columns
            $table->dropColumn([
                'purchase_type',
                'payment_at',
                'payment_by',
                'kain_diterima_at',
                'kain_diterima_by',
                'printing_at',
                'printing_by',
                'jahit_at',
                'jahit_by',
                'selesai_at',
                'selesai_by'
            ]);
        });
    }
};