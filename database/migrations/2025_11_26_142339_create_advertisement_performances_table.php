<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('advertisement_performances', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['chat', 'followup', 'closing']);
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->index(['date', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('advertisement_performances');
    }
};