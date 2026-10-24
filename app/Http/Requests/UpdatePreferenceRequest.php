<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Preference;
use App\Services\ScenarioCaps;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The preferences PRD US-11 authorizes, and nothing else.
 *
 * The key set is a contract rather than a free-form blob, so an unrecognised key is refused
 * instead of dropped: `validated()` would silently discard it and the form that sent it would go
 * on believing the preference had been stored. The migration's own docblock says a third key is a
 * PRD change, and this is the boundary that enforces that.
 *
 * `theme` carries three stored values plus the absence of a row: light, dark, and the
 * standalone `eva01` palette (owner request 2026-10-24). The absence is follow-the-OS, which
 * the controller stores as no row at all. `failure_estimate` is the on/off pair, off by
 * default (`ADR-0001` §3).
 *
 * `settings` is the structured preferences' one key (SCREEN-024, the D18b slice plan): one JSON
 * blob in one row, whose own key set is `Preference::SETTINGS_KEYS` and whose values are
 * validated here rather than by a second layer. A nested key that is not in that list is refused
 * by the same `after` check the top level uses, because `validated()` would discard it just as
 * silently as it discards an unknown top-level key.
 */
class UpdatePreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // local-only tool; no auth surface (ARCHITECTURE §8)
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var list<string> $statOrder */
        $statOrder = array_values((array) config('scenarios.stat_order'));

        return [
            // `present` plus `nullable` because follow-the-OS has to be writable. The form sends
            // it as `theme=`, and the framework's ConvertEmptyStringsToNull middleware turns that
            // into null before this list is read, so null is the stated answer and a key nobody
            // sent is not. `required` would reject follow-the-OS as if it were missing.
            'theme' => ['present', 'nullable', Rule::in(['light', 'dark', 'eva01'])],
            'failure_estimate' => ['present', Rule::in(['off', 'on'])],
            // `present` for the same reason theme is, and the screen sends the blob it renders.
            // Null is the stated answer for "store no structured preferences", which the
            // controller resolves by dropping the row rather than by storing an empty object.
            'settings' => ['present', 'nullable', 'array'],
            // The value must be a key the matrix actually composes: a stored scenario the config
            // no longer names would pre-select a card that does not exist.
            'settings.default_scenario' => ['nullable', 'string', Rule::in(array_keys((array) config('scenarios.scenarios')))],
            'settings.recommendation_aggressiveness' => ['nullable', Rule::in(Preference::AGGRESSIVENESS)],
            // Exactly the matrix's stats, by its own names: a partial map would look like a
            // complete set of defaults, and an unknown key would be a stat this tool does not hold.
            'settings.stat_target_defaults' => ['nullable', 'array', static function (string $attribute, mixed $value, Closure $fail) use ($statOrder): void {
                if (! is_array($value)) {
                    return;
                }

                $unknown = array_diff(array_keys($value), $statOrder);
                $missing = array_diff($statOrder, array_keys($value));

                if ($unknown !== [] || $missing !== []) {
                    $fail("Stat-target defaults carry exactly the stat matrix's stats, by its own names.");
                }
            }],
            // The engine ceiling, not a scenario's runtime cap: the defaults pre-fill a form whose
            // own clamp is per-run, so a number the tightest scenario would refuse is still a
            // number this preference is allowed to hold.
            'settings.stat_target_defaults.*' => ['nullable', 'integer', 'min:0', 'max:'.ScenarioCaps::hardCap()],
            // One language, because the corpus is Global-labelled. The control is a disabled
            // single-option select, so this rule exists for the boundary rather than for the form.
            'settings.language' => ['nullable', Rule::in(Preference::LANGUAGES)],
            'settings.dashboard_layout' => ['nullable', Rule::in(['compact', 'standard', 'wide', 'focused', 'bento', 'wide_bento'])],
            'settings.dashboard_hidden_cards' => ['nullable', 'array'],
            'settings.dashboard_hidden_cards.*' => ['string', Rule::in(['active_career', 'next_best_action', 'quick_actions', 'recent_veterans', 'data_status'])],
        ];
    }

    /**
     * The retired next-best-action card id is tolerated, not refused: a Trainer whose stored
     * hidden-cards list predates the merge would otherwise have every later save bounced by a
     * value this screen no longer offers. Stripped here, explicitly, rather than silently by
     * `validated()`; every other unknown id is still refused below.
     */
    protected function prepareForValidation(): void
    {
        $settings = $this->input('settings');

        if (is_array($settings) && is_array($settings['dashboard_hidden_cards'] ?? null)) {
            $settings['dashboard_hidden_cards'] = array_values(array_filter(
                $settings['dashboard_hidden_cards'],
                static fn (mixed $id): bool => $id !== 'next_best_action',
            ));

            $this->merge(['settings' => $settings]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unknown = array_diff(array_keys($this->all()), Preference::KEYS, ['_token', '_method']);

            if ($unknown !== []) {
                $validator->errors()->add(
                    'preferences',
                    'This tool stores only the preferences its key list authorizes.',
                );
            }

            $settings = $this->input('settings');

            if (is_array($settings)) {
                $unknownSettings = array_diff(array_keys($settings), Preference::SETTINGS_KEYS);

                if ($unknownSettings !== []) {
                    $validator->errors()->add(
                        'settings',
                        'The settings blob carries only the keys the preference matrix lists.',
                    );
                }
            }
        });
    }
}
