<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One Trainer UI preference, one row per key (PRD US-11).
 *
 * A key-value store on purpose: the set of preferences is a contract that changes
 * with rulings, not a schema, and there is no `user_id` because a single-Trainer
 * local tool has no accounts to scope one to (PRD §6 non-goal 1).
 *
 * SQLite is the only store. PRD §6 non-goal 12 cuts browser-side authoritative
 * data, so nothing here may be mirrored to `localStorage` (research D-104) —
 * reading a preference from the browser would reintroduce the second source of
 * truth this table exists to end.
 *
 * @property string $key
 * @property string $value
 */
#[Fillable(['key', 'value'])]
class Preference extends Model
{
    /** @use HasFactory<PreferenceFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'key';

    /**
     * The stored value for a key, or null when the Trainer has never set it.
     * Absence is the default: a preference that has no row is not a preference
     * holding an empty string.
     */
    public static function get(string $key): ?string
    {
        $value = static::query()->whereKey($key)->value('value');

        return $value === null ? null : (string) $value;
    }

    public static function put(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
