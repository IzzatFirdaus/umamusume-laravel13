<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\ReleaseStatus;
use App\Services\DataPipeline\Contracts\SkillSourceParser;
use JsonException;

/**
 * Reads the GameTora skill catalogue into `skills` rows (ADR-0011; PRD FR-D-1 as amended 2026-09-29).
 *
 * Measured on the document as fetched 2026-09-29 (1,910 records): this class states no number it cannot
 * point at in that file, and the three places the source says less than a screen would like are handled
 * by leaving a column null rather than by rounding the claim up.
 *
 * **The client string is `name_en`, and `enname` is not it.** `enname` is a literal rendering of the
 * Japanese name and the two differ on 535 of the 623 `[Global]` rows — id 200311 is `G1 Dislike` there and
 * `G1 Averseness` in `name_en`, and `UMAMUSUME_REFERENCE.md` §1.2.6 pins grade 100 → G1 from that client
 * copy. `name_is_client` is therefore set from the pair the source states (a `name_en` exists **and** the
 * row is not marked unreleased on `en`), never from which of the two fields looked more English.
 *
 * **A `name_en` on an unreleased row is still not client copy.** 362 of the 1,287 rows carry one, and
 * §2.7's Air Messiah ruling is exactly this case: an English string on something the server has not
 * shipped is a third-party label. Those rows import with the name they have and the flag false, which is
 * what makes `Skill::availableOnGlobal()` the whole of the read-path filter.
 *
 * **Availability comes from the source, not from inference.** `unreleased` lists the servers a skill is
 * not on; every populated value in this file contains `en`, so absence is the Global statement. Crossed
 * against `character-cards`' own `release_en` on the 673 skills a released card owns, the two agree on
 * 403 and disagree on 270, every disagreement a rarity-6 evolved row — evolved skills are absent from
 * `[Global]` even for characters who are on it, which is conflict row 45 reproduced from two documents.
 *
 * **`rarity` is stored as the class code the source states.** 1 (598 rows) and 2 (346) carry `cost`;
 * 3 (22), 4 (22) and 5 (250) are bound to `char` and carry `gene_version`; 6 (672) all carry `pre_evo`.
 * That is learnable / evolvable / unique-variant / evolved, not the client's three rarities, and no
 * rarity word is emitted here. `is_unique` is the one conclusion the codes do support: every id in a
 * card's `skills_unique` list has rarity 3, 4 or 5, and 22 + 22 + 246 = 290 is exactly the population
 * carrying `gene_version`. The stored count is **294**, four rarity-5 rows beyond what the card join
 * reaches — `300131`, `300141`, `1400011`, `1400021`, each with an empty `char`, no `gene_version`, named
 * by no card. A parser sees one document and cannot join, so the code rule is what runs and `ADR-0011` §5
 * carries the delta instead of pretending the two counts agree.
 *
 * **`type` is derived, labelled as derived, and absent where the evidence stops.** The source's own
 * `type` field is a list of gate keys (`nac`, `med`, `l_1`, `dir`, `cor`, `f_s`, …) — when a skill fires,
 * not what kind it is. The category is read off the effect codes, each identified by the English
 * description sitting in the same record:
 *
 * | code | rows | what the record's own text says | derived |
 * |---|---|---|---|
 * | 27 | 1,238 | "increase velocity" | Speed |
 * | 22 | 380 | "surge ahead … increase acceleration" | Speed |
 * | 9 | 334 | Stamina recovery mid-race | Recovery |
 * | 28 | 40 | Stamina recovery when the way ahead is jammed | Recovery |
 * | 1 / 2 / 3 | 124 / 78 / 59 | performance modified by a track-side, venue or grade condition | Passive |
 *
 * **The descriptions are the publisher's English, not the client's.** Code 9's rows are identified here
 * from `desc_en`, and that field names the recovered stat with a word `[Global]` does not use for it
 * (`docs/SOURCE-OF-TRUTH.md` §3: the stat is Stamina). So a description is evidence of what an effect
 * *does* and is never a source of copy — the same split this class already draws for names, where
 * `name_en` is the client string and `enname` is a translation. Nothing below quotes a description into a
 * label.
 *
 * **A negative value voids the claim, and that rule is here because of one row.** Effect code 1 is not
 * "Passive"; it is an axis — `Right-Handed ◎` reads +600000 ("increase performance on right-handed
 * tracks") and `G1 Averseness` reads −400000 on the same code ("decrease performance in G1 or otherwise
 * important races"). Reading the code alone would have labelled an averseness skill `Passive`, so the
 * sign is part of the rule: any effect whose value is not a positive integer returns null.
 *
 * Twenty-two other codes are unmapped — 31 (431 rows), 21 (60), 4, 5, 6, 8, 10, 13, 14, 29, 32, 35, 37,
 * 38, 41, 42, 48, 49, 50, 501, 502, 503 — and a row carrying one gets null rather than a guess. Measured
 * on the imported document: across all 1,910 rows Speed 913, Passive 120, Recovery 82, null 795; on the
 * 623 `[Global]` rows Speed 199, Passive 82, Recovery 54, null 288. **The sign rule is what moves the
 * second set** — reading code alone would have labelled 53 more rows, including 32 averseness skills on
 * code 1. **Code 21 is deliberately not read as Debuff**: its values are negative (`v=-2000`) on skills
 * that weaken their *own* runner — `Corner Adept ×`, `Defeatist` — which is the negative-skill family of
 * `SKILLS-GAPS.md` path 8, not the client's Debuff type, and conflating the two would name a category off
 * a colour guess.
 *
 * The four words themselves come from Game8's skill-type classification, `[A]` tier, not from a captured
 * client frame, so a surface rendering `type` renders this tool's derivation and says so (D-20, D-256).
 */
final class GametoraSkillsParser implements SkillSourceParser
{
    /**
     * Class codes the card document names as a trainee's own unique skill.
     *
     * Evidence: all 268 `character-cards` records list their unique skill in `skills_unique`, and every
     * id those arrays reach has one of these three rarity codes. The count is the check — 22 + 22 + 246
     * equals the 290 rows carrying `gene_version`, the inherited form only a unique skill can have.
     */
    private const UNIQUE_CLASS_CODES = [3, 4, 5];

    /** Effect code to category, each justified by the English text in the same record. See the class docblock. */
    private const CATEGORY_BY_EFFECT_CODE = [
        27 => 'Speed',
        22 => 'Speed',
        9 => 'Recovery',
        28 => 'Recovery',
        1 => 'Passive',
        2 => 'Passive',
        3 => 'Passive',
    ];

    /**
     * @return list<array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *                   release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null}>
     */
    public function parse(string $body): array
    {
        try {
            $skills = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($skills)) {
            return [];
        }

        $rows = [];

        foreach ($skills as $skill) {
            $row = $this->row($skill);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $skill  mixed, not array: a body that decodes to a map yields scalars here, and a
     *                        fetch must not throw over it
     * @return array{export_id: int, name: string, name_ja: string|null, name_is_client: bool,
     *               release_status: string, rarity: int|null, is_unique: bool, sp_cost: int|null, type: string|null}|null
     */
    private function row(mixed $skill): ?array
    {
        if (! is_array($skill) || ! isset($skill['id']) || ! is_int($skill['id'])) {
            return null;
        }

        $onGlobal = ! $this->unreleasedOnGlobal($skill['unreleased'] ?? null);
        $clientName = $this->textOrNull($skill['name_en'] ?? null);
        $rendering = $this->textOrNull($skill['enname'] ?? null);

        // A row this file cannot name is not worth a row: `skills.name` is NOT NULL, and filling it
        // with an id would be a number wearing the place of a label.
        $name = $clientName ?? $rendering;

        if ($name === null) {
            return null;
        }

        $rarity = isset($skill['rarity']) && is_int($skill['rarity']) ? $skill['rarity'] : null;

        return [
            'export_id' => $skill['id'],
            'name' => $name,
            // The Japanese name lives under `jpname` here and `name_jp` in the card document. Two
            // different keys for one publisher, so neither is guessed: KI-23 is what reading the wrong
            // one looks like, and its own test could not see it because the fixture agreed with it.
            'name_ja' => $this->textOrNull($skill['jpname'] ?? null),
            'name_is_client' => $clientName !== null && $onGlobal,
            'release_status' => $onGlobal
                ? ReleaseStatus::GlobalReleased->value
                : ReleaseStatus::JapanOnly->value,
            'rarity' => $rarity,
            'is_unique' => $rarity !== null && in_array($rarity, self::UNIQUE_CLASS_CODES, true),
            'sp_cost' => isset($skill['cost']) && is_int($skill['cost']) ? $skill['cost'] : null,
            'type' => $this->category($skill['condition_groups'] ?? null),
        ];
    }

    /**
     * The servers the source says this skill is *not* on.
     *
     * @param  mixed  $unreleased  mixed because the field is absent on the 623 Global rows
     */
    private function unreleasedOnGlobal(mixed $unreleased): bool
    {
        return is_array($unreleased) && in_array('en', $unreleased, true);
    }

    /**
     * Speed, Recovery, Passive — or null.
     *
     * Null on three conditions, each of them a refusal to guess: any effect code outside the table above,
     * more than one category in one skill (a recovery *and* velocity effect is not one type), and no
     * effect at all.
     *
     * @param  mixed  $groups  mixed: the caller is reading a document, not a schema
     */
    private function category(mixed $groups): ?string
    {
        if (! is_array($groups)) {
            return null;
        }

        $categories = [];

        foreach ($groups as $group) {
            if (! is_array($group) || ! is_array($group['effects'] ?? null)) {
                continue;
            }

            foreach ($group['effects'] as $effect) {
                $code = is_array($effect) ? ($effect['type'] ?? null) : null;
                $value = is_array($effect) ? ($effect['value'] ?? null) : null;

                // The code names an axis and the sign names the direction, and only the pair is a
                // category: `G1 Averseness` carries the same code 1 as `Right-Handed ◎` while meaning
                // the opposite of it. A non-positive value is therefore not evidence of `Passive`.
                if (! is_int($code) || ! is_int($value) || $value <= 0
                    || ! isset(self::CATEGORY_BY_EFFECT_CODE[$code])) {
                    return null;
                }

                $categories[(string) self::CATEGORY_BY_EFFECT_CODE[$code]] = true;
            }
        }

        return count($categories) === 1 ? (string) array_key_first($categories) : null;
    }

    private function textOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
