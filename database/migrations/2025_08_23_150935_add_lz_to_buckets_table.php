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
        Schema::table('buckets', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->unsignedBigInteger('limit_size')->default(0)->comment('Max size in bytes; 0 = unlimited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buckets', function (Blueprint $table) {
            $table->dropColumn('limit_size');
            $table->dropColumn('description');
        });
    }
};
