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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id')->unique();
            $table->foreignId('category_id')->nullable()->after('sku')->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('image_path');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['sku', 'category_id', 'is_active']);
        });
    }
};