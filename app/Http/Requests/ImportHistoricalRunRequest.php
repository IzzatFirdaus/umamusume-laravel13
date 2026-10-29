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
     * Why the posted column mapping cannot be read, when it cannot. Set while parsing so a refusal
     * reports the mapping rather than the generic "no usable turn rows" sentence, which would send a
     * Trainer to re-check a header that was read fine.
     */
    private ?string $mappingProblem = null;

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

        $rows = self::parse($csv, $this->mapping());

        if ($rows === null) {
            // Unparseable or wrong-shaped. `csv.bad` is what the message is reported against, so the
            // form re-shows the textarea the Trainer pasted into rather than clearing it.
            $this->mappingProblem = $this->mapping() === null ? null : self::mappingProblem($csv, (array) $this->mapping());
            $this->merge(['csv' => $csv, 'turns' => []]);

            return;
        }

        $this->merge(['turns' => $rows]);
    }

    /**
     * The posted column mapping, or null when the form posted none.
     *
     * A mapping is a list aligned to the file's own columns, each entry naming the import column that
     * column supplies, or `''` to leave it out. When it is present the header line need not be the
     * app's own order: a sheet with the same eight columns in another order is a mapping away from
     * importing, and refusing it would make the Trainer re-order a sheet by hand.
     *
     * @return list<string|null>|null
     */
    private function mapping(): ?array
    {
        $mapping = $this->input('mapping');

        if (! is_array($mapping)) {
            return null;
        }

        return array_values(array_map(
            static fn (mixed $target): ?string => is_string($target) ? trim($target) : null,
            $mapping
        ));
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
        $rules['mapping'] = ['nullable', 'array'];
        $rules['mapping.*'] = ['nullable', 'string', Rule::in(self::HEADERS)];
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
     * The validated run fields, narrowed to the shape `ImportHistoricalRun::handle()` reads.
     *
     * @return array{umamusume_id: int, character_card_id?: int|null, scenario?: string|null, status: string, notes?: string|null, current_objective_index?: int|null}
     */
    public function runInput(): array
    {
        $validated = $this->validated();

        $run = [
            'umamusume_id' => (int) $validated['umamusume_id'],
            'status' => (string) $validated['status'],
        ];

        if (array_key_exists('character_card_id', $validated)) {
            $run['character_card_id'] = $validated['character_card_id'] === null ? null : (int) $validated['character_card_id'];
        }

        if (array_key_exists('scenario', $validated)) {
            $run['scenario'] = $validated['scenario'] === null ? null : (string) $validated['scenario'];
        }

        if (array_key_exists('notes', $validated)) {
            $run['notes'] = $validated['notes'] === null ? null : (string) $validated['notes'];
        }

        if (array_key_exists('current_objective_index', $validated)) {
            $run['current_objective_index'] = $validated['current_objective_index'] === null ? null : (int) $validated['current_objective_index'];
        }

        return $run;
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
            // Laravel's default for a wildcard key names the field ("The selected mapping.0 is invalid."),
            // which tells the Trainer nothing about the choice they have to make.
            'mapping.*' => 'That file column cannot be mapped. Map it to one of: '.implode(', ', self::HEADERS).'.',
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
            if ($this->mappingProblem !== null) {
                $validator->errors()->add('mapping', $this->mappingProblem);

                /*
                 * The mapping is what stopped the parse, so `turns` is empty and the generic
                 * `turns.required` sentence would fire on top of the real reason. It names the header as
                 * the culprit, and the header was read fine — the mapping was not. The refusal that
                 * explains the mapping is the one the Trainer needs, so the generic sentence is dropped
                 * rather than shown beside it.
                 */
                $validator->errors()->forget('turns');
                $validator->errors()->forget('turns.required');
            }

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
     * The parsed rows, or null when the text is not a run export (or the mapping cannot be read).
     *
     * Blank lines are skipped rather than counted: a file saved from a spreadsheet usually ends in one,
     * and treating it as a row of empty cells would reject a file that is otherwise exact.
     *
     * With no mapping the header must equal `HEADERS` exactly, and the columns are read in that order.
     * With a mapping the header may be any order: each entry names the import column its file column
     * supplies, `''` ignores that column, and the mapping must be a bijection onto `HEADERS`:
     * `mappingProblem()` is the one place that rule is stated, so the parser and the refusal agree.
     *
     * @param  list<string|null>|null  $mapping
     * @return list<array<string, string|null>>|null
     */
    public static function parse(string $csv, ?array $mapping = null): ?array
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
        $header = self::headerCells($lines[0]);

        if ($mapping === null) {
            if ($header !== self::HEADERS) {
                return null;
            }

            /** @var array<string, int> $sourceByTarget */
            $sourceByTarget = array_flip(self::HEADERS);
        } else {
            if (count($mapping) !== count($header) || self::mappingProblem($csv, $mapping) !== null) {
                return null;
            }

            $sourceByTarget = [];

            foreach ($mapping as $index => $target) {
                if (is_string($target) && $target !== '') {
                    $sourceByTarget[$target] = $index;
                }
            }
        }

        $rows = [];

        foreach (array_slice($lines, 1) as $line) {
            $cells = str_getcsv($line, ',', '"', '');

            // A short row is a hole in the transcription, not an empty trailing cell, and padding it
            // would let `fans` land in `energy`. Reject the file instead of guessing which column moved.
            if (count($cells) !== count($header)) {
                return null;
            }

            $row = [];

            foreach (self::HEADERS as $key) {
                $value = trim((string) $cells[$sourceByTarget[$key]]);
                $row[$key] = $value === '' ? null : $value;
            }

            if (is_string($row['mood'])) {
                $row['mood'] = strtoupper($row['mood']);
            }

            $rows[] = $row;
        }

        return $rows === [] ? null : $rows;
    }

    /**
     * Why a posted mapping cannot be read, or null when it is a bijection onto `HEADERS`.
     *
     * Every import column must be supplied exactly once: a duplicate would read one column twice and
     * silently drop another, an unknown name imports nothing, and a gap would fail the per-turn rules
     * with a row error the Trainer cannot trace back to the mapping. The message names which of the
     * three it is, because "the header is wrong" is not true of a file whose header was read fine.
     *
     * @param  list<string|null>  $mapping
     */
    public static function mappingProblem(string $csv, array $mapping): ?string
    {
        $lines = preg_split('/\R/', trim(preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv)) ?: [];
        $header = self::headerCells($lines[0] ?? '');

        if (count($mapping) !== count($header)) {
            return 'The mapping must name one import column for each of the file\'s '.count($header).' columns.';
        }

        $seen = [];

        foreach ($mapping as $target) {
            if ($target === null || $target === '') {
                continue;
            }

            if (! in_array($target, self::HEADERS, true)) {
                return 'The mapping names "'.$target.'", which is not a column this importer reads.';
            }

            if (isset($seen[$target])) {
                return 'Two file columns both map to "'.$target.'". Each import column is supplied once.';
            }

            $seen[$target] = true;
        }

        $missing = array_values(array_diff(self::HEADERS, array_keys($seen)));

        if ($missing !== []) {
            return 'The mapping leaves '.implode(', ', $missing).' unmapped. Every column the importer writes needs one source column.';
        }

        return null;
    }

    /**
     * The first line's cells, normalised the way both the strict and the mapped read need.
     *
     * @return list<string>
     */
    private static function headerCells(string $line): array
    {
        /** @var list<string> $cells */
        $cells = array_map(
            static fn (?string $h): string => is_string($h) ? strtolower(trim($h)) : '',
            str_getcsv($line, ',', '"', '')
        );

        return $cells;
    }
}
