<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('c_remitted', function (Blueprint $table) {
            $table->renameColumn('or_no', 'orno');
        });
    }

    public function down(): void
    {
        Schema::table('c_remitted', function (Blueprint $table) {
            $table->renameColumn('orno', 'or_no');
        });
    }
};