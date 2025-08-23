<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'approved', 'posted'])->default('draft');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // yang buat
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // pengecek
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // yang approve
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('system_qty'); // qty di sistem
            $table->integer('actual_qty'); // qty hasil cek lapangan
            $table->integer('difference')->default(0); // selisih
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
