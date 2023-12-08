<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmcData extends Model
{
    use HasFactory;

    protected $table = 'amc_data';
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
        'release_date',
        'amc_title',
    ];
}
