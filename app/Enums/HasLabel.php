<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * Resolves a Trainer-facing label for a backed enum case.
 *
 * Keys on the case NAME, not the backing value, so the value stays free to be
 * the machine token that form fields and query strings submit.
 */
trait HasLabel
{
    public function label(): string
    {
        $key = 'uma.'.Str::snake(class_basename(static::class)).'.'.$this->name;
        $label = trans($key);

        return $label === $key ? Str::headline($this->name) : $label;
    }
}
