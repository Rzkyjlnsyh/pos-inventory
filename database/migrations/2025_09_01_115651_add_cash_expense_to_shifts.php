<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('cash_total', 15, 2)->default(0)->after('initial_cash');
            $table->decimal('expense_total', 15, 2)->default(0)->after('cash_total');
        });
    }

    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['cash_total', 'expense_total']);
        });
    }
};