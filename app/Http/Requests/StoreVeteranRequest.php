<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * The Save Veteran write (`POST /training-runs/{run}/veteran`, PRD FR-G-1, `SCREEN-020`, plan §8's D16).
 *
 * **This is the slice's only trust boundary, and both of its fields are free text.** `tags` is a list the
 * Trainer composes from the brief's suggestions plus whatever words she likes, and `notes` is a paragraph.
 * Neither is trusted further than here: the page prints both through `{{ }}`, which escapes, and no caller
 * renders them as HTML. Length caps are the denial-of-service bound (`security-and-hardening`'s input-size
 * control) on a tool with no rate limit because it has no network exposure (PRD NFR-1).
 *
 * **The suggestions are a picker, not an allow-list.** `screen-spec-2.0` §24 calls them *suggested tags* and
 * the brief asks for custom tags beside them, so membership is free and only shape and size are enforced. An
 * allow-list here would refuse the half of the field the screen exists to offer.
 *
 * **`authorize()` grants everything but the type.** The repository has no authorization surface by design
 * (`AGENTS.md` §12, PRD NFR-1), so the security guidance to check ownership on every write does not apply
 * and no policy is added. What is checked is that the route actually resolved to a `TrainingRun`, because a
 * write keyed on a missing run would create an unattached library row.
 *
 * The Completed rule lives here rather than in the controller so the boundary has one owner. The same rule
 * is a guard inside `RecordVeteran`, which is the domain's own answer for a non-HTTP caller. The screen makes
 * the POST unreachable for an unfinished career — it renders no form at all, so a Trainer cannot submit one —
 * which leaves this as the refusal for a client that posts anyway: a stale tab, or a request that never came
 * from the screen. That path is tested at the request, not in the browser.
 */
class StoreVeteranRequest extends FormRequest
{
    public const MAX_TAGS = 20;

    public const MAX_TAG_LENGTH = 40;

    public const MAX_NOTES_LENGTH = 2000;

    /**
     * The three numbers are length bounds a form needs to exist by, in the shape every other free-text
     * request in this repository chose: `StoreTrainingRunRequest` caps notes at 5000,
     * `StoreTurnEventRequest` at 1000, `StoreBuildTargetRequest` a skill name at 255. None of them is a
     * sourced game fact, and no source publishes a tag-length rule, so this is a size ceiling rather than a
     * statistic about the sport. Published as `limits` props so the page binds `maxlength` to the same
     * numbers instead of restating them.
     *
     * @return array{max_tags: int, max_tag_length: int, max_notes_length: int}
     */
    public static function limits(): array
    {
        return [
            'max_tags' => self::MAX_TAGS,
            'max_tag_length' => self::MAX_TAG_LENGTH,
            'max_notes_length' => self::MAX_NOTES_LENGTH,
        ];
    }

    /**
     * Control characters, kept as a pattern so the rule and its explanation cannot drift apart. A tag is a
     * label the library prints in a chip and matches against a filter, so a stray escape or a NUL is a
     * broken row rather than a stylistic choice.
     */
    private const CONTROL_CHARACTERS = '/[\x00-\x1F\x7F]/u';

    public function authorize(): bool
    {
        // The type is the whole check, and `veteranRun()` already states it: a write keyed on a run the
        // router never resolved would file an orphan row. There is no authorization surface to consult
        // (`AGENTS.md` §12, PRD NFR-1), so no policy and no ownership test is added here.
        $this->veteranRun();

        return true;
    }

    /**
     * A refused write returns to the same screen with the Trainer's own tags and notes still on it
     * (WCAG 3.3.7). `runs.veteran` is parameterised by the run, so the URL is named here rather than by a
     * `$redirectRoute` the framework could not fill in.
     */
    public function getRedirectUrl(): string
    {
        return route('runs.veteran', $this->veteranRun());
    }

    /**
     * Tags arrive as a hand-composed list, so this is where the shape is decided once: trim, drop the blanks
     * a focused-but-empty chip leaves behind, and de-duplicate case-insensitively so `Turf` and `turf` are
     * not two facets the library's tag filter then matches separately.
     *
     * A first spelling wins, because that is the one the Trainer typed first. A non-string element is left
     * exactly where it is rather than dropped: `tags.0` has to be the field the rules refuse, and silently
     * discarding it would answer a malformed request as if it had been a clean one.
     */
    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');

        if (! is_array($tags)) {
            return;
        }

        $clean = [];
        $seen = [];

        foreach ($tags as $tag) {
            // A focused-but-empty chip arrives as `''`, which `ConvertEmptyStringsToNull` has already
            // turned into null by the time this method runs. Dropping it here is the whole reason the
            // branch exists: without it the null falls into the non-string arm below, is preserved as an
            // element, and the Trainer is told a blank is "not a word" for a field they left empty.
            if ($tag === null) {
                continue;
            }

            if (! is_string($tag)) {
                $clean[] = $tag;

                continue;
            }

            $trimmed = trim($tag);

            if ($trimmed === '') {
                continue;
            }

            $key = mb_strtolower($trimmed);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[] = $trimmed;
        }

        $this->merge(['tags' => $clean]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tags' => ['nullable', 'array', 'max:'.self::MAX_TAGS],
            'tags.*' => ['string', 'min:1', 'max:'.self::MAX_TAG_LENGTH, 'not_regex:'.self::CONTROL_CHARACTERS],
            'notes' => ['nullable', 'string', 'max:'.self::MAX_NOTES_LENGTH],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tags.max' => 'A Veteran carries at most '.self::MAX_TAGS.' tags. Remove one before adding another.',
            'tags.*.string' => 'A tag is a word, not a nested value.',
            'tags.*.max' => 'A tag is at most '.self::MAX_TAG_LENGTH.' characters, so the library stays readable as a list of chips.',
            'tags.*.not_regex' => 'A tag cannot contain a control character.',
            'notes.max' => 'The note is at most '.self::MAX_NOTES_LENGTH.' characters.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tags' => 'tags',
            'notes' => 'note',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $run = $this->veteranRun();

            if ($run->status !== RunStatus::Completed) {
                $validator->errors()->add(
                    'run',
                    'Only a Completed career is filed as a Veteran. This run is '.$run->status->label().', so it is not finished yet.',
                );
            }
        });
    }

    public function veteranRun(): TrainingRun
    {
        $run = $this->route('run');

        abort_unless($run instanceof TrainingRun, 404);

        return $run;
    }

    /**
     * The Trainer's tags, already trimmed and de-duplicated by `prepareForValidation()`. An untagged save is
     * an empty list, which the column stores as `[]` rather than null so the library prints "no tags" rather
     * than a missing cell (`LegacyController::compare()` makes the same point).
     *
     * @return list<string>
     */
    public function tagList(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        /** @var list<string> $tags */
        $tags = array_values((array) ($validated['tags'] ?? []));

        return $tags;
    }

    public function noteText(): ?string
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        $notes = $validated['notes'] ?? null;

        return is_string($notes) && $notes !== '' ? $notes : null;
    }
}
