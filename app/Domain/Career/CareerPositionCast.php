<?php

declare(strict_types=1);

namespace App\Domain\Career;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

/**
 * The Eloquent cast for `training_runs.career_position`, so the column reads as a `CareerPosition`
 * and writes as its stored shape.
 *
 * The value object itself cannot be its own cast: Laravel constructs the cast class with no
 * arguments, and a value object whose constructor requires a year, a month and a phase cannot be
 * constructed empty. This class is the adapter that was missing, and it holds no state.
 *
 * @implements CastsAttributes<CareerPosition|null, mixed>
 */
final class CareerPositionCast implements CastsAttributes
{
    public function get(mixed $model, string $key, mixed $value, array $attributes): ?CareerPosition
    {
        if ($value === null) {
            return null;
        }

        $payload = is_array($value) ? $value : json_decode((string) $value, true);

        return CareerPosition::fromArray(is_array($payload) ? $payload : []);
    }

    public function set(mixed $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CareerPosition) {
            return json_encode($value->toArray());
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException(
                'A career position is stored as itself or as its payload; ['.get_debug_type($value).'] is neither.',
            );
        }

        return json_encode(CareerPosition::fromArray($value)->toArray());
    }
}
