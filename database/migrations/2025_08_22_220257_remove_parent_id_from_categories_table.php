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
        Schema::table('categories', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['parent_id']);
            // Hapus kolom parent_i
            $table->dropColumn('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Tambahkan kembali kolom parent_id
            $table->unsignedBigInteger('parent_id')->nullable()->after('name');
            // Tambahkan kembali foreign key constraint
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
        });
    }
};
