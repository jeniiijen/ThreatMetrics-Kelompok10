<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalystNote extends Model
{
    protected $table = 'analyst_notes';

    protected $fillable = [
        'author',
        'content_html',
    ];
}