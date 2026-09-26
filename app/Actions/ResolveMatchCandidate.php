<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Models\UmamusumeAlias;
use Illuminate\Support\Facades\DB;

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
