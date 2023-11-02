<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('descripton')->nullable();
            $table->string('tomato')->nullable();
            $table->string('imdb')->nullable();
            $table->string('poster_url')->nullable(); // originally 'image'
            $table->string('trailer_url')->nullable(); // originally 'trailer'
            $table->string('rating')->nullable();
            $table->string('year')->nullable();
            $table->string('genre')->nullable();
            $table->string('runtime')->nullable();
            $table->string('services')->nullable();
            $table->boolean('watched');
            $table->boolean('released');
            $table->boolean('featured');
            $table->boolean('amc');
            $table->boolean('downloaded');
            $table->timestamp('add_date')->nullable();
            $table->timestamp('watched_date')->nullable();
            $table->timestamp('release_date')->nullable();
            $table->string('search_term'); // originally 'search'
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
        Schema::dropIfExists('movies');
    }
}
