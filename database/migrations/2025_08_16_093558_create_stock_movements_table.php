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
        
            $table->dateTime('moved_at');          
            $table->string('type');                
            $table->string('ref_code')->nullable();
        
            $table->integer('initial_qty');        
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('final_qty');          
        
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
