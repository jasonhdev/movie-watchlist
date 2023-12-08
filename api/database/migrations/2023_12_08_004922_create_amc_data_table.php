<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmcDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amc_data', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('tomato')->nullable();
            $table->string('imdb')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('trailer_url', 500)->nullable();
            $table->string('rating')->nullable();
            $table->string('year')->nullable();
            $table->string('genre')->nullable();
            $table->string('runtime')->nullable();
            $table->string('amc_title');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amc_data');
    }
}
