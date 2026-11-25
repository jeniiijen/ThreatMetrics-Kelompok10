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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'tags' => 'array',
        ];
    }

    // Resolve route parameter by primary ID or threat_id code
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->firstOrFail();
        }

        if (is_numeric($value)) {
            return $this->whereKey((int) $value)->firstOrFail();
        }

        return $this->where('threat_id', $value)->firstOrFail();
    }

    protected static function booted(): void
    {
        // Generate default threat_id code when omitted on creation
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