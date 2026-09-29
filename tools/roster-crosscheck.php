<?php

declare(strict_types=1);

/*
 * Tier A cross-check of every Global costume card (Task 8, plan 2026-09-29).
 *
 * `docs/SOURCE-OF-TRUTH.md` §5:152 — "A Tier B dataset (GameTora) needs A- or S-tier
 * confirmation before a claim becomes app data". This is the crossing: it reads a saved
 * GameTora character-card body, keeps the cards the shipped parser calls Global, looks each
 * one up on the two saved Tier A pages, and prints one Markdown row per card with a verdict.
 *
 * It fetches nothing, reads nothing from and writes nothing to the database, and edits no
 * title: a `[Global]` client string is source data (`CONSTRAINTS.md` C-4), so it is printed
 * verbatim, brackets included, and the comparison happens on folded copies instead.
 *
 *   php tools/roster-crosscheck.php > docs/data/roster-crosscheck-table.md
 *
 * Inputs, overridable with --gametora= --wiki= --game8= --tier-a= :
 *   research-scratch/data/json/character-cards.json      (the Tier B body)
 *   research-scratch/data/html/umamusu-list-of-trainees-2026-09-29.html
 *   research-scratch/data/html/game8-characters-2026-09-29.html
 *   research-scratch/data/json/tier-a-rows.json          (derived; written if absent)
 *
 * A verdict is a claim about the CARD, keyed on `card_id`, never about the trainee a source
 * has it attached to: `unconfirmed` answers "do two independent sources attest that this
 * card exists, with this title, date and rarity?" (owner ruling 2026-09-29), so a card that
 * re-parents keeps its verdict and no verdict here is keyed on a trainee.
 */

require dirname(__DIR__).'/vendor/autoload.php';

use App\Services\DataPipeline\NameNormalizer;
use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;

const WIKI_PAGE = 'https://umamusu.wiki/Game:List_of_Trainees';
const GAME8_PAGE = 'https://game8.co/games/Umamusume-Pretty-Derby/archives/535926';

/** The ornamental glyphs a `[Global]` costume name is decorated with and a guide page drops. */
const ORNAMENTAL_GLYPHS = ['☆', '★', '♡', '♥', '♪', '✩', '✧', '＋'];

$paths = [
    'gametora' => 'research-scratch/data/json/character-cards.json',
    'wiki' => 'research-scratch/data/html/umamusu-list-of-trainees-2026-09-29.html',
    'game8' => 'research-scratch/data/html/game8-characters-2026-09-29.html',
    'tier-a' => 'research-scratch/data/json/tier-a-rows.json',
];

/**
 * The highest fold a witness may be matched on. `3` is the rule the cross-check ran under;
 * `--max-tier=1` reproduces the brief's strict rule — brackets off, case folded, whitespace
 * collapsed, nothing deeper — so the two count lines in the evidence file are both reproducible
 * from one run rather than one of them being hand-derived prose.
 */
$maxTier = 3;

foreach (array_slice($argv, 1) as $argument) {
    if (preg_match('/^--max-tier=(\d)$/', $argument, $m) === 1) {
        if ((int) $m[1] < 1 || (int) $m[1] > 3) {
            fwrite(STDERR, "roster-crosscheck: --max-tier takes 1, 2 or 3, got {$m[1]}\n");
            exit(1);
        }

        $maxTier = (int) $m[1];

        continue;
    }

    if (preg_match('/^--([a-z-]+)=(.*)$/', $argument, $m) === 1 && array_key_exists($m[1], $paths)) {
        $paths[$m[1]] = $m[2];

        continue;
    }

    fwrite(STDERR, "roster-crosscheck: unrecognised argument {$argument}\n");
    exit(1);
}

foreach (['gametora', 'wiki', 'game8'] as $role) {
    if (! is_file($paths[$role])) {
        fwrite(STDERR, "roster-crosscheck: no {$role} body at {$paths[$role]}\n");
        exit(1);
    }
}

$cards = (new GametoraCharacterCardParser)->parse((string) file_get_contents($paths['gametora']));

if ($cards === []) {
    fwrite(STDERR, "roster-crosscheck: the shipped parser read no Global cards out of {$paths['gametora']}\n");
    exit(1);
}

if (is_file($paths['tier-a'])) {
    $tierA = json_decode((string) file_get_contents($paths['tier-a']), true);

    if (! is_array($tierA) || $tierA === []) {
        fwrite(STDERR, "roster-crosscheck: {$paths['tier-a']} is not a non-empty tier-a-rows JSON\n");
        exit(1);
    }
} else {
    $tierA = array_merge(readWikiTable($paths['wiki']), readGame8Page($paths['game8']));
    file_put_contents($paths['tier-a'], json_encode($tierA, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

$indexRows = static function (array $rows, string $source): array {
    $index = [];

    foreach ($rows as $row) {
        if ($row['source'] !== $source) {
            continue;
        }

        foreach (foldKey((string) $row['card_title_en']) as $tier => $key) {
            $index[$tier][$key][] = $row;
        }
    }

    return $index;
};

$wikiIndex = $indexRows($tierA, 'umamusu-wiki');
$game8Index = $indexRows($tierA, 'game8');

reportFoldAmbiguity($wikiIndex, 'umamusu.wiki', $maxTier);
reportFoldAmbiguity($game8Index, 'Game8', $maxTier);

// The other side of the bijection: two Global cards sharing a fold would let one witness stand
// for both, so the check runs over the cards as well as over the source rows.
$byCardKey = [];

foreach ($cards as $card) {
    foreach (foldKey((string) $card['title']) as $tier => $key) {
        if ((int) $tier > $maxTier) {
            continue;
        }

        $byCardKey[$tier][$key][] = (string) $card['card_id'];
    }
}

for ($tier = 1; $tier <= $maxTier; $tier++) {
    $shared = array_filter($byCardKey[$tier] ?? [], fn (array $ids): bool => count($ids) > 1);

    if ($shared !== []) {
        fwrite(STDERR, "roster-crosscheck: roster tier {$tier} has ".count($shared)." folded title(s) shared by more than one Global card:\n");

        foreach ($shared as $key => $ids) {
            fwrite(STDERR, "  key '{$key}' => card_ids ".implode(', ', $ids)."\n");
        }
    }
}

echo '| card_id | GameTora title | Game8 title | umamusu.wiki title | GameTora date | Tier A date | rarity | verdict |', "\n";
echo '|', str_repeat('---|', 8), "\n";

$counts = ['two-source-confirmed' => 0, 'single-source' => 0, 'conflict' => 0, 'unwitnessed' => 0];
$variants = [];
$conflicts = [];

foreach ($cards as $card) {
    $wikiHit = firstTierHit($wikiIndex, (string) $card['title'], $maxTier);
    $game8Hit = firstTierHit($game8Index, (string) $card['title'], $maxTier);
    $wikiRow = $wikiHit['row'];
    $tierADate = $wikiRow === null ? 'not listed' : (string) $wikiRow['global_release_date'];
    $tierARarity = $wikiRow === null ? null : $wikiRow['rarity'];

    // A date or rarity that disagrees with the Tier A page carries the row down to conflict
    // however many pages list the card, and `N/A` there against a real date here is a
    // disagreement about whether the card reached `[Global]` at all. A title is different:
    // the client string arbitrates it, so a differing spelling is logged, not downgraded.
    $dateDisagrees = $wikiRow !== null && $tierADate !== $card['global_release_date'];
    $rarityDisagrees = is_int($tierARarity) && $tierARarity !== $card['rarity'];

    $verdict = match (true) {
        $dateDisagrees || $rarityDisagrees => 'conflict',
        $game8Hit['row'] !== null && $wikiHit['row'] !== null => 'two-source-confirmed',
        $game8Hit['row'] !== null || $wikiHit['row'] !== null => 'single-source',
        default => 'unwitnessed',
    };

    $counts[$verdict]++;

    foreach (['game8' => $game8Hit, 'umamusu.wiki' => $wikiHit] as $page => $hit) {
        if ($hit['tier'] > 1) {
            $variants[] = sprintf(
                'card %d | tier %d | %s vs %s (%s) | %s',
                $card['card_id'],
                $hit['tier'],
                $card['title'],
                $hit['row']['card_title_en'],
                $page,
                $hit['row']['pointer'],
            );
        }
    }

    if ($verdict === 'conflict') {
        $conflicts[] = sprintf(
            'card %d | %s | %s | GameTora %s vs %s %s (%s)',
            $card['card_id'],
            $card['title'],
            $dateDisagrees ? 'Global date' : 'rarity',
            $dateDisagrees ? $card['global_release_date'] : $card['rarity'],
            $dateDisagrees ? $tierADate : $tierARarity,
            'umamusu.wiki',
            $wikiRow['pointer'] ?? '-',
        );
    }

    echo '| ', implode(' | ', [
        (string) $card['card_id'],
        cell((string) $card['title']),
        cell($game8Hit['row'] === null ? 'not listed' : (string) $game8Hit['row']['card_title_en']),
        cell($wikiRow === null ? 'not listed' : (string) $wikiRow['card_title_en']),
        cell((string) $card['global_release_date']),
        cell($tierADate),
        (string) $card['rarity'],
        $verdict,
    ]), " |\n";
}

// The counts and the two logs go to STDERR so stdout stays the Markdown table the evidence
// file embeds, and a reader can re-derive the variant and conflict sections by re-running
// the same command rather than trusting what was pasted in.
fwrite(STDERR, sprintf(
    "roster-crosscheck: %d cards | two-source-confirmed %d | single-source %d | conflict %d | unwitnessed %d\n",
    count($cards),
    $counts['two-source-confirmed'],
    $counts['single-source'],
    $counts['conflict'],
    $counts['unwitnessed'],
));

foreach ([
    'title witnesses above tier 1 (spelling variants, logged not reconciled)' => $variants,
    'conflicts' => $conflicts,
] as $heading => $lines) {
    fwrite(STDERR, "roster-crosscheck: {$heading}: ".count($lines)."\n");

    foreach ($lines as $line) {
        fwrite(STDERR, "  {$line}\n");
    }
}

/**
 * The three comparison folds of one title, cheapest first, as an ordered map the index and
 * the lookup share so the two cannot drift. Tier 1 is the brief's rule: surrounding brackets
 * off, case folded, whitespace runs collapsed. Tier 2 is the repo's own `NameNormalizer`
 * fold, which additionally drops combining marks and the four separators search already
 * ignores. Tier 3 strips the ornamental glyphs on top of that.
 *
 * @return array{1: string, 2: string, 3: string}
 */
function foldKey(string $title): array
{
    static $normalizer;
    static $cache = [];

    if (array_key_exists($title, $cache)) {
        return $cache[$title];
    }

    $normalizer ??= new NameNormalizer;

    $unbracketed = trim($title);

    if (preg_match('/^\[(.*)\]$/u', $unbracketed, $m) === 1) {
        $unbracketed = trim($m[1]);
    }

    $tier2 = $normalizer->normalize($unbracketed);

    return $cache[$title] = [
        '1' => mb_strtolower(preg_replace('/\s+/u', ' ', $unbracketed) ?? $unbracketed),
        '2' => $tier2,
        '3' => str_replace(ORNAMENTAL_GLYPHS, '', $tier2),
    ];
}

/**
 * The cheapest tier at which a title witnesses anything, at or below `$maxTier`.
 *
 * @param  array<int|string, list<array<string, mixed>>>  $index
 * @return array{tier: int, row: array<string, mixed>|null}
 */
function firstTierHit(array $index, string $title, int $maxTier): array
{
    foreach (foldKey($title) as $tier => $key) {
        if ((int) $tier > $maxTier) {
            break;
        }

        if (isset($index[$tier][$key][0])) {
            return ['tier' => (int) $tier, 'row' => $index[$tier][$key][0]];
        }
    }

    return ['tier' => 0, 'row' => null];
}

/**
 * Say out loud when a folded key stops being one-to-one, because that is the property the
 * deeper folds are safe on. `firstTierHit` takes the first row of a bucket, so once two
 * source rows share a fold, or two Global cards do, a tier-2/3 match stops being a
 * bijection and the verdict stops being defensible without reading which rows collided.
 * Nothing in the 2026-09-29 bodies collides; this is the guard for the next hash.
 *
 * @param  array<int|string, list<array<string, mixed>>>  $index
 */
function reportFoldAmbiguity(array $index, string $label, int $maxTier): void
{
    for ($tier = 1; $tier <= $maxTier; $tier++) {
        $shared = array_filter($index[$tier] ?? [], fn (array $rows): bool => count($rows) > 1);

        if ($shared === []) {
            continue;
        }

        fwrite(STDERR, "roster-crosscheck: {$label} tier {$tier} has ".count($shared)." folded key(s) matching more than one row:\n");

        foreach ($shared as $key => $rows) {
            $titles = array_map(fn (array $row): string => (string) $row['card_title_en'], $rows);
            fwrite(STDERR, "  key '{$key}' => ".implode(' | ', $titles)."\n");
        }
    }
}

/**
 * One Markdown cell. Pipes are escaped so a title cannot break the table; nothing else is
 * changed, because a title is a verbatim client string.
 */
function cell(string $value): string
{
    return str_replace('|', '\\|', $value);
}

/**
 * The wiki's single sortable wikitable: one row per card, with the English costume name in
 * the bolded link and the Japanese one under it, the trainee beside it, and the JP and
 * Global dates and the star rarity after that.
 *
 * @return list<array<string, mixed>>
 */
function readWikiTable(string $path): array
{
    $trs = htmlQuery($path, '//table//tr');
    $rows = [];

    foreach ($trs as $offset => $tr) {
        if ($offset === 0) {
            continue;
        }

        $cells = directCells($tr);

        if (count($cells) < 6) {
            continue;
        }

        $bold = $tr->getElementsByTagName('b')->item(0);
        $italic = $tr->getElementsByTagName('i')->item(0);
        $rarityCell = $cells[5];
        $sortValue = $rarityCell->getAttribute('data-sort-value');
        $stars = substr_count($rarityCell->textContent, '★');

        $rows[] = [
            'source' => 'umamusu-wiki',
            'page' => WIKI_PAGE,
            // Body rows are numbered from 1 for the first card under the header, so a reader
            // counting down the rendered table lands on row N+1 of it, header included.
            'pointer' => 'wikitable body row '.$offset.' (of '.(count($trs) - 1).')',
            'trainee_en' => plain($cells[2]),
            'card_title_en' => $bold !== null ? plain($bold) : plain($cells[1]),
            'card_title_jp' => $italic !== null ? plain($italic) : null,
            'rarity' => $sortValue !== '' ? (int) $sortValue : ($stars > 0 ? $stars : null),
            'jp_release_date' => plain($cells[3]),
            'global_release_date' => plain($cells[4]),
        ];
    }

    return $rows;
}

/**
 * Game8's character page names each trainee's costume inside the link text, "Trainee
 * (Costume)", and carries no date and no rarity for it: it witnesses a title, nothing more.
 * Duplicate tiles (the same pair in two tables) keep their first pointer.
 *
 * @return list<array<string, mixed>>
 */
function readGame8Page(string $path): array
{
    $rows = [];
    $seen = [];

    foreach (htmlQuery($path, '//table//a[contains(@class, "a-link")]') as $link) {
        if (preg_match('/^(.*)\s+\((.+)\)$/u', plain($link), $m) !== 1) {
            continue;
        }

        $key = foldKey($m[2])['3'].'|'.foldKey($m[1])['3'];

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;

        $table = 0;
        $row = 0;
        $cell = 0;

        for ($node = $link->parentNode; $node instanceof DOMElement; $node = $node->parentNode) {
            if ($node->nodeName === 'tr' && $row === 0) {
                $row = elementOrdinal($node);
            }

            if ($node->nodeName === 'td' && $cell === 0) {
                $cell = elementOrdinal($node);
            }

            if ($node->nodeName === 'table') {
                $table = tableOrdinal($node);

                break;
            }
        }

        $rows[] = [
            'source' => 'game8',
            'page' => GAME8_PAGE,
            'pointer' => "table {$table} row {$row} cell {$cell}",
            'trainee_en' => trim($m[1]),
            'card_title_en' => trim($m[2]),
            'card_title_jp' => null,
            'rarity' => null,
            'global_release_date' => null,
        ];
    }

    return $rows;
}

/**
 * @return list<DOMElement>
 */
function htmlQuery(string $path, string $query): array
{
    libxml_use_internal_errors(true);
    $document = new DOMDocument;

    if ($document->loadHTML((string) file_get_contents($path)) === false) {
        fwrite(STDERR, "roster-crosscheck: {$path} did not parse as HTML\n");
        exit(1);
    }

    $nodes = [];

    foreach ((new DOMXPath($document))->query($query) as $node) {
        if ($node instanceof DOMElement) {
            $nodes[] = $node;
        }
    }

    return $nodes;
}

function tableOrdinal(DOMElement $table): int
{
    $document = $table->ownerDocument;
    $index = 0;

    foreach ($document->getElementsByTagName('table') as $candidate) {
        $index++;

        if ($candidate->isSameNode($table)) {
            return $index;
        }
    }

    return 0;
}

/**
 * One-based position of an element among its element siblings, which is what a reader
 * counting down a rendered table or across a row of tiles needs. Text nodes are skipped.
 */
function elementOrdinal(DOMElement $node): int
{
    $ordinal = 1;

    for ($sibling = $node->previousElementSibling; $sibling instanceof DOMElement; $sibling = $sibling->previousElementSibling) {
        $ordinal++;
    }

    return $ordinal;
}

/**
 * @return list<DOMElement>
 */
function directCells(DOMElement $row): array
{
    $cells = [];

    foreach ($row->childNodes as $child) {
        if ($child instanceof DOMElement && in_array($child->nodeName, ['td', 'th'], true)) {
            $cells[] = $child;
        }
    }

    return $cells;
}

function plain(DOMNode $node): string
{
    return trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
}
