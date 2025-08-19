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
        Schema::create('c_remitted', function (Blueprint $table) {
            $table->id();
            $table->string('record_type')->nullable();
            $table->string('pagibig_acctno')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->decimal('employee_contribution', 10, 2)->nullable();
            $table->decimal('employer_contribution', 10, 2)->nullable();
            $table->string('tin')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('or_no')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_remitted');
    }
};