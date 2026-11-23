<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Threat extends Model
{
    protected $table = 'threats';

    protected $fillable = [
        'threat_id',
        'date',
        'user',
        'type',
        'value',
        'tags',
        'tweet',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'tags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Threat $threat) {
            if (!$threat->threat_id) {
                $threat->forceFill([
                    'threat_id' => 'TH-' . str_pad(
                        $threat->id,
                        4,
                        '0',
                        STR_PAD_LEFT
                    ),
                ])->saveQuietly();
            }
        });
    }
}
