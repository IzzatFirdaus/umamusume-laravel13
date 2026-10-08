<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MoodTier;
use App\Models\TrainingRun;
use App\Services\ScenarioCaps;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates a historical run being imported from the CSV the app itself exports (ADR-0017).
 *
 * This **extends** `StoreTrainingRunRequest` rather than copying it. The trainee / costume-card /
 * scenario / status / notes half of an import is the same contract the create form already enforces,
 * down to the card-must-belong-to-the-trainee join and the scenario-validated-against-the-composition-matrix
 * rule, and its `prepareForValidation()` is what normalises an empty scenario string to null. Parenting
 * gets that normalisation for free, which is the single most important thing an import needs: Slice 2's
 * browser pass found that a run whose `scenario` column is `''` 500s the run page, and an importer taking
 * external strings is precisely how such a row would be created.
 *
 * The rows themselves are parsed into `turns` before the rules run, so the ceiling check on every stat is
 * an ordinary nested validation rule rather than a bespoke loop the rest of the app would have to trust.
 */
class ImportHistoricalRunRequest extends StoreTrainingRunRequest
{
    /**
     * The header `TrainingRunController::export()` emits. An import accepts exactly this and nothing else:
     * a file the app wrote must be a file the app can read back, and a hand-built sheet is expected to
     * start from that column order.
     */
    public const HEADERS = ['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans'];

    /**
     * The stat columns, so the ceiling loop and the rule list name the same four letters once.
     *
     * @var list<string>
     */
    private const STATS = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Turn counts are bounded by a career's 24-28 turns plus headroom, not by a taste for round numbers.
     * The ceiling matters because every row is validated against the scenario's caps, so an unbounded
     * paste is an unbounded number of rules. A legacy sheet longer than this is a transcription error or
     * several runs, and saying so beats truncating silently.
     */
    private const MAX_ROWS = 200;

    /**
     * A refused submit returns to the import form, never to `back()`.
     *
     * `back()` resolves from the session's previous URL, and between the preview and the commit the
     * most recent request is the preview POST itself, so a refused commit was redirected to
     * `/training-runs/import/preview` — whose GET answers 405 — and the failure navigated nowhere,
     * rendered no error and read as a button that does nothing (KI-64). The form is the one surface
     * that can show the errors and the pasted body again (`old`), so both steps fail there.
     */
    protected function getRedirectUrl(): string
    {
        return route('runs.import');
    }

    /**
     * Parse the CSV into `turns`, and normalise the two places where a real spreadsheet differs from this
     * app's own output: Excel writes a UTF-8 BOM, which would make the first header cell read as
     * "\xEF\xBB\xBFturn" and reject a file the app itself produced; and it writes CRLF line endings.
     *
     * Mood is upper-cased here rather than in a filter because `MoodTier`'s backing values are the
     * `[Global]` client strings verbatim and those are uppercase, while a paper sheet writes "good".
     * An unrecognised word is left as-is so the enum rule rejects it with its own message, instead of
     * quietly becoming null.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->hasFile('file')) {
            /** @var UploadedFile $upload */
            $upload = $this->file('file');
            $this->merge(['csv' => (string) file_get_contents($upload->getRealPath())]);
            // The browser's filename is the provenance the Trainer would recognise; the path behind it is
            // not worth storing, and the rule set never reads this key so it cannot be posted around.
            $this->merge(['source_name' => $upload->getClientOriginalName()]);
            // Removed so `file` is not validated as if the Trainer had typed it.
            $this->request->remove('file');
        }

        $csv = $this->input('csv');

        if (! is_string($csv) || trim($csv) === '') {
            return;
        }

        $rows = self::parse($csv);

        if ($rows === null) {
            // Unparseable or wrong-shaped. `csv.bad` is what the message is reported against, so the
            // form re-shows the textarea the Trainer pasted into rather than clearing it.
            $this->merge(['csv' => $csv, 'turns' => []]);

            return;
        }

        $this->merge(['turns' => $rows]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // A transient run carrying only the submitted scenario is enough to reach the single owner of
        // ceiling arithmetic, so an import is measured by exactly the numbers the turn form and the
        // stat band use: base cap for a run naming no scenario, the scenario's own ceiling otherwise
        // (ADR-0015). Nothing here re-derives a cap.
        $caps = ScenarioCaps::forRun(new TrainingRun([
            'scenario' => is_string($this->input('scenario')) ? $this->input('scenario') : null,
        ]));

        $rules = parent::rules();

        $rules['csv'] = ['required', 'string'];
        $rules['file'] = ['nullable', 'file', 'mimes:csv,txt', 'max:1024'];
        $rules['turns'] = ['required', 'array', 'min:1', 'max:'.self::MAX_ROWS];
        $rules['turns.*.turn'] = ['required', 'integer', 'min:1', 'distinct'];

        foreach (self::STATS as $stat) {
            $rules['turns.*.'.$stat] = ['required', 'integer', 'between:0,'.$caps[ucfirst($stat)]];
        }

        $rules['turns.*.sp'] = ['nullable', 'integer', 'min:0'];
        $rules['turns.*.condition'] = ['nullable', 'string', 'max:255'];
        $rules['turns.*.energy'] = ['nullable', 'integer', 'between:0,100'];
        $rules['turns.*.mood'] = ['nullable', Rule::enum(MoodTier::class)];
        $rules['turns.*.fans'] = ['nullable', 'integer', 'min:0'];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'turns.required' => 'That file has no usable turn rows. The first line must be the header: '
                .implode(', ', self::HEADERS).'.',
            'turns.*.turn.distinct' => 'Two rows claim the same turn number. Turn numbers are unique within a run.',
            'csv.required' => 'Paste the run\'s CSV or choose the file to import.',
        ];

        /*
         * KI-46. Laravel's default text puts the nested request key in the sentence, which is how a Trainer
         * read `The turns.0.speed field must be between 0 and 1400.` `attributes()` cannot help: a wildcard
         * key maps to one string, so renaming collapses every row onto the same word and the row identity
         * goes with it. These carry a `:where` token that `withValidator()` replaces per row, and they are
         * phrased to complete the sentence the form already prints in front of them.
         */
        foreach (self::STATS as $stat) {
            $messages["turns.*.{$stat}.between"] = ':where must be between :min and :max.';
            $messages["turns.*.{$stat}.integer"] = ':where has a '.$stat.' that is not a whole number.';
            $messages["turns.*.{$stat}.required"] = ':where has no '.$stat.'.';
        }

        $messages['turns.*.turn.integer'] = ':where must be a turn number.';
        $messages['turns.*.turn.required'] = ':where has no turn number.';

        return $messages;
    }

    /**
     * Replace each `:where` with the row it belongs to: the turn number the Trainer wrote, or the position
     * in the file when that is the cell that is broken. KI-46.
     */
    public function withValidator(Validator $validator): void
    {
        /** @var list<array<string, string|null>> $rows */
        $rows = (array) $this->input('turns', []);

        $validator->after(function (Validator $validator) use ($rows): void {
            $errors = $validator->errors();

            foreach ($errors->messages() as $key => $messages) {
                if (preg_match('/^turns\.(\d+)\.(?:'.implode('|', self::STATS).'|turn)$/', $key, $m) !== 1) {
                    continue;
                }

                $stated = $rows[(int) $m[1]]['turn'] ?? null;
                $where = is_string($stated) && ctype_digit($stated)
                    ? 'turn '.$stated
                    : 'row '.((int) $m[1] + 1);

                $errors->forget($key);

                foreach ($messages as $message) {
                    $errors->add($key, str_replace(':where', $where, $message));
                }
            }
        });
    }

    /**
     * What to store in `training_runs.import_source`.
     *
     * The filename when an upload carried one, because that is the string the Trainer would point at.
     * A pasted body has no name, so it gets a short hash of its own content: not a fingerprint anyone
     * will verify, but enough that two pasted runs are distinguishable in the record and neither claims
     * a provenance it does not have.
     */
    public function sourceLabel(string $csv): string
    {
        $name = $this->input('source_name');

        if (is_string($name) && trim($name) !== '') {
            return str($name)->limit(180)->value();
        }

        return 'pasted CSV ('.substr(md5($csv), 0, 8).')';
    }

    /**
     * The parsed rows, or null when the text is not a run export.
     *
     * Blank lines are skipped rather than counted: a file saved from a spreadsheet usually ends in one,
     * and treating it as a row of empty cells would reject a file that is otherwise exact.
     *
     * @return list<array<string, string|null>>|null
     */
    public static function parse(string $csv): ?array
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv;
        $lines = preg_split('/\R/', trim($csv)) ?: [];
        $lines = array_values(array_filter($lines, static fn (string $l): bool => trim($l) !== ''));

        if ($lines === []) {
            return null;
        }

        // The escape argument is passed explicitly: PHP 8.5 deprecates relying on its default, and an
        // empty escape is the correct reading of this app's own export, which emits bare commas and no
        // backslash quoting. Passing it keeps the import symmetrical with the writer.
        $header = array_map(
            static fn (?string $h): string => is_string($h) ? strtolower(trim($h)) : '',
            str_getcsv($lines[0], ',', '"', '')
        );

        if ($header !== self::HEADERS) {
            return null;
        }

        $rows = [];

        foreach (array_slice($lines, 1) as $line) {
            $cells = str_getcsv($line, ',', '"', '');

            // A short row is a hole in the transcription, not an empty trailing cell, and padding it
            // would let `fans` land in `energy`. Reject the file instead of guessing which column moved.
            if (count($cells) !== count(self::HEADERS)) {
                return null;
            }

            $row = [];

            foreach (self::HEADERS as $i => $key) {
                $value = trim((string) $cells[$i]);
                $row[$key] = $value === '' ? null : $value;
            }

            if (is_string($row['mood'])) {
                $row['mood'] = strtoupper($row['mood']);
            }

            $rows[] = $row;
        }

        return $rows === [] ? null : $rows;
    }
}
