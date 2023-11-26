<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetFieldDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->boolean('watched')->default('0')->change();
            $table->boolean('released')->default('1')->change();
            $table->boolean('featured')->default('0')->change();
            $table->boolean('amc')->default('0')->change();
            
            $table->string('trailer_url', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->boolean('watched')->default(null)->change();
            $table->boolean('released')->default(null)->change();
            $table->boolean('featured')->default(null)->change();
            $table->boolean('amc')->default(null)->change();

            $table->string('trailer_url', 255)->change();
        });
    }
}
