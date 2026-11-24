<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ioc extends Model
{
    protected $fillable = [
        'source_key',
        'type',
        'value',
        'source_user',
        'source_tweet',
        'tags',
        'category',
        'severity',
        'status',
        'source_date',
        'source',
    ];

    protected $casts = [
        'tags'        => 'array',
        'source_date' => 'datetime',
    ];
}