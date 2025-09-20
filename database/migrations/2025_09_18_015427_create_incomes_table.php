<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->timestamps();
        });

        // Tambahkan column income_total di shifts
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('income_total', 15, 2)->default(0)->after('expense_total');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('income_total');
        });
    }
};