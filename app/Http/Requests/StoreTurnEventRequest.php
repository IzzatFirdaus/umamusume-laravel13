<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Records one event choice on the turn the Trainer says it happened (ADR-0003 turn_events).
 *
 * The TurnEvent model is the payload: `event_type` carries the source (the four cases the
 * enum defines for choice events), `source_name` the event's name, `choice_index`/`choice_label`
 * the option taken, and `origin_note` the recorded outcome the Trainer typed. The structured
 * `deltas` shapes (purchase, burst, rank, fatigue, friendship) have their own Requests and
 * payloads; this one stores no invented keys.
 */
class StoreTurnEventRequest extends FormRequest
{
    /**
     * The four source cases a choice event can carry. Failure and Inheritance write through
     * their own screens and routes, so they are not accepted here.
     */
    public const SOURCES = ['Character', 'SupportCard', 'Group', 'Scenario'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'turn' => ['required', 'integer', 'min:1', 'max:72'],
            'event_type' => ['required', Rule::in(self::SOURCES)],
            'source_name' => ['required', 'string', 'max:100'],
            'choice_index' => ['nullable', 'integer', 'min:0'],
            'choice_label' => ['required', 'string', 'max:200'],
            'support_card_name' => ['nullable', 'string', 'max:100'],
            'bond_delta' => ['nullable', 'integer'],
            'origin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'choice_label.required' => 'Choose or name the choice you recorded.',
            'event_type.in' => 'The source must be Character, Support Card, Group, or Scenario.',
        ];
    }
}
