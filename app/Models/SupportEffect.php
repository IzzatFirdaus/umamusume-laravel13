<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupportEffectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportEffect extends Model
{
    /** @use HasFactory<SupportEffectFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'effect_id',
        'name_en',
        'name_ja',
        'calc',
        'symbol',
        'description_en',
        'source_url',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'effect_id' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }
}
