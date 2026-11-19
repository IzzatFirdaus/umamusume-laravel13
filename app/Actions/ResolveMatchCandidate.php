<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Trainer verdict on a review-queue candidate (PRD US-5). Confirmed promotes the
 * stored payload (into the suggested row when there is one, as a new row
 * otherwise); Aliased records the proposed name as an alias of the target;
 * Rejected only closes the candidate. All three run in one transaction.
 */
final class ResolveMatchCandidate
{
    public function __construct(private readonly PromoteMatchedRecord $promote) {}

    /**
     * @param  array{status: string, umamusume_id?: int|null, alias_language?: string|null}  $input
     */
    public function handle(MatchCandidate $candidate, array $input): MatchCandidate
    {
        $status = CandidateStatus::from($input['status']);

        return DB::transaction(function () use ($candidate, $status, $input): MatchCandidate {
            $target = isset($input['umamusume_id'])
                ? Umamusume::findOrFail((int) $input['umamusume_id'])
                : $candidate->suggestedUmamusume;

            // A race is not a trainee. `confirm()` promotes through `PromoteMatchedRecord`, whose shape
            // constructs an `App\Models\Umamusume`, so confirming a race-sourced candidate would either
            // throw (the payload has `name_en`, not `name`) or - if a payload ever carried both - write a
            // race into the trainee table. That is a data-integrity write, not a crash, which is why the
            // refusal belongs here rather than in a docblock.
            //
            // ponytail: race candidates can only be Rejected today. Whether a race should ever be
            // promotable BY HAND is an owner ruling, and the resolver path that would do it
            // (`StoreRaceCatalogSlots` joining by id, as `uma:enrich-races` does) is a schema decision
            // (an `entity` column) this slice does not take. Upgrade path: add the column + ADR, then
            // dispatch on it here.
            if (($candidate->payload['entity'] ?? 'umamusume') !== 'umamusume'
                && $status !== CandidateStatus::Rejected) {
                throw ValidationException::withMessages([
                    'status' => 'This candidate is a race, and the review queue can only reject one. Races reach the catalog through uma:enrich-races.',
                ]);
            }

            // A verdict that promises a write needs something to write on. `alias()` used to return
            // early when the target was null while this method stamped the row `Aliased` anyway, so a
            // suggestion-less candidate left the queue marked as aliased with no alias recorded: the
            // queue's only non-Reject door for those rows was a no-op reported as success. Refused
            // here rather than in the request because only the model knows whether a suggestion
            // exists, and a 500 or a silent success are both worse than a named refusal.
            if ($status === CandidateStatus::Aliased && $target === null) {
                throw ValidationException::withMessages([
                    'umamusume_id' => 'This candidate has no suggested trainee and none was named, so there is nothing to record an alias on. Name the trainee, or reject the candidate.',
                ]);
            }

            match ($status) {
                CandidateStatus::Confirmed => $this->confirm($candidate, $target),
                CandidateStatus::Aliased => $this->alias($candidate, $target, $input),
                CandidateStatus::Rejected => null,
                CandidateStatus::Pending => null,
            };

            $candidate->status = $status;
            $candidate->save();

            return $candidate;
        });
    }

    private function confirm(MatchCandidate $candidate, ?Umamusume $target): void
    {
        /** @var array{name: string, url?: string} $payload */
        $payload = $candidate->payload;

        $this->promote->handle(
            record: $payload,
            existing: $target,
            sourceKey: $candidate->source_key,
            url: (string) ($payload['url'] ?? 'resolved-from-review-queue'),
        );
    }

    /**
     * @param  array{alias_language?: string|null}  $input
     */
    private function alias(MatchCandidate $candidate, ?Umamusume $target, array $input): void
    {
        if ($target === null) {
            return;
        }

        $language = AliasLanguage::tryFrom((string) ($input['alias_language'] ?? '')) ?? AliasLanguage::English;

        $this->addAlias($target, $candidate->proposed_name, $language);

        if ($candidate->proposed_name_ja !== null) {
            $this->addAlias($target, $candidate->proposed_name_ja, AliasLanguage::Japanese);
        }
    }

    private function addAlias(Umamusume $target, string $alias, AliasLanguage $language): void
    {
        UmamusumeAlias::firstOrCreate([
            'umamusume_id' => $target->id,
            'alias' => $alias,
            'language' => $language->value,
        ]);
    }
}
