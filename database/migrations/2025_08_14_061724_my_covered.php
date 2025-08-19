<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('c_remitted', function (Blueprint $table) {
        $table->string('my_covered')->nullable()->after('updated_at');
    });
}

public function down()
{
    Schema::table('c_remitted', function (Blueprint $table) {
        $table->dropColumn('my_covered');
    });
}
};
