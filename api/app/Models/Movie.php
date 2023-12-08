<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies';
    protected $fillable = [
        'title',
        'description',
        'tomato',
        'imdb',
        'poster_url',
        'trailer_url',
        'rating',
        'year',
        'genre',
        'runtime',
        'services',
        'watched',
        'released',
        'featured',
        'amc',
        'add_date',
        'watched_date',
        'release_date',
        'search_term',
    ];
}
