<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JsonException;

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

    /**
     * The keys PRD US-11 authorizes (SCREEN_SPEC.md §7-5). One list for the store, the writer's
     * validation and the screen that reads them back, so a third key is added in the one place
     * the ruling lives rather than in three.
     *
     * `settings` is the structured preferences' one key: a JSON object in `value` whose own key
     * set is `SETTINGS_KEYS`, so the table stays one-row-per-key and the blob is one preference
     * among them rather than a second storage shape.
     */
    public const KEYS = ['theme', 'failure_estimate', 'settings'];

    /**
     * The keys the `settings` blob carries (SCREEN-024, the D18b slice plan). One list for the
     * writer's validation, the screen that renders them and every reader that pre-fills from
     * them, for the same reason `KEYS` exists.
     *
     * `units` is deliberately absent: `RaceCatalogSlot::distanceLabel()` prints metres because
     * the client does, no source publishes an imperial rendering, and a key with no reader is
     * the drift `KEYS` exists to refuse. `race_risk_thresholds` is held on `ADR-0016`.
     */
    public const SETTINGS_KEYS = ['default_scenario', 'recommendation_aggressiveness', 'stat_target_defaults', 'language'];

    /**
     * The recommendation-aggressiveness values the screen offers. Stored now; `TrainerAdvisor`
     * reads it in a follow-up slice, which the control's own `title` says.
     */
    public const AGGRESSIVENESS = ['conservative', 'balanced', 'aggressive'];

    /**
     * The languages this build can render. The corpus is Global-labelled, so there is one.
     */
    public const LANGUAGES = ['en'];

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

    /**
     * The structured preferences, keyed by `SETTINGS_KEYS`, or an empty array when the Trainer has
     * never saved one.
     *
     * An empty array rather than null, so a reader never tests for a missing offset — the same
     * contract `SetupDraft::read()` states for its own bag. An empty array cannot be stored (the
     * writer drops the row instead, the way `theme`'s follow-the-OS does), so `[]` unambiguously
     * means "not set".
     *
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        $raw = static::get('settings');

        if ($raw === null) {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // A blob that does not decode is not a preference the app can read, and inventing
            // defaults for it would be a second claim about what the Trainer chose. Absence is
            // the honest answer; the row stays so the Trainer can see and overwrite it.
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Writes the whole settings blob, replacing whatever was there.
     *
     * @param  array<string, mixed>  $blob
     */
    public static function putSettings(array $blob): void
    {
        static::put('settings', (string) json_encode($blob, JSON_THROW_ON_ERROR));
    }
}
