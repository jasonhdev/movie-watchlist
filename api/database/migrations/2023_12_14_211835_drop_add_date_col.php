<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAddDateCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies', function ($table) {
            $table->dropColumn('add_date');
            $table->dropColumn('downloaded');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function ($table) {
            $table->timestamp('add_date')->nullable();
            $table->boolean('downloaded')->default(0);
        });
    }
}
