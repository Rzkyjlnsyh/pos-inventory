<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        
            $table->dateTime('moved_at');          // waktu transaksi (bukan created_at)
            $table->string('type');                // 'INCOMING','OPNAME','POS_SALE','POS_CANCEL','SALE_RETURN'
            $table->string('ref_code')->nullable();// nomor dokumen (IN..., OP..., POS...)
        
            $table->integer('initial_qty');        // stok sebelum transaksi
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('final_qty');          // stok setelah transaksi
        
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
        
            $table->timestamps();
        
            $table->index(['product_id', 'moved_at']);
            $table->index(['type', 'moved_at']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
