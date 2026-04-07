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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('file')->nullable()->after('image');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->string('image')->nullable()->after('body');
            $table->string('file')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('file');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['image', 'file']);
        });
    }
};
