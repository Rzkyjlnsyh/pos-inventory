<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();

            $table->enum('method', ['cash', 'transfer', 'split']);
            $table->enum('status', ['dp', 'lunas'])->default('dp');

            // nominal
            $table->decimal('amount', 12, 2)->default(0); // total dibayar
            $table->decimal('cash_amount', 12, 2)->default(0);     // untuk split/cash
            $table->decimal('transfer_amount', 12, 2)->default(0); // untuk split/transfer

            $table->dateTime('paid_at')->nullable();
            $table->string('reference')->nullable(); // no. ref / keterangan bank / nomor nota
            $table->string('proof_path')->nullable(); // path bukti bayar 
            $table->text('note')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
