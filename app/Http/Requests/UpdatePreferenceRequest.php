<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Preference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The two UI preferences PRD US-11 authorizes, and nothing else.
 *
 * The key set is a contract rather than a free-form blob, so an unrecognised key is refused
 * instead of dropped: `validated()` would silently discard it and the form that sent it would go
 * on believing the preference had been stored. The migration's own docblock says a third key is a
 * PRD change, and this is the boundary that enforces that.
 *
 * `theme` carries three authorized values and the third is expressed as no value at all, which
 * the controller stores as the absence of a row. `failure_estimate` is the on/off pair, off by
 * default (`ADR-0001` §3).
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
        return [
            // `present` plus `nullable` because follow-the-OS has to be writable. The form sends
            // it as `theme=`, and the framework's ConvertEmptyStringsToNull middleware turns that
            // into null before this list is read, so null is the stated answer and a key nobody
            // sent is not. `required` would reject the third authorized value as if it were missing.
            'theme' => ['present', 'nullable', Rule::in(['light', 'dark'])],
            'failure_estimate' => ['present', Rule::in(['off', 'on'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unknown = array_diff(array_keys($this->all()), Preference::KEYS, ['_token', '_method']);

            if ($unknown !== []) {
                $validator->errors()->add(
                    'preferences',
                    'This tool stores only the two preferences PRD US-11 authorizes.',
                );
            }
        });
    }
}
