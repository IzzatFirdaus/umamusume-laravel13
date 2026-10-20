<?php

declare(strict_types=1);

namespace App\Services\Legacy;

use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Veteran;
use App\Services\DataPipeline\ArtworkMirror;

/**
 * One Veteran as a list row, in the shape the surfaces that list the library already print.
 *
 * `LegacyController::index()` (the Legacy Lab browse list) and `VeteranController::index()` (the Veteran
 * library) show the same record: the trainee she ran as, the scenario she ran it in, the state the run is
 * in, the Trainer's own tags and notes, and whether a Legacy read-back exists on it. Two copies of that
 * mapping would be two shapes for one row, and the first screen to gain a column would silently print a
 * different Veteran than the other does, so the shape has one owner here.
 *
 * **It reads; it derives nothing** (PRD FR-G-4, `ADR-0020` §3). No factor analysis, no spark count, no
 * legacy value, no ranking: those are the held computations, and a row that carried one would put a score
 * on a screen whose contract says the library stores and searches. Everything here is a stored column, a
 * route, or — for `portrait_url` — an id-derived mirror path the render reads back (`ADR-0021`), which is
 * the same read-not-derive shape the catalog's own header portrait takes.
 *
 * Both link fields are always present and both print: the library names the trainee with `veteran_url`,
 * and the Legacy Lab now links the same name while still offering `builder_url` where a read-back exists.
 * `status_label` travels because one record is one row shape for every surface, not because a list
 * states it: neither list prints it, since only a Completed run is recordable (`RecordVeteran`) and a
 * column that holds one word on every row says nothing about the row it is on.
 */
final class VeteranRow
{
    /**
     * @return array{id: int, run_id: int, label: string, trainee: string, trainee_ja: string|null, scenario_label: string,
     *               status_label: string, tags: list<string>, notes: string|null,
     *               hasSelection: bool, builder_url: string|null, veteran_url: string, portrait_url: string|null}
     */
    public static function from(Veteran $veteran): array
    {
        // The caller eager-loads `trainingRun.umamusume.cards` (`ListVeterans::handle()`), so this is a read
        // of rows already in memory rather than a query per row.
        $run = $veteran->trainingRun;
        $payload = $run->legacySelection();

        return [
            'id' => $veteran->id,
            // The run's own id, because the Legacy Lab's compare surface takes it: a Veteran is a pointer
            // and the comparison is between runs, not between library rows (`Legacy/Index.vue`).
            'run_id' => $run->id,
            'label' => self::label($run),
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'scenario_label' => self::scenarioLabel($run),
            'status_label' => $run->status->label(),
            // Filtered to the stored strings the same way the model's own `booted()` guard folds the twin,
            // so a stray non-string entry cannot widen the list's type or reach a consumer.
            'tags' => array_values(array_filter($veteran->tags ?? [], 'is_string')),
            'notes' => $veteran->notes,
            'hasSelection' => $payload !== null,
            'builder_url' => $payload === null ? null : route('legacy.builder', $run),
            'veteran_url' => route('veterans.show', $veteran),
            // The frame of the form this career was trained in, resolved server-side by the helper below.
            // The url is never composed in Vue (`ADR-0021` read half, `DESIGN.md` §4.7).
            'portrait_url' => self::portraitUrl($run),
        ];
    }

    /**
     * The portrait of the form this career was trained in, as a loopback url, or null when the mirror
     * holds no file for it.
     *
     * The one owner of the resolution: the Veteran library row and the Veteran detail header both read it
     * here, so the two cannot pick two different forms for the same Veteran. Two paths exist and the order
     * is deliberate:
     *
     *   1. **The run's chosen form.** `training_runs.character_card_id` records the costume the Trainer
     *      actually started the career in (`ADR-0008`, the column the run carries). A Veteran is that
     *      career read back, so its frame is that costume: a New Year run shows the New Year portrait, not
     *      the debut. The catalog index deliberately does *not* do this — it is a catalog of trainees, not
     *      of careers — so the two surfaces differ on purpose rather than by accident.
     *   2. **The trainee's identity frame, only when the run recorded no card.** The column is nullable and
     *      a Trainer can leave it unset, in which case the career has no chosen form. There the catalog's
     *      own identity rule stands in — the debut form, else her first card, the same resolution
     *      `CatalogController` uses for a catalog row's header.
     *
     * A chosen form the mirror does not hold is a null, not a fall-through to the debut: the Trainer chose
     * that costume, so showing a different one would be a claim the career did not make. `card_id` is the
     * publisher's number and the only key the mirror's storage path answers to; a null is the mirror's
     * normal partial answer and the caller renders the reserved absence cell for it.
     */
    public static function portraitUrl(?TrainingRun $run): ?string
    {
        if ($run === null) {
            return null;
        }

        $mirror = app(ArtworkMirror::class);

        $chosen = $run->characterCard;

        if ($chosen !== null) {
            return $mirror->url('card_portrait', (int) $chosen->card_id);
        }

        $trainee = $run->umamusume;
        $trainee->loadMissing('cards');
        $headerCard = $trainee->cards->first(static fn (CharacterCard $card): bool => $card->is_debut_form)
            ?? $trainee->cards->first();

        return $headerCard === null ? null : $mirror->url('card_portrait', (int) $headerCard->card_id);
    }

    /**
     * A human-readable label that disambiguates careers trained in the same form: trainee, scenario and
     * the date the run started (`A-95`). The date is the only stored start marker; without it the library
     * can print two identical "Vodka" rows.
     */
    public static function label(TrainingRun $run): string
    {
        $date = $run->created_at?->format('Y-m-d') ?? 'unknown date';

        return "{$run->umamusume->name} · ".self::scenarioLabel($run)." · {$date}";
    }

    /**
     * The scenario's displayed label, from `config/scenarios.php` and nowhere else.
     *
     * A run with no scenario is a real state, and the client's own word for the absence is the sentence
     * this returns rather than an empty string a row would then render as a gap.
     */
    public static function scenarioLabel(TrainingRun $run): string
    {
        if (! filled($run->scenario)) {
            return 'No scenario set';
        }

        /** @var array<string, array{label: string}> $scenarios */
        $scenarios = config('scenarios.scenarios');

        return $scenarios[$run->scenario]['label'] ?? $run->scenario;
    }
}
