<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movies extends Model
{
    use HasFactory;

    protected $table = 'movies';
    protected $fillable = [
        'title',
        'description',
        'tomato',
        'imdb',
        'poster_url', // originally 'image'
        'trailer_url', // originally 'trailer'
        'rating',
        'year',
        'genre',
        'runtime',
        'services',
        'watched',
        'released',
        'featured',
        'amc',
        // downloaded,
        'add_date',
        'watched_date',
        'release_date',
        'search_term', // originally 'search'
    ];
}
