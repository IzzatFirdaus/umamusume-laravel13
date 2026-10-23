<?php

declare(strict_types=1);

/*
 * ============================================================================
 * TRAINER DESK v2.0.0 HFW — the mock catalogue.
 * GENERATOR. Writes assets/data/{umamusume,support-cards,skills}.js.
 *
 * WHAT THIS READS
 *
 *   database/database.sqlite          the dev database's own catalogue tables:
 *                                     umamusume, character_cards, support_cards,
 *                                     support_effects, skills, umamusume_profiles.
 *   database/seeders/data/*.json      the committed GameTora export bodies the
 *                                     app itself imports from, for the three
 *                                     things the catalogue tables do not keep:
 *                                     a card's base stats and its growth row, and
 *                                     a skill's source text.
 *
 * WHY THE DATABASE AND NOT THE EXPORT ALONE
 *
 *   The export carries the raw keys; the database carries what the pipeline
 *   made of them. The derived skill `type` word (Speed / Passive / Recovery)
 *   and the `is_unique` mark are the parser's conclusions, not the export's
 *   fields, and the support-card effect dictionary has already been resolved
 *   through its own `name_en` -> `name_en_eon` fallback. Re-deriving any of
 *   that here would be a second implementation of a rule that already has one.
 *
 * WHAT THIS DOES NOT DO
 *
 *   It does not invent a value. Every figure below is a figure the app already
 *   holds or a figure the committed export body states, and the checks at the
 *   end assert exactly that: the counts here must equal the counts in the
 *   tables, and the growth rows must still sum to 30.
 *
 *   It does not write a skill description. G-SK-20 holds that no description
 *   column may be added until a client-string source for descriptions exists,
 *   and the export's English is the publisher's, not the client's (the skills
 *   parser says so in its own words). The publisher's text is carried under
 *   `sourceText`, which is a name that cannot be mistaken for copy, and the
 *   `description` field is null on every row so a surface that binds to it
 *   prints the "no client string" state the wireframe already draws.
 *
 * RUN
 *
 *   php build-data.php            from this directory. Writes the three files.
 *
 *   The database is gitignored and is rebuilt by `php artisan migrate:fresh
 *   --seed`, which seeds from the committed bodies, so the output is
 *   reproducible from a clean checkout. Run that first if the file is absent.
 * ============================================================================
 */

$here = __DIR__;
$repo = dirname($here, 3);
$database = $repo.'/database/database.sqlite';
$seedDir = $repo.'/database/seeders/data';
$outDir = $here.'/assets/data';

if (! is_file($database)) {
    fwrite(STDERR, "The dev database is missing: {$database}\n".
        "Run `php artisan migrate:fresh --seed` first (it seeds from the committed bodies).\n");
    exit(1);
}

$db = new PDO('sqlite:'.$database);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/** @return list<array<string, mixed>> */
function rows(PDO $db, string $sql): array
{
    $out = [];
    foreach ($db->query($sql) as $row) {
        $clean = [];
        foreach ($row as $key => $value) {
            if (! ctype_digit((string) $key)) {
                $clean[$key] = $value;
            }
        }
        $out[] = $clean;
    }

    return $out;
}

/** @return array<int, mixed> */
function readJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (! is_array($data)) {
        throw new RuntimeException("{$path} is not a JSON array");
    }

    return $data;
}

/**
 * Encode one value as a JavaScript literal.
 *
 * JSON is a subset of JavaScript, so `json_encode` is the encoder; the two
 * characters JSON leaves raw that older JavaScript parsers reject as line
 * terminators are escaped here, because these files are loaded as classic
 * scripts straight off the disk.
 */
function js(mixed $value): string
{
    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    return str_replace([' ', ' '], ['\\u2028', '\\u2029'], $encoded);
}

/**
 * One record per line, so a diff shows the record that moved.
 *
 * @param  list<array<string, mixed>>  $records
 */
function jsRecords(array $records, int $indent = 4): string
{
    if ($records === []) {
        return '[]';
    }
    $pad = str_repeat(' ', $indent);
    $lines = array_map(static fn (array $r): string => $pad.js($r), $records);

    return "[\n".implode(",\n", $lines)."\n]";
}

/** A date column, trimmed to the calendar day the source states. */
function day(?string $value): ?string
{
    if ($value === null || $value === '' || str_starts_with($value, '0000-00-00')) {
        return null;
    }

    return substr($value, 0, 10);
}

/**
 * The five stats, in the export's own column order.
 *
 * GameTora's order is Speed, Stamina, Power, Guts, Wit
 * (`docs/UMAMUSUME_REFERENCE.md` §1.3.5), and the wireframes print the growth
 * row in that order too.
 *
 * @param  list<int|float>|null  $values
 * @return array<string, int|float>|null
 */
function statRow(?array $values): ?array
{
    if ($values === null || count($values) !== 5) {
        return null;
    }

    return [
        'speed' => $values[0],
        'stamina' => $values[1],
        'power' => $values[2],
        'guts' => $values[3],
        'wit' => $values[4],
    ];
}

/** The client word for a support card's type key (`intelligence` is Wit, `friend` is Pal). */
function typeWord(string $type): string
{
    return match ($type) {
        'intelligence' => 'Wit',
        'friend' => 'Pal',
        default => ucfirst($type),
    };
}

/** The client word for a card's rarity code. */
function rarityWord(int $rarity): string
{
    return match ($rarity) {
        1 => 'R',
        2 => 'SR',
        3 => 'SSR',
        default => (string) $rarity,
    };
}

/*
 * ============================================================================
 * 1. THE TRAINEE ROSTER
 * ============================================================================
 *
 * The roster is the `umamusume` table (every trainee the catalogue holds) with
 * each trainee's card forms joined on, and the profile the character document
 * carries joined on where one exists.
 */

$cardSeed = [];
foreach (readJson($seedDir.'/gametora-characters.e9e9ee6d.json') as $card) {
    $cardSeed[(int) $card['card_id']] = $card;
}

$characterDocument = [];
foreach (readJson($seedDir.'/characters.c6676539.json') as $character) {
    $characterDocument[(int) $character['char_id']] = $character;
}

$profiles = [];
foreach (rows($db, 'select * from umamusume_profiles order by umamusume_id') as $profile) {
    $profiles[(int) $profile['umamusume_id']] = $profile;
}

$formsByTrainee = [];
foreach (rows($db, 'select * from character_cards order by umamusume_id, card_id') as $card) {
    $formsByTrainee[(int) $card['umamusume_id']][] = $card;
}

/*
 * WHAT IS EXCLUDED, AND WHY
 *
 * A development database is a working surface, and this one carries rows that
 * the game does not: four trainees named "Form Counted 1" to "Form Counted 4"
 * and twelve card forms titled "[Sample Form]", both written by a test
 * fixture against the `.test` host. They are in the `umamusume` and
 * `character_cards` tables because a test wrote them there, and the app's own
 * counters include them, which is why the live dashboard reads 71 trainees
 * where the catalogue holds 67 real ones.
 *
 * A dataset that claims to reflect the game's content cannot carry them, so
 * the rule is a join and not a name filter: a trainee is in the dataset when
 * the pipeline stamped it with a source reference AND the committed character
 * document states that character, and a form is in it when the committed
 * character-card body states that card. Every row the join leaves out is then
 * checked to be a row that was never sourced, so a real trainee that falls
 * out of the join fails the build instead of vanishing from the dataset.
 */

$roster = [];
$excludedTrainees = [];
$excludedForms = [];
foreach (rows($db, 'select * from umamusume order by id') as $trainee) {
    $id = (int) $trainee['id'];
    $reference = $trainee['external_ref'];
    $sourceId = is_string($reference) && str_starts_with($reference, 'gametora:char:')
        ? (int) substr($reference, strlen('gametora:char:'))
        : null;

    if ($sourceId === null || ! isset($characterDocument[$sourceId])) {
        $excludedTrainees[] = ['id' => $id, 'name' => $trainee['name'], 'reference' => $reference];

        continue;
    }

    $profile = $profiles[$id] ?? null;
    $profileBlock = null;
    if ($profile !== null) {
        $birth = null;
        if ($profile['birth_year'] !== null) {
            $birth = sprintf(
                '%04d-%02d-%02d',
                (int) $profile['birth_year'],
                (int) $profile['birth_month'],
                (int) $profile['birth_day']
            );
        }
        $profileBlock = [
            'voiceActor' => $profile['va_en'] !== null && $profile['va_en'] !== '' ? $profile['va_en'] : null,
            'voiceActorJa' => $profile['va_ja'] !== null && $profile['va_ja'] !== '' ? $profile['va_ja'] : null,
            'birthDate' => $birth,
            'heightCm' => $profile['height'] !== null ? (int) $profile['height'] : null,
            'measurements' => [
                'bust' => $profile['three_sizes_b'] !== null ? (int) $profile['three_sizes_b'] : null,
                'waist' => $profile['three_sizes_w'] !== null ? (int) $profile['three_sizes_w'] : null,
                'hip' => $profile['three_sizes_h'] !== null ? (int) $profile['three_sizes_h'] : null,
            ],
        ];
    }

    $forms = [];
    foreach ($formsByTrainee[$id] ?? [] as $card) {
        $seed = $cardSeed[(int) $card['card_id']] ?? null;
        if ($seed === null) {
            $excludedForms[] = [
                'cardId' => (int) $card['card_id'],
                'title' => $card['title'],
                'sourceUrl' => $card['source_url'],
            ];

            continue;
        }
        $evolution = [];
        foreach (json_decode((string) $card['skills_evo'], true) ?: [] as $pair) {
            $evolution[] = ['from' => (int) $pair['old'], 'to' => (int) $pair['new']];
        }
        $forms[] = [
            'id' => 'card-'.(int) $card['card_id'],
            'cardId' => (int) $card['card_id'],
            'title' => $card['title'],
            'titleJa' => $seed['title_jp'] ?? null,
            'rarity' => (int) $card['rarity'],
            'rarityWord' => rarityWord((int) $card['rarity']),
            'isDebutForm' => (bool) $card['is_debut_form'],
            'globalReleaseDate' => day((string) $card['global_release_date']),
            'baseStats' => statRow($seed['base_stats'] ?? null),
            'growthRate' => statRow($seed['stat_bonus'] ?? null),
            'breakthrough' => [
                'fourStar' => statRow($seed['four_star_stats'] ?? null),
                'fiveStar' => statRow($seed['five_star_stats'] ?? null),
            ],
            'skills' => [
                'innate' => array_map('intval', json_decode((string) $card['skills_innate'], true) ?: []),
                'unique' => array_map('intval', json_decode((string) $card['skills_unique'], true) ?: []),
                'awakening' => array_map('intval', json_decode((string) $card['skills_awakening'], true) ?: []),
                'event' => array_map('intval', json_decode((string) $card['skills_event'], true) ?: []),
                'evolution' => $evolution,
            ],
        ];
    }

    $roster[] = [
        'id' => 'umamusume-'.$sourceId,
        'sourceId' => $sourceId,
        'slug' => $trainee['slug'],
        'name' => $trainee['name'],
        'nameJa' => $trainee['name_ja'],
        'releaseStatus' => $trainee['release_status'],
        'jpDebutDate' => day((string) $trainee['jp_debut_date']),
        'globalDebutDate' => day((string) $trainee['global_debut_date']),
        'profile' => $profileBlock,
        'aptitude' => [
            'turf' => $trainee['aptitude_turf'],
            'dirt' => $trainee['aptitude_dirt'],
            'sprint' => $trainee['aptitude_sprint'],
            'mile' => $trainee['aptitude_mile'],
            'medium' => $trainee['aptitude_medium'],
            'long' => $trainee['aptitude_long'],
            'frontRunner' => $trainee['aptitude_front_runner'],
            'paceChaser' => $trainee['aptitude_pace_chaser'],
            'lateSurger' => $trainee['aptitude_late_surger'],
            'endCloser' => $trainee['aptitude_end_closer'],
        ],
        'forms' => $forms,
    ];
}

/*
 * ============================================================================
 * 2. THE SUPPORT CARDS
 * ============================================================================
 *
 * The card's own rows, joined with the effect dictionary so a surface can
 * print the effect's name and its unit beside the number.
 *
 * The effect row is a positional vector of eleven anchors (levels 1, 5, 10,
 * 15, 20, 25, 30, 35, 40, 45 and 50) plus the effect's id, and -1 means the
 * source holds no entry at that level rather than that the effect is zero
 * (`app/Services/SupportCardEffects.php`). So the stated anchors are kept as
 * they are and the absent ones are left out: a missing level is not a zero.
 */

$effectDictionary = [];
foreach (rows($db, 'select * from support_effects order by effect_id') as $effect) {
    $effectDictionary[(int) $effect['effect_id']] = $effect;
}

/*
 * The dictionary is shipped ONCE, as its own file, and a card's effect row
 * carries the id to join on. The database does the same, and repeating the
 * dictionary on all 559 cards would add three quarters of a megabyte of the
 * same thirty-five rows.
 */
$supportEffectRows = [];
foreach ($effectDictionary as $effectId => $effect) {
    $supportEffectRows[] = [
        'id' => 'effect-'.$effectId,
        'effectId' => $effectId,
        'name' => $effect['name_en'],
        'nameJa' => $effect['name_ja'],
        'calc' => $effect['calc'],
        'symbol' => $effect['symbol'],
        'description' => $effect['description_en'],
    ];
}

$anchorLevels = [1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

$cards = [];
foreach (rows($db, 'select * from support_cards order by support_id') as $card) {
    $cardEffects = [];
    foreach (json_decode((string) $card['effects'], true) ?: [] as $row) {
        if (count($row) !== count($anchorLevels) + 1) {
            continue;
        }
        $effectId = (int) $row[0];
        $levels = [];
        $cap = null;
        foreach ($anchorLevels as $index => $level) {
            $value = $row[$index + 1];
            if ($value === -1) {
                continue;
            }
            $levels[(string) $level] = (int) $value;
            $cap = ['level' => $level, 'value' => (int) $value];
        }
        if ($cap === null) {
            continue;
        }
        $cardEffects[] = [
            'effectId' => $effectId,
            'levels' => $levels,
            'cap' => $cap,
        ];
    }

    $type = (string) $card['type'];
    $cards[] = [
        'id' => 'support-'.(int) $card['support_id'],
        'sourceId' => (int) $card['support_id'],
        'character' => [
            'sourceId' => (int) $card['char_id'],
            'name' => $card['char_name'],
            'nameJa' => $card['name_ja'],
        ],
        'title' => $card['title_en'],
        'titleJa' => $card['title_ja'],
        'rarity' => (int) $card['rarity'],
        'rarityWord' => rarityWord((int) $card['rarity']),
        'type' => $type,
        'typeWord' => typeWord($type),
        'releaseJp' => day((string) $card['release_jp']),
        'releaseGlobal' => day((string) $card['release_global']),
        'effects' => $cardEffects,
        'hintSkills' => array_map('intval', json_decode((string) $card['hint_skills'], true) ?: []),
        'eventSkills' => array_map('intval', json_decode((string) $card['event_skills'], true) ?: []),
    ];
}

/*
 * ============================================================================
 * 3. THE SKILLS
 * ============================================================================
 *
 * The skill table, joined with the export body for the three fields the table
 * does not keep: the source's own activation flag, the gate keys the source
 * lists, and the publisher's English.
 *
 * The type word is the pipeline's derivation, not the export's field, and the
 * export's `type` field is a list of gate keys that say when a skill fires
 * rather than what kind it is. The derivation is the parser's, and it is
 * recorded as derived: 27 and 22 are Speed, 9 and 28 are Recovery, and 1, 2
 * and 3 are Passive only where the effect's value is positive
 * (`app/Services/DataPipeline/Parsers/GametoraSkillsParser.php`).
 */

$skillSeed = [];
foreach (readJson($seedDir.'/skills.609afe88.json') as $skill) {
    $skillSeed[(int) $skill['id']] = $skill;
}

$skills = [];
foreach (rows($db, 'select * from skills order by id') as $skill) {
    $sourceId = $skill['export_id'] !== null ? (int) $skill['export_id'] : (int) $skill['id'];
    $seed = $skillSeed[$sourceId] ?? null;

    $classCodes = [];
    if ($seed !== null) {
        $raw = $seed['type'] ?? null;
        $classCodes = is_array($raw) ? array_values($raw) : ($raw !== null ? [$raw] : []);
    }

    $conditions = [];
    foreach (json_decode((string) $skill['condition_groups'], true) ?: [] as $group) {
        $conditionEffects = [];
        foreach ($group['effects'] ?? [] as $effect) {
            $conditionEffects[] = ['type' => (int) $effect['type'], 'value' => $effect['value']];
        }
        $conditions[] = [
            'condition' => $group['condition'] ?? null,
            'precondition' => $group['precondition'] ?? null,
            'baseTime' => isset($group['base_time']) ? (int) $group['base_time'] : null,
            'effects' => $conditionEffects,
        ];
    }

    $skills[] = [
        'id' => 'skill-'.$sourceId,
        'sourceId' => $sourceId,
        'name' => $skill['name'],
        'nameJa' => $skill['name_ja'],
        // The pipeline's derived word. Null where the evidence stops: twenty-two
        // effect codes are unmapped, and a negative value voids the claim.
        'type' => $skill['type'],
        'spCost' => $skill['sp_cost'] !== null ? (int) $skill['sp_cost'] : null,
        // The source's class code. 1 and 2 carry a cost, 3, 4 and 5 are bound
        // to a character, and 6 is an evolved row. The source names no tier.
        'rarity' => $skill['rarity'] !== null ? (int) $skill['rarity'] : null,
        'isUnique' => (bool) $skill['is_unique'],
        'releaseStatus' => $skill['release_status'],
        // The source's own activation flag. No tracked source decodes it.
        'activation' => isset($seed['activation']) ? (int) $seed['activation'] : null,
        // The source's gate keys. Recording them is a fact; what they mean is
        // not established, so no meaning is attached here.
        'classCodes' => $classCodes,
        'conditions' => $conditions,
        // No client string exists for a skill's description (G-SK-20), so this
        // field is null on every row and a surface that binds to it prints the
        // "no client string" state rather than a paraphrase of the export.
        'description' => null,
        // The publisher's English, kept as source data. It is not the client's
        // wording, and it is not to be rendered on a display path.
        'sourceText' => [
            'en' => $seed['desc_en'] ?? null,
            'end' => $seed['endesc'] ?? null,
        ],
    ];
}

/*
 * ============================================================================
 * 4. THE CHECKS
 * ============================================================================
 *
 * The counts below are the ones the app's own pages print, so a drift between
 * this file and the catalogue is caught here rather than in a screenshot.
 */

$problems = [];

// Every row the join leaves out must be a row that was never sourced. A real
// trainee or a real card that falls out of the join is a build failure, not a
// quiet omission: a dataset that silently drops game content is worse than
// one that refuses to be written.
foreach ($excludedTrainees as $row) {
    if ($row['reference'] !== null) {
        $problems[] = "trainee {$row['id']} ({$row['name']}) carries a source reference but is not in the character document";
    }
}
foreach ($excludedForms as $row) {
    $url = (string) ($row['sourceUrl'] ?? '');
    if (! str_contains($url, '.test/')) {
        $problems[] = "card {$row['cardId']} ({$row['title']}) is not in the character-card body and was not written by a test";
    }
}

// The counts the dataset holds must equal the counts the catalogue holds once
// the unsourced rows are taken out.
$sourcedTrainees = (int) $db->query('select count(*) from umamusume where external_ref is not null')->fetchColumn();
if (count($roster) !== $sourcedTrainees) {
    $problems[] = 'the roster holds '.count($roster)." trainees, the catalogue holds {$sourcedTrainees} sourced ones";
}

$formCount = 0;
foreach ($roster as $trainee) {
    $formCount += count($trainee['forms']);
}
$sourcedCards = (int) $db->query('select count(*) from character_cards where source_url like \'https://gametora.com/%\'')->fetchColumn();
if ($formCount !== $sourcedCards) {
    $problems[] = "the roster carries {$formCount} forms, the catalogue holds {$sourcedCards} sourced ones";
}

$supportCount = (int) $db->query('select count(*) from support_cards')->fetchColumn();
if (count($cards) !== $supportCount) {
    $problems[] = 'the dataset holds '.count($cards)." support cards, the table holds {$supportCount}";
}

// Every effect a card carries must be in the dictionary, or a surface that joins
// the two cannot name the effect it is printing.
$knownEffects = array_column($supportEffectRows, 'effectId');
foreach ($cards as $card) {
    foreach ($card['effects'] as $effect) {
        if (! in_array($effect['effectId'], $knownEffects, true)) {
            $problems[] = "card {$card['id']} carries effect {$effect['effectId']}, and the dictionary does not";
        }
    }
}
if (count($supportEffectRows) !== (int) $db->query('select count(*) from support_effects')->fetchColumn()) {
    $problems[] = 'the dictionary does not hold every row the table holds';
}

$skillCount = (int) $db->query('select count(*) from skills')->fetchColumn();
if (count($skills) !== $skillCount) {
    $problems[] = 'the dataset holds '.count($skills)." skills, the table holds {$skillCount}";
}

// The growth row is 30 points on every card, in 5-point steps (1.3.5). A row
// that does not sum to 30 would mean the join picked the wrong card.
foreach ($roster as $trainee) {
    foreach ($trainee['forms'] as $form) {
        if ($form['growthRate'] === null) {
            $problems[] = "{$form['id']} carries no growth row";

            continue;
        }
        $total = array_sum($form['growthRate']);
        if ($total !== 30) {
            $problems[] = "{$form['id']} growth sums to {$total}, and every card's sums to 30";
        }
    }
}

// A base row of 450, 425 or 400, by the card's own rarity (1.3.5).
$expectedTotals = [1 => 400, 2 => 425, 3 => 450];
foreach ($roster as $trainee) {
    foreach ($trainee['forms'] as $form) {
        if ($form['baseStats'] === null) {
            $problems[] = "{$form['id']} carries no base stats";

            continue;
        }
        $total = array_sum($form['baseStats']);
        $expected = $expectedTotals[$form['rarity']] ?? null;
        if ($expected !== null && $total !== $expected) {
            $problems[] = "{$form['id']} base sums to {$total}, and a rarity {$form['rarity']} card sums to {$expected}";
        }
    }
}

if ($problems !== []) {
    fwrite(STDERR, "The dataset does not match the catalogue:\n - ".implode("\n - ", $problems)."\n");
    exit(1);
}

/*
 * ============================================================================
 * 5. THE FILES
 * ============================================================================
 */

/*
 * The freeze record. Every generated file carries the inputs it was built from, so a reader can tell
 * a current capture from a stale one without opening the seed set: the commit the inputs were read
 * at, the capture instant, and a sha256 digest per seed file (the seed files are already named by
 * content hash, so a renamed-then-edited seed shows here as a changed digest rather than as the same
 * filename with different content). A capture older than the seed set it was built from is stale -
 * see assets/data/README.md's ageing rule.
 */
$frozenRecord = static function () use ($here): string {
    $root = dirname($here, 3);

    $commit = trim((string) @shell_exec('git -C '.escapeshellarg($root).' rev-parse --short HEAD 2>&1'));
    if (preg_match('/^[0-9a-f]{7,40}$/', $commit) !== 1) {
        $commit = 'unavailable (git not runnable here)';
    }

    $seeds = [];
    foreach (glob($root.'/database/seeders/data/*.json') ?: [] as $seed) {
        $seeds[] = basename($seed).' '.substr((string) hash_file('sha256', $seed), 0, 12);
    }
    sort($seeds);

    /*
     * One instant, one zone. The host PHP default is UTC, so `date('c')` alone stamps a capture that
     * reads as the previous day beside every dated note in this repository (all of which are written in
     * the display zone). The offset is printed rather than implied, so the instant stays unambiguous.
     */
    $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur'));

    $lines = [
        '   FROZEN '.$now->format('Y-m-d').' · capture '.$now->format(DATE_ATOM).' · source commit '.$commit,
        '   Seed identity (sha256, first 12 hex, of database/seeders/data/*.json):',
    ];
    foreach ($seeds as $seed) {
        $lines[] = '     '.$seed;
    }
    $lines[] = '   Ageing rule: see README.md in this directory.';

    return implode("\n", $lines)."\n";
};

$header = static function (string $title, string $body) use ($frozenRecord): string {
    return "/* ============================================================================\n".
        "   TRAINER DESK v2.0.0 HFW — {$title}.\n".
        "   GENERATED FILE. Do not hand-edit; run `php build-data.php` to regenerate.\n".
        "   \n".
        "{$body}\n".
        $frozenRecord().
        "   ============================================================================ */\n";
};

$umamusumeBody = <<<'BODY'
   The catalogue's own roster: every trainee the `umamusume` table holds, each
   one with the card forms joined on, the growth row and the star-breakthrough
   rows the export states, the nine aptitude grades the catalogue keeps, and the
   profile the character document carries where one exists.

   Provenance: `database/database.sqlite` (`umamusume`, `character_cards`,
   `umamusume_profiles`) joined with `database/seeders/data/gametora-characters.*.json`
   for the base stats, the growth row and the breakthrough rows.

   The growth row is the source's `stat_bonus`, which the client reads as the
   Growth Rate row (`docs/UMAMUSUME_REFERENCE.md` §1.3.5). A zero is a real
   figure and is printed as `+0%`, never as a blank.
BODY;

$supportBody = <<<'BODY'
   The support-card catalogue: every card the `support_cards` table holds, with
   its effect rows resolved against the effect dictionary, so a surface can
   print the effect's name and its unit beside the number.

   Provenance: `database/database.sqlite` (`support_cards`, `support_effects`).

   An effect row is eleven anchors plus the effect's id, and -1 means the
   source holds no entry at that level rather than that the effect is zero, so
   the absent levels are left out and `cap` names the last anchor the source
   states. `type` is the export's key and `typeWord` is the client's word for
   it (`intelligence` is Wit, `friend` is Pal).
BODY;

$supportEffectsBody = <<<'BODY'
   The support-effect dictionary: the thirty-five effects a card can carry, each
   with the client's name, the source's unit word and the source's own
   description of what it does.

   Provenance: `database/database.sqlite` (`support_effects`).

   This file exists so the thirty-five rows are stated once. A card's effect
   row carries the `effectId` to join on, which is the shape the database holds
   and the shape a surface that prints a card's effects wants.

   `calc` is how the effect combines, and the source states it for four rows
   only: `mult` on the friendship bonus, the mood effect and one more, and
   `add` on the specialty priority. Where the source states no mode the field
   is null, because naming a fourth mode would state a rule no source holds.
BODY;

$skillsBody = <<<'BODY'
   The skill catalogue: every row the `skills` table holds, with the engine
   data the source states and nothing that would have to be invented.

   Provenance: `database/database.sqlite` (`skills`) joined with
   `database/seeders/data/skills.*.json` for the activation flag, the gate keys
   and the publisher's English.

   `type` is the pipeline's DERIVED word, not the export's field: 27 and 22 are
   Speed, 9 and 28 are Recovery, and 1, 2 and 3 are Passive only where the
   effect's value is positive. `rarity` is the source's class code and names no
   tier. `classCodes` are the source's own gate keys, and no meaning is
   attached to them because none is established.

   `description` is null on every row. No client string for a skill's
   description exists in this repository (G-SK-20), and the export's English is
   the publisher's, not the client's. `sourceText` carries that publisher's
   English as source data; it is reference material and is not to be rendered
   on a display path.
BODY;

$footer = <<<'BODY'
   Loaded as a classic script: `window.TRAINER_DESK_DATA` is the namespace, and
   the same object is exported through CommonJS for a Node-side consumer.
BODY;

$files = [
    'umamusume.js' => $header('the trainee roster', $umamusumeBody)."\n".
        "(function (root, factory) {\n".
        "    'use strict';\n".
        "    var data = factory();\n".
        "    if (typeof module === 'object' && module.exports) { module.exports = data; }\n".
        "    root.TRAINER_DESK_DATA = root.TRAINER_DESK_DATA || {};\n".
        "    root.TRAINER_DESK_DATA.umamusume = data.umamusume;\n".
        "    root.TRAINER_DESK_DATA.umamusumeSchema = data.umamusumeSchema;\n".
        "})(typeof globalThis !== 'undefined' ? globalThis : this, function () {\n".
        "    'use strict';\n\n".
        '    var UMAMUSUME = '.jsRecords($roster).";\n\n".
        '    var UMAMUSUME_SCHEMA = '.js([
            'entity' => 'Umamusume (a trainee)',
            'count' => count($roster),
            'source' => 'the catalogue\'s own `umamusume` table, joined with the committed GameTora export body',
            'fields' => [
                'id' => 'the key that never changes, `umamusume-` and the source id',
                'sourceId' => 'the source\'s own character id',
                'slug' => 'the catalogue\'s URL slug',
                'name' => 'the client name',
                'nameJa' => 'the Japanese name',
                'releaseStatus' => 'the catalogue\'s release state',
                'jpDebutDate' => 'the first Japanese release, `YYYY-MM-DD`',
                'globalDebutDate' => 'the first Global release, `YYYY-MM-DD`',
                'profile' => 'the character document\'s facts, or null where the catalogue holds no profile',
                'aptitude' => 'the nine aptitude grades, A to G',
                'forms' => 'the card forms this trainee has, debut form first',
            ],
            'formFields' => [
                'id' => 'the key that never changes, `card-` and the source id',
                'cardId' => 'the source\'s own card id',
                'title' => 'the client name of the form, in brackets',
                'titleJa' => 'the Japanese name of the form, in brackets',
                'rarity' => 'the source\'s rarity code, 1 to 3',
                'rarityWord' => 'the client\'s word for that code: R, SR or SSR',
                'isDebutForm' => 'whether this is the debut form',
                'globalReleaseDate' => 'the first Global release, `YYYY-MM-DD`',
                'baseStats' => 'the five base values, which sum to 450, 425 or 400 by rarity',
                'growthRate' => 'the Growth Rate row; 30 points across one to three stats, zeros are real',
                'breakthrough' => 'the four-star and five-star rows, which sum to 500 and 550',
                'skills' => 'the innate, unique, awakening and event skill ids, and the evolution pairs',
            ],
        ], 4).";\n\n".
        "    return { umamusume: UMAMUSUME, umamusumeSchema: UMAMUSUME_SCHEMA };\n".
        "});\n",
    'support-cards.js' => $header('the support cards', $supportBody)."\n".
        "(function (root, factory) {\n".
        "    'use strict';\n".
        "    var data = factory();\n".
        "    if (typeof module === 'object' && module.exports) { module.exports = data; }\n".
        "    root.TRAINER_DESK_DATA = root.TRAINER_DESK_DATA || {};\n".
        "    root.TRAINER_DESK_DATA.supportCards = data.supportCards;\n".
        "    root.TRAINER_DESK_DATA.supportCardsSchema = data.supportCardsSchema;\n".
        "})(typeof globalThis !== 'undefined' ? globalThis : this, function () {\n".
        "    'use strict';\n\n".
        '    var SUPPORT_CARDS = '.jsRecords($cards).";\n\n".
        '    var SUPPORT_CARDS_SCHEMA = '.js([
            'entity' => 'Support card',
            'count' => count($cards),
            'source' => 'the catalogue\'s own `support_cards` table, with the effect rows resolved against the `support_effects` dictionary',
            'join' => 'each effect row carries `effectId`; join it against TRAINER_DESK_DATA.supportEffects to get the effect\'s name, unit and description',
            'fields' => [
                'id' => 'the key that never changes, `support-` and the source id',
                'sourceId' => 'the source\'s own support card id',
                'character' => 'the trainee the card belongs to',
                'title' => 'the client name of the card, in brackets',
                'titleJa' => 'the Japanese name of the card, in brackets',
                'rarity' => 'the source\'s rarity code, 1 to 3',
                'rarityWord' => 'the client\'s word for that code: R, SR or SSR',
                'type' => 'the export\'s type key: speed, stamina, power, guts, intelligence, friend, group',
                'typeWord' => 'the client\'s word for that key: Wit for intelligence, Pal for friend',
                'releaseJp' => 'the first Japanese release, `YYYY-MM-DD`',
                'releaseGlobal' => 'the first Global release, `YYYY-MM-DD`',
                'effects' => 'the effect rows, each with its stated anchors and its cap',
                'hintSkills' => 'the skill ids the card can hint',
                'eventSkills' => 'the skill ids the card can grant as an event',
            ],
            'effectFields' => [
                'effectId' => 'the source\'s effect id; join it against the dictionary',
                'levels' => 'the stated anchors, keyed by card level; a level the source does not state is absent, not zero',
                'cap' => 'the last anchor the source states, and its value',
            ],
        ], 4).";\n\n".
        "    return { supportCards: SUPPORT_CARDS, supportCardsSchema: SUPPORT_CARDS_SCHEMA };\n".
        "});\n",
    'support-effects.js' => $header('the support-effect dictionary', $supportEffectsBody)."\n".
        "(function (root, factory) {\n".
        "    'use strict';\n".
        "    var data = factory();\n".
        "    if (typeof module === 'object' && module.exports) { module.exports = data; }\n".
        "    root.TRAINER_DESK_DATA = root.TRAINER_DESK_DATA || {};\n".
        "    root.TRAINER_DESK_DATA.supportEffects = data.supportEffects;\n".
        "    root.TRAINER_DESK_DATA.supportEffectsSchema = data.supportEffectsSchema;\n".
        "})(typeof globalThis !== 'undefined' ? globalThis : this, function () {\n".
        "    'use strict';\n\n".
        '    var SUPPORT_EFFECTS = '.jsRecords($supportEffectRows).";\n\n".
        '    var SUPPORT_EFFECTS_SCHEMA = '.js([
            'entity' => 'Support effect',
            'count' => count($supportEffectRows),
            'source' => 'the catalogue\'s own `support_effects` table',
            'fields' => [
                'id' => 'the key that never changes, `effect-` and the source id',
                'effectId' => 'the source\'s effect id; the join key for a card\'s effect row',
                'name' => 'the client name of the effect',
                'nameJa' => 'the Japanese name',
                'calc' => 'how the effect combines: `mult`, `add`, or null where the source states no mode',
                'symbol' => 'the source\'s unit word: percent, none, level, or null',
                'description' => 'the source\'s own description of the effect, kept verbatim',
            ],
        ], 4).";\n\n".
        "    return { supportEffects: SUPPORT_EFFECTS, supportEffectsSchema: SUPPORT_EFFECTS_SCHEMA };\n".
        "});\n",
    'skills.js' => $header('the skills', $skillsBody)."\n".
        "(function (root, factory) {\n".
        "    'use strict';\n".
        "    var data = factory();\n".
        "    if (typeof module === 'object' && module.exports) { module.exports = data; }\n".
        "    root.TRAINER_DESK_DATA = root.TRAINER_DESK_DATA || {};\n".
        "    root.TRAINER_DESK_DATA.skills = data.skills;\n".
        "    root.TRAINER_DESK_DATA.skillsSchema = data.skillsSchema;\n".
        "})(typeof globalThis !== 'undefined' ? globalThis : this, function () {\n".
        "    'use strict';\n\n".
        '    var SKILLS = '.jsRecords($skills).";\n\n".
        '    var SKILLS_SCHEMA = '.js([
            'entity' => 'Skill',
            'count' => count($skills),
            'source' => 'the catalogue\'s own `skills` table, joined with the committed GameTora export body',
            'fields' => [
                'id' => 'the key that never changes, `skill-` and the source id',
                'sourceId' => 'the source\'s own skill id',
                'name' => 'the client name',
                'nameJa' => 'the Japanese name',
                'type' => 'the pipeline\'s DERIVED word: Speed, Passive, Recovery, or null where the evidence stops',
                'spCost' => 'the SP cost, or null where the source states none',
                'rarity' => 'the source\'s class code: 1 and 2 are learnable, 3, 4 and 5 are character-bound, 6 is evolved',
                'isUnique' => 'the mark the pipeline derives from the class code, not from a card\'s own list',
                'releaseStatus' => 'the catalogue\'s release state',
                'activation' => 'the source\'s own activation flag; no tracked source decodes it',
                'classCodes' => 'the source\'s gate keys; recording them is a fact, their meaning is not established',
                'conditions' => 'the activation groups, with the engine\'s own expressions and effect codes',
                'description' => 'always null: no client string for a skill description exists (G-SK-20)',
                'sourceText' => 'the publisher\'s English, as source data; reference only, not a display path',
            ],
            'conditionFields' => [
                'condition' => 'the engine\'s activation expression, verbatim',
                'precondition' => 'the engine\'s precondition, verbatim, or null',
                'baseTime' => 'the engine\'s base time in milliseconds',
                'effects' => 'the effect rows: the source\'s effect code and its value',
            ],
        ], 4).";\n\n".
        "    return { skills: SKILLS, skillsSchema: SKILLS_SCHEMA };\n".
        "});\n",
];

if (! is_dir($outDir) && ! mkdir($outDir, 0o755, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

foreach ($files as $name => $contents) {
    file_put_contents($outDir.'/'.$name, $contents);
    printf("wrote %s (%d KB)\n", 'assets/data/'.$name, (int) (strlen($contents) / 1024));
}

printf(
    "%d trainees, %d forms, %d support cards, %d skills\n",
    count($roster),
    $formCount,
    count($cards),
    count($skills)
);
printf(
    "excluded: %d unsourced trainees, %d unsourced forms\n",
    count($excludedTrainees),
    count($excludedForms)
);
