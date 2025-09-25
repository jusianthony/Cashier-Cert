<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gssremitted', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('bpno')->unique()->nullable(); // BPNO
            $table->string('last_name')->nullable(); // LastName
            $table->string('first_name')->nullable(); // FirstName
            $table->string('mi')->nullable(); // Middle Initial (nullable)
            $table->string('basic_monthly_salary', 12, 2)->nullable(); // Basic Monthly Salary
            $table->string('effectivity_date')->nullable(); // Effectivity Date
            $table->decimal('ps', 12, 2)->nullable();   // PS
            $table->decimal('gs', 12, 2)->nullable();   // GS
            $table->decimal('ec', 12, 2)->nullable();   // EC
            $table->decimal('consoloan', 12, 2)->nullable(); // CONSOLOAN
            $table->decimal('emrglyn', 12, 2)->nullable();   // EMRGYLN
            $table->decimal('plreg', 12, 2)->nullable();     // PLREG
            $table->decimal('gfal', 12, 2)->nullable();      // GFAL
            $table->decimal('mpl', 12, 2)->nullable();       // MPL
            $table->decimal('mpl_lite', 12, 2)->nullable();  // MPL_LITE
            $table->string('orno')->nullable(); // OR NO
            $table->string('datepaid')->nullable(); // DATE PAID
            $table->string('my_covered')->nullable(); // COVERED DATE
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gssremitted');
    }
};
