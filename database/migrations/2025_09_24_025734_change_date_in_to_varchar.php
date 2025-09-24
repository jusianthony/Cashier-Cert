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
        Schema::table('c_remitted', function (Blueprint $table) {
            $table->string('birthdate', 255)->nullable()->change();
            $table->string('date', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_remitted', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->change();
            $table->date('date')->nullable()->change();
        });
    }
};
