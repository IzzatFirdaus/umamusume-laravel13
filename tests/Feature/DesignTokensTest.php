<?php

declare(strict_types=1);

use App\Models\MatchCandidate;
use App\Models\Preference;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Schema;
use Pest\Browser\Playwright\Browser;

/*
 * G-18 / G-19 for the application's two source trees.
 *
 * G-19 is a grep gate, and this is that grep run against rendered HTML rather
 * than the source, so a value smuggled in through a Blade expression is caught
 * too. Two things must not appear: a `dark:` utility, because the theme forks by
 * flipping custom properties and nothing else (D-101), and any skeleton palette
 * class, which is what left the dark theme with a white page and zinc borders
 * while the shell still rendered on the skeleton palette (B1 retired that shell).
 *
 * The measured half of G-18 — every text/background pair clearing 4.5:1 in both
 * themes — is a browser check against resolved custom properties, and the corpus
 * already records it as passing (FRONTEND-SPEC-DIVERGENCE.md §5: 19 text pairs
 * and the 9 grade-badge fills, both themes, worst light 4.74, worst dark 5.48).
 * What this file proves is the mechanical precondition for that result: these
 * four pages reference tokens and nothing else, so one set of classes serves both
 * themes.
 */

/**
 * Pages the design audit found on the skeleton palette. Every one of them is an Inertia page now, so
 * what the response still carries from the server is the shell and the resolved props, and both are
 * read here: the shell for its own classes, the props for a class smuggled in through an expression.
 * The markup the components render is swept from source by the gate below, where it can be seen whole.
 *
 * @return array<string, string>
 */
function shellPageUrls(): array
{
    return [
        'catalog index' => '/umamusume',
        'catalog show' => '/umamusume/tokai-teio',
        'review index' => '/review',
        'run create' => '/training-runs/create',
    ];
}

it('renders each legacy shell page from tokens, with no theme fork and no skeleton palette class', function (string $url): void {
    Umamusume::factory()->create(['name' => 'Special Week', 'slug' => 'special-week']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Tokai Teio', 'slug' => 'tokai-teio']);
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);

    $html = test()->get($url)->assertOk()->getContent();

    expect($html)
        ->toContain('bg-page')
        ->not->toMatch('/\bdark:/')
        ->not->toMatch('/\b(?:zinc|gray|neutral|stone|slate|amber|red|blue|emerald)-[0-9]/')
        ->not->toMatch('/\b(?:bg|text|border)-white\b/');
})->with(shellPageUrls());

/**
 * Every source that can put a class on an element a Trainer sees: the Blade that is left, and the
 * Vue components and pages that replaced most of it (ADR-0020 §1).
 *
 * RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a glob-based sweep
 * reads one directory and passes without having looked at the rest. A gate that reads only one of the
 * two trees goes quiet as the port advances, which is how a sweep ends up proving nothing.
 *
 * @return array<string, string>
 */
function styleSources(): array
{
    $sources = [];

    foreach (['resources/views', 'resources/js'] as $root) {
        $walk = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path($root), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($walk as $file) {
            // getExtension() answers "php" for foo.blade.php, so the suffix is matched on the
            // whole filename.
            if (preg_match('/(\.blade\.php|\.vue|\.ts)$/', $file->getFilename()) === 1) {
                $sources[str_replace([DIRECTORY_SEPARATOR, base_path().DIRECTORY_SEPARATOR], ['/', ''], $file->getPathname())] =
                    (string) file_get_contents($file->getPathname());
            }
        }
    }

    return $sources;
}

/**
 * The comments out of a source file, leaving the code.
 *
 * Comments are where a rule is documented by naming the form it rejects, which is
 * precisely the shape this gate is meant to forbid in markup. DESIGN.md and the
 * test prose that guards it both name the withdrawn pairs so nobody reintroduces
 * them; matching those would make the gate fail the prose that keeps the rule
 * alive, so the class scan reads only what can reach an element.
 *
 * ponytail: line comments are found by `//` not preceded by `:`, which covers a URL in a string but
 * would also cut a trailing double slash inside one. Upgrade path is a real tokenizer, worth it only
 * if a class ever hides behind that shape.
 *
 * @param  array<string, string>  $sources
 * @return array<string, string>
 */
function withoutComments(array $sources): array
{
    $patterns = ['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s', '/\/\*.*?\*\//s', '/(?<!:)\/\/[^\n]*/'];

    return array_map(
        static function (string $source) use ($patterns): string {
            foreach ($patterns as $pattern) {
                $source = (string) preg_replace($pattern, '', $source);
            }

            return $source;
        },
        $sources,
    );
}

it('keeps every class the Trainer will see on tokens, in either source tree', function (): void {
    /*
     * G-19 run against both trees. The page that used to anchor this claim called `->links()` and
     * rendered the framework's published pagination view; every page that paginates is a Vue component
     * now, so the pager's classes live in `resources/js` and that published override renders nowhere.
     * Sweeping the sources rather than one rendered page is what keeps the gate biting as the port
     * advances instead of narrowing it to whatever Blade happens to be left.
     */
    $offenders = [];

    foreach (withoutComments(styleSources()) as $path => $source) {
        $hits = [];

        if (preg_match('/\bdark:/', $source) === 1) {
            $hits[] = 'a dark: utility (theme forks by flipping custom properties only, D-101)';
        }

        if (preg_match('/\b(?:zinc|gray|neutral|stone|slate|amber|red|blue|emerald)-[0-9]/', $source) === 1) {
            $hits[] = 'a skeleton palette class';
        }

        if (preg_match('/\b(?:bg|text|border)-white\b/', $source) === 1) {
            $hits[] = 'a hardcoded white';
        }

        if ($hits !== []) {
            $offenders[] = $path.': '.implode(', ', $hits);
        }
    }

    expect($offenders)->toBe([]);
});

it('keeps preferences in one keyed table with no per-user column', function (): void {
    Preference::put('theme', 'light');

    $row = Preference::whereKey('theme')->sole();

    expect($row->value)->toBe('light')
        // A third key is a PRD change; a `user_id` would contradict §6 non-goal 1.
        ->and(Schema::hasTable('preferences'))->toBeTrue()
        ->and(Schema::hasColumn('preferences', 'user_id'))->toBeFalse()
        ->and(Schema::getColumnListing('preferences'))->toBe(['key', 'value', 'created_at', 'updated_at']);

    // This case is about the table. `failure_estimate` used to be its subject, which left the key
    // with a schema test and no behavior test at all while §7-5 said no control could set it;
    // the behavioral half now lives in PreferenceControlsTest.
});

it('renders a stored theme into the document and drops the pre-paint script', function (): void {
    Preference::put('theme', 'dark');

    $html = test()->get('/umamusume')->assertOk()->getContent();

    // D-104 / PRD US-11: the server-rendered attribute means the first paint is
    // already dark, so the head script is not emitted at all. Emitting both would
    // leave the script free to overwrite the Trainer's stored choice.
    expect($html)
        ->toMatch('/<html[^>]*\bdata-theme="dark"/')
        ->not->toContain('prefers-color-scheme')
        ->not->toContain('uma-theme');
});

it('falls back to the system setting when no theme is stored', function (): void {
    $html = test()->get('/umamusume')->assertOk()->getContent();

    expect($html)
        ->not->toContain('data-theme=')
        ->toContain('prefers-color-scheme: dark');
});

it('ignores a stored theme it cannot honour rather than writing a dead attribute', function (): void {
    // US-11 authorizes light, dark, and follow-the-OS, and the third is expressed
    // as the absence of a row. Any other stored value is not a theme, and a
    // `data-theme="system"` would match no stylesheet and silently pin light.
    Preference::put('theme', 'system');

    $html = test()->get('/umamusume')->assertOk()->getContent();

    expect($html)->not->toContain('data-theme=')->toContain('prefers-color-scheme: dark');
});

/**
 * The token inventory, measured from the stylesheet rather than remembered.
 *
 * The browser gate below names a count, and that count went stale twice: the
 * suite said 41, then 43, while the tree had already grown past both. Counting
 * the declarations here means a new token has to move this number, and a stale
 * documented figure fails a test instead of sitting in a comment.
 */
it('draws every selection boundary with the line token, never the fill token', function (): void {
    // KI-9 / R15. `--color-pick` is the gold used to paint a selection, and on a
    // `raised` surface in the light theme it measures 1.59:1 — fine as a fill behind
    // dark ink, useless as a 2px outline someone must see to know which row is live.
    // So boundaries take `--color-pick-line` and the fill keeps `--color-pick`.
    //
    // Asserted across both source trees because the failure mode is one component
    // drifting back to the prettier token, not all of them being wrong at once.
    $offenders = [];

    foreach (withoutComments(styleSources()) as $path => $source) {
        // `border-pick-line` is allowed; `border-pick` followed by anything but a
        // hyphen is the fill token used as a boundary.
        if (preg_match('/border-pick(?![-\w])/', $source) === 1) {
            $offenders[] = $path;
        }
    }

    expect($offenders)->toBe([]);
});

/**
 * The paragraph above is the tripwire: this count moved three times, and every move was
 * someone writing the number by hand while the tree had already grown past it.
 */
it('counts every colour token the static theme declares', function (): void {
    // 53 through Slice 5, plus the five mood fills and `--color-on-mood` from
    // Slice 6. The count is a tripwire for an undeclared token, not a budget: a
    // new colour role is added here and in DESIGN.md §3.1 together, and this row
    // moves with it. `--color-on-green` was retired 2026-10-09 with the last
    // consumer of its `text-on-green` utility, the low-Energy advisory badge on
    // the guided rail that owner ruling R-2 retired; a declaration cites a use,
    // and no plan or design document names one for it. `DESIGN.md` §2.1 still says
    // 60 because that row predates R-2; the stylesheet is the authority and it
    // declares 59, which is also what `TokenPairHygieneTest` records.
    //
    // 2026-10-14: 59 -> 66. The seven `--color-type-*` support-type glyph fills
    // (`SupportTypeMark.vue`) are the client's own chip colours, added so the seven
    // types read apart instead of washing to one ink (UMAMUSUME_REFERENCE.md §1.4.7,
    // DESIGN-CORPUS.md §6.0b). They fill a glyph, not a text/background pair, so no
    // G-18 contract row is owed; they are declared here because every colour role is.
    //
    // 2026-10-15: 66 -> 79. Track 2-D owns nine brand and status rows declared as
    // amendments to the marketing palette: chrome, on-chrome, accent, accent-ink,
    // accent-soft, status-positive, status-positive-ink, status-caution,
    // status-caution-ink. Phase 11.18 adds four more so the glyph artwork in
    // `SupportTypeMark.vue` carries no hex of its own: mark-ink, mark-pal,
    // mark-group-soft, mark-group-line. They fill a glyph, not a text/background
    // pair, so no G-18 contract row is owed for the four.
    //
    // 2026-10-15: 79 -> 81. The primary action fill split from the chrome role it aliased:
    // `--color-primary-action` and `--color-on-primary-action` are declared as aliases of the
    // brand-chrome pair, so both resolve in both themes with no dark re-declaration of their own.
    $tokens = themeStaticColorTokens();

    expect($tokens)->toHaveCount(89)
        ->and($tokens)->toContain('--color-goal')
        ->and($tokens)->toContain('--color-goal-line')
        ->and($tokens)->toContain('--color-on-risk')
        ->and($tokens)->toContain('--color-stat-speed')
        ->and($tokens)->toContain('--color-stat-group');
});

/*
 * Rule 36 (primitive-reconciliation.md section 5): every `var(--...)` in app.css names a
 * custom property app.css declares. An undefined custom property inherits rather than
 * errors, so this is a source-level guard for the same failure the rendered gate catches
 * for declared names. Added 2026-10-22 (Phase 2.4).
 */
it('resolves every var() reference in app.css to a declared custom property', function (): void {
    $css = (string) file_get_contents(base_path('resources/css/app.css'));
    $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);

    preg_match_all('/--([a-z0-9-]+)\s*:/', $css, $declared);
    preg_match_all('/var\(\s*(--[a-z0-9-]+)/', $css, $used);

    $declaredNames = array_unique($declared[1]);
    $usedNames = array_unique(array_map(static fn (string $name): string => substr($name, 2), $used[1]));

    $unresolved = array_values(array_diff($usedNames, $declaredNames));

    expect($unresolved)->toBe([], 'app.css references a custom property it never declares: '.implode(', ', $unresolved));
});

/**
 * The non-colour scale tokens (plan 1.1).
 *
 * The count gate above reads `--color-*` only, so the ratified radius scale and the overlay
 * shadow were declared but unprotected: deleting `--radius-md` left the suite green while every
 * card lost its corner. Values are quoted from the owner amendment `DESIGN.md` §2.3 signed
 * 2026-10-14 and from Phase 11.14's shadow, so a drift fails a test instead of a screen.
 *
 * No `--text-*` or `--spacing-*` scale is asserted here because none exists and declaring one is
 * not this slice's to make: `DESIGN.md` §2.2 signs Body `text-sm` (14px) and Critical
 * `text-3xl` (30px), and §2.3 signs "Tailwind default 0.25rem increments" for spacing. A type or
 * spacing token at the plan's 16/20/28 would re-litigate a signed amendment, which
 * `docs/research-scratch/PROCESS-PLANS.md` section `## implementation-backlog-2026-10-17.md`
 * §C leaves to the owner.
 */
it('declares the ratified radius scale and the overlay shadow in the static theme', function (): void {
    $block = themeStaticBlock();

    expect($block)
        ->toContain('--radius-sm: 6px')
        ->toContain('--radius-md: 10px')
        ->toContain('--radius-lg: 14px')
        ->toContain('--radius-xl: 16px')
        ->toContain('--radius-pill: 999px')
        ->toContain('--shadow-overlay');
});

/**
 * Every `--color-*` token the static theme block declares, read from the stylesheet
 * rather than remembered by hand.
 *
 * The browser gate below is the other reader of this list, and the two must agree:
 * a token the stylesheet declares but the browser cannot resolve is D-288's
 * failure, and one it resolves but nothing declares is a phantom that only exists
 * because some utility emitted a hex.
 *
 * @return array<int, string>
 */
function themeStaticColorTokens(): array
{
    preg_match_all('/(--color-[a-z0-9-]+)\s*:/', themeStaticBlock(), $matches);

    return array_values(array_unique($matches[1]));
}

/**
 * The `@theme static` block with its comments stripped, read from the stylesheet.
 *
 * Shared by the colour count above and the scale-presence case below. Comments are cut
 * because a rule is often documented in this block by naming the value it rejects, and
 * prose is not a declaration.
 */
function themeStaticBlock(): string
{
    $css = (string) file_get_contents(base_path('resources/css/app.css'));
    $theme = substr($css, strpos($css, '@theme static'));
    $open = strpos($theme, '{');
    $depth = 0;
    $end = strlen($theme);

    for ($i = $open; $i < strlen($theme); $i++) {
        if ($theme[$i] === '{') {
            $depth++;
        } elseif ($theme[$i] === '}') {
            $depth--;

            if ($depth === 0) {
                $end = $i;
                break;
            }
        }
    }

    return (string) preg_replace('#/\*.*?\*/#s', '', substr($theme, $open + 1, $end - $open - 1));
}

/**
 * G-18's contract: the text/background combinations this system paints, and the
 * threshold each one owes.
 *
 * `text` marks a pair that carries copy, which owes WCAG 2.1 AA at 4.5:1 (D-10). The
 * pairs that do not carry copy owe 1.4.11's 3:1 instead: a selection boundary, a
 * progress fill and a state outline are information, but they are not text, and
 * holding them to 4.5:1 would fail every hairline in the system. A third threshold —
 * `hairline` at 1.2:1 — covers decorative overlays and brand-tint fills that are
 * neither text nor boundary: they owe no WCAG threshold but must not be invisible.
 *
 * Membership is the recovered R9 browser pass: the pairs `DESIGN-CORPUS.md` §3.4
 * and §3.7 list as approved, the calendar, capsule and stat-band rows measured in
 * the slice-2 and slice-3 verification records, and the energy-band, advisory,
 * delta and failure-chip rows measured in slice-5. Ratios were transcribed there
 * from `getComputedStyle` on the rendered element per D-288, not from a source
 * hex. The retired `on-green` pair is absent because owner ruling R-2 retired
 * `--color-on-green` with its only consumer on 2026-10-09, so what this list
 * counted was 19 rather than 20; 2026-10-13 adds `ink-muted on raised` and
 * `ink-muted on panel` (cockpit/wizard UI audit 2026-10-12 §2.9), making it 21.
 *
 * 2026-10-22: FQ-01..FQ-05 extend the list. FQ-01 (re-spell) adds no new pair —
 * `rule on page` is already here at 3:1. FQ-02 adds `pick-line on raised` at 3:1
 * (boundary). FQ-04 adds `on-risk on risk` at 4.5:1 (text; `--color-on-risk` is
 * minted in step 2.2). FQ-05 adds seven `stat-* on raised` pairs at 3:1 (boundary;
 * the seven `--color-stat-*` tokens are minted in step 2.2). Two hairline rows are
 * added: `brand-accent on brand-chrome` at 1.2:1 and `scrim on page` at 1.2:1.
 *
 * @return array<string, array{foreground: string, background: string, type: string}>
 */
function g18ContractPairs(): array
{
    return [
        // Body ink on the surfaces it is set on.
        'ink on panel' => ['foreground' => 'ink', 'background' => 'panel', 'type' => 'text'],
        'ink on raised' => ['foreground' => 'ink', 'background' => 'raised', 'type' => 'text'],
        'ink-muted on sunken' => ['foreground' => 'ink-muted', 'background' => 'sunken', 'type' => 'text'],
        // 2026-10-13: the muted tier's heaviest consumers print on raised and panel
        // (raised-cell labels, panel sub-copy), so both pairings join the contract
        // (cockpit/wizard UI audit 2026-10-12 §2.9) instead of staying hand-computed.
        'ink-muted on raised' => ['foreground' => 'ink-muted', 'background' => 'raised', 'type' => 'text'],
        'ink-muted on panel' => ['foreground' => 'ink-muted', 'background' => 'panel', 'type' => 'text'],

        // Per-stat band tints: wayfinding, never identity (D-113, D-114).
        'ink on tint-speed' => ['foreground' => 'ink', 'background' => 'tint-speed', 'type' => 'text'],
        'ink on tint-stamina' => ['foreground' => 'ink', 'background' => 'tint-stamina', 'type' => 'text'],
        'ink on tint-power' => ['foreground' => 'ink', 'background' => 'tint-power', 'type' => 'text'],
        'ink on tint-guts' => ['foreground' => 'ink', 'background' => 'tint-guts', 'type' => 'text'],
        'ink on tint-wit' => ['foreground' => 'ink', 'background' => 'tint-wit', 'type' => 'text'],
        'ink on tint-sp' => ['foreground' => 'ink', 'background' => 'tint-sp', 'type' => 'text'],

        // Fills with their own ink, the pair each one exists for.
        'ink on green-tint' => ['foreground' => 'ink', 'background' => 'green-tint', 'type' => 'text'],
        'ink-strong on raised' => ['foreground' => 'ink-strong', 'background' => 'raised', 'type' => 'text'],
        'on-chrome on chrome' => ['foreground' => 'on-chrome', 'background' => 'chrome', 'type' => 'text'],
        'on-pick on pick' => ['foreground' => 'on-pick', 'background' => 'pick', 'type' => 'text'],
        'on-pick on green' => ['foreground' => 'on-pick', 'background' => 'green', 'type' => 'text'],

        // FQ-04: white ink on the themed risk fill. `--color-on-risk` is minted in
        // step 2.2; in light it is #FFFFFF (10.89:1 on dark --risk) and in dark it
        // is #121013 (on light --risk #FF7E8C). Both clear 4.5:1 once the token exists.
        'on-risk on risk' => ['foreground' => 'on-risk', 'background' => 'risk', 'type' => 'text'],

        // Deltas and the goal cell: never colour-only (D-12, §6.16b).
        'up on panel' => ['foreground' => 'up', 'background' => 'panel', 'type' => 'text'],
        'down on raised' => ['foreground' => 'down', 'background' => 'raised', 'type' => 'text'],
        'goal on raised' => ['foreground' => 'goal', 'background' => 'raised', 'type' => 'boundary'],
        'goal-line on raised' => ['foreground' => 'goal-line', 'background' => 'raised', 'type' => 'boundary'],

        // The progress fill on its own track, the one repair the pass caught.
        'green-deep on sunken' => ['foreground' => 'green-deep', 'background' => 'sunken', 'type' => 'boundary'],

        // Card boundary: border-rule on page (WCAG 1.4.11 non-text, 3:1).
        'rule on page' => ['foreground' => 'rule', 'background' => 'page', 'type' => 'boundary'],

        // FQ-02: the selection boundary on a raised surface. `--color-pick-line` is
        // the gold hairline that marks the live pick; it is a non-text boundary at 3:1.
        'pick-line on raised' => ['foreground' => 'pick-line', 'background' => 'raised', 'type' => 'boundary'],

        // FQ-05: seven stat-colour boundaries on a raised surface. The seven
        // `--color-stat-*` tokens are minted in step 2.2 at the §3 values; each is
        // a non-text boundary at 3:1 (computed 4.24–6.23:1 in §3).
        'stat-speed on raised' => ['foreground' => 'stat-speed', 'background' => 'raised', 'type' => 'boundary'],
        'stat-stamina on raised' => ['foreground' => 'stat-stamina', 'background' => 'raised', 'type' => 'boundary'],
        'stat-power on raised' => ['foreground' => 'stat-power', 'background' => 'raised', 'type' => 'boundary'],
        'stat-guts on raised' => ['foreground' => 'stat-guts', 'background' => 'raised', 'type' => 'boundary'],
        'stat-wit on raised' => ['foreground' => 'stat-wit', 'background' => 'raised', 'type' => 'boundary'],
        'stat-pal on raised' => ['foreground' => 'stat-pal', 'background' => 'raised', 'type' => 'boundary'],
        'stat-group on raised' => ['foreground' => 'stat-group', 'background' => 'raised', 'type' => 'boundary'],

        // Track 2-D brand and status additions (Phase 11.16). Each new role owes a
        // measured G-18 pair; values are the ruling's starting draft and these rows
        // are the gate. Failures here mean a token needs stepping, never that a row
        // is removed (addendum prohibition 1).
        'on-brand-chrome on brand-chrome' => ['foreground' => 'on-brand-chrome', 'background' => 'brand-chrome', 'type' => 'text'],
        'brand-accent-ink on page' => ['foreground' => 'brand-accent-ink', 'background' => 'page', 'type' => 'text'],
        'brand-accent-ink on raised' => ['foreground' => 'brand-accent-ink', 'background' => 'raised', 'type' => 'text'],
        'brand-accent-ink on sunken' => ['foreground' => 'brand-accent-ink', 'background' => 'sunken', 'type' => 'text'],
        'brand-accent-ink on brand-accent-soft' => ['foreground' => 'brand-accent-ink', 'background' => 'brand-accent-soft', 'type' => 'text'],
        'status-positive-ink on status-positive' => ['foreground' => 'status-positive-ink', 'background' => 'status-positive', 'type' => 'text'],
        'status-caution-ink on status-caution' => ['foreground' => 'status-caution-ink', 'background' => 'status-caution', 'type' => 'text'],
        'ink-muted on brand-accent-soft' => ['foreground' => 'ink-muted', 'background' => 'brand-accent-soft', 'type' => 'text'],
        'ink-strong on brand-accent-soft' => ['foreground' => 'ink-strong', 'background' => 'brand-accent-soft', 'type' => 'text'],

        // Hairline row (1.2:1): a decorative brand-tint fill on a chrome surface
        // that is neither text nor boundary. It owes no WCAG threshold but must not
        // vanish against the chrome it sits on.
        'brand-accent on brand-chrome' => ['foreground' => 'brand-accent', 'background' => 'brand-chrome', 'type' => 'hairline'],

        // D-1's second hairline row (1.2:1). `--color-scrim` is the modal overlay
        // fill declared in app.css (light :272, dark :438); it is neither text nor a
        // boundary, so it owes no WCAG threshold, but an overlay that reads at the
        // page's own luminance would be invisible as an overlay. The pair was
        // described in the docstring above since 2026-10-22 but never added to this
        // array, so the gate asserted 41 rows over a ruling that bound 42.
        'scrim on page' => ['foreground' => 'scrim', 'background' => 'page', 'type' => 'hairline'],
    ];
}

/**
 * The nine grade-badge fills, each measured against `ink-strong`.
 *
 * Nine letters, not seventeen: `config/scenarios.php` bands every stat at 50-point
 * half-steps and emits the nine letters these tokens are named for. White on the
 * light fills measures 1.20-1.44 and is banned (G-47), so the ink is `ink-strong`
 * in both themes.
 *
 * @return array<int, string>
 */
function g18GradeBadgeTokens(): array
{
    return [
        'grade-g', 'grade-f', 'grade-e', 'grade-d', 'grade-c',
        'grade-b', 'grade-a', 'grade-s', 'grade-ss',
    ];
}

/**
 * The script that resolves a token list in a given theme.
 *
 * `getPropertyValue` on `documentElement` returns the *specified* value, which can
 * be a hex, a `color-mix()` or a keyword, so it is normalised by routing it through
 * a probe element's `color` and reading the computed value back: that is the one
 * form the browser cannot leave half-resolved. An empty string is returned as an
 * empty string rather than by falling back to the probe's inherited colour, which
 * is the silent false pass D-288 was written about.
 */
function resolvedTokenScript(array $names, string $theme): string
{
    return sprintf(
        '(() => {'
        .'document.documentElement.dataset.theme = %s;'
        .'const root = getComputedStyle(document.documentElement);'
        .'const probe = document.createElement("span");'
        .'probe.style.cssText = "position:absolute;left:-9999px;top:0";'
        .'document.body.appendChild(probe);'
        .'const read = (name) => {'
        .'  const raw = root.getPropertyValue(name).trim();'
        .'  if (raw === "") { return { raw: "", resolved: "" }; }'
        .'  probe.style.color = ""; probe.style.color = raw;'
        .'  return { raw: raw, resolved: getComputedStyle(probe).color };'
        .'};'
        .'const out = {};'
        .'for (const name of %s) { out[name] = read(name); }'
        .'probe.remove();'
        .'return out;'
        .'})()',
        json_encode($theme),
        json_encode(array_values($names)),
    );
}

/**
 * An `rgb()` / `rgba()` computed value as its three channels, or null if it is not
 * a colour at all.
 *
 * @return array<int, int>|null
 */
function parseComputedColor(string $value): ?array
{
    if (preg_match('/^rgba?\(\s*([\d.]+)[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $value, $matches) === 1) {
        return [
            (int) round((float) $matches[1]),
            (int) round((float) $matches[2]),
            (int) round((float) $matches[3]),
        ];
    }

    return null;
}

/**
 * WCAG 2.1 relative luminance, linearising each channel exactly as
 * `DESIGN-CORPUS.md` §3.4 states the formula.
 *
 * @param  array<int, int>  $rgb
 */
function relativeLuminance(array $rgb): float
{
    $channels = array_map(
        static function (int $channel): float {
            $component = $channel / 255;

            return $component <= 0.03928 ? $component / 12.92 : (($component + 0.055) / 1.055) ** 2.4;
        },
        $rgb,
    );

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * The WCAG 2.1 contrast ratio between two colours.
 *
 * @param  array<int, int>  $foreground
 * @param  array<int, int>  $background
 */
function contrastRatio(array $foreground, array $background): float
{
    $lighter = max(relativeLuminance($foreground), relativeLuminance($background));
    $darker = min(relativeLuminance($foreground), relativeLuminance($background));

    return ($lighter + 0.05) / ($darker + 0.05);
}

/**
 * Browser-based contrast and token-resolution gate (D-288, G-18).
 *
 * D-288 requires reading the resolved custom property rather than sampling through
 * an element that already has a colour, and treating empty as a failure: Tailwind
 * prunes an `@theme` token nothing references, an undefined `var()` then inherits
 * whatever the element had, and a naive probe reports a pass for a colour that was
 * never declared. The gate therefore reads every token off `documentElement` in
 * both themes and fails on the first empty one.
 *
 * G-18 is D-10 applied per theme (D-103): every pair in `g18ContractPairs()` clears
 * 4.5:1 for text or 3:1 for a non-text boundary in light *and* dark, with no
 * component that only works in one of them.
 */
describe('browser contrast and token resolution (D-288, G-18)', function (): void {
    beforeEach(function (): void {
        if (! class_exists(Browser::class)) {
            $this->markTestSkipped('Playwright browser driver not installed; skipping D-288/G-18 browser gate.');
        }

        /*
         * `tests/Pest.php` strips `@vite` from every Feature test so the suite
         * needs no build manifest. This gate reads resolved custom properties, so
         * it needs the real sheet: without it the rendered HTML carries no
         * stylesheet at all, every `getPropertyValue` returns an empty string, and
         * D-288's "fail on empty" fires on a missing asset rather than on a
         * missing token. Restoring the facade puts the built manifest back, and the
         * browser server serves `public/build` as a static file.
         */
        $this->withVite();
    });

    it('resolves every declared token in both themes and fails on empty', function (): void {
        $page = visit('/umamusume');
        $names = themeStaticColorTokens();

        expect($names)->toHaveCount(89);

        foreach (['light', 'dark'] as $theme) {
            /** @var array<string, array{raw: string, resolved: string}> $resolved */
            $resolved = (array) $page->script(resolvedTokenScript($names, $theme));

            $empty = [];
            $unparsable = [];

            foreach ($names as $name) {
                $value = $resolved[$name]['raw'] ?? '';

                if ($value === '') {
                    $empty[] = $name;

                    continue;
                }

                if (parseComputedColor($resolved[$name]['resolved'] ?? '') === null) {
                    $unparsable[] = $name.' ('.$value.')';
                }
            }

            expect($empty)->toBe([], sprintf(
                '%s theme leaves %d declared token(s) unresolved. A token Tailwind pruned resolves to nothing,'
                .' and an undefined var() inherits silently, so a contrast probe would report someone else\'s colour.'
                .' @theme static is what prevents this (D-288).',
                $theme,
                count($empty),
            ));

            expect($unparsable)->toBe([], sprintf(
                '%s theme resolved %d token(s) to something that is not a colour.',
                $theme,
                count($unparsable),
            ));
        }
    });

    it('asserts the G-18 pair list matches the design contract', function (): void {
        $page = visit('/umamusume');

        $pairs = g18ContractPairs();
        $badges = g18GradeBadgeTokens();

        expect($pairs)->toHaveCount(42)
            ->and($badges)->toHaveCount(9)
            ->and(array_keys($pairs))->toBe([
                'ink on panel', 'ink on raised', 'ink-muted on sunken', 'ink-muted on raised', 'ink-muted on panel',
                'ink on tint-speed', 'ink on tint-stamina', 'ink on tint-power', 'ink on tint-guts', 'ink on tint-wit',
                'ink on tint-sp', 'ink on green-tint', 'ink-strong on raised', 'on-chrome on chrome',
                'on-pick on pick', 'on-pick on green', 'on-risk on risk', 'up on panel', 'down on raised',
                'goal on raised', 'goal-line on raised', 'green-deep on sunken', 'rule on page',
                'pick-line on raised',
                'stat-speed on raised', 'stat-stamina on raised', 'stat-power on raised',
                'stat-guts on raised', 'stat-wit on raised', 'stat-pal on raised', 'stat-group on raised',
                'on-brand-chrome on brand-chrome', 'brand-accent-ink on page',
                'brand-accent-ink on raised', 'brand-accent-ink on sunken',
                'brand-accent-ink on brand-accent-soft',
                'status-positive-ink on status-positive', 'status-caution-ink on status-caution',
                'ink-muted on brand-accent-soft', 'ink-strong on brand-accent-soft',
                'brand-accent on brand-chrome', 'scrim on page',
            ]);

        $roles = collect($pairs)
            ->flatMap(static fn (array $pair): array => [$pair['foreground'], $pair['background']])
            ->merge($badges)
            ->merge(['ink-strong'])
            ->unique()
            ->values()
            ->all();

        $names = array_map(static fn (string $role): string => '--color-'.$role, $roles);

        foreach (['light', 'dark'] as $theme) {
            /** @var array<string, array{raw: string, resolved: string}> $resolved */
            $resolved = (array) $page->script(resolvedTokenScript($names, $theme));

            $colors = [];

            foreach ($roles as $role) {
                $colors[$role] = parseComputedColor($resolved['--color-'.$role]['resolved'] ?? '');
            }

            expect(array_filter($colors, static fn (?array $rgb): bool => $rgb === null))->toBe([], sprintf(
                '%s theme left one or more contract roles unreadable; no ratio below it can be trusted.',
                $theme,
            ));

            foreach ($pairs as $label => $pair) {
                $ratio = contrastRatio($colors[$pair['foreground']], $colors[$pair['background']]);

                // Three-way threshold model (FQ-01..FQ-05, 2026-10-22):
                // text → 4.5:1 (WCAG 2.1 AA), boundary → 3:1 (WCAG 1.4.11 non-text),
                // hairline → 1.2:1 (decorative; owes no WCAG threshold but must not vanish).
                $threshold = match ($pair['type']) {
                    'text' => 4.5,
                    'boundary' => 3.0,
                    'hairline' => 1.2,
                };

                expect($ratio)->toBeGreaterThanOrEqual($threshold, sprintf(
                    '%s theme: the contract pair "%s" (--color-%s on --color-%s) measures %.2f:1, under the %.1f:1 %s'
                    .' threshold. Every ratio the corpus records for it clears this bar.',
                    $theme,
                    $label,
                    $pair['foreground'],
                    $pair['background'],
                    $ratio,
                    $threshold,
                    $pair['type'],
                ));
            }

            foreach ($badges as $badge) {
                $ratio = contrastRatio($colors[$badge], $colors['ink-strong']);

                expect($ratio)->toBeGreaterThanOrEqual(4.5, sprintf(
                    '%s theme: grade badge "%s" measures %.2f:1 against ink-strong, under the 4.5:1 the grade letter'
                    .' needs. White on these fills measures 1.20-1.44 and is banned (G-47).',
                    $theme,
                    $badge,
                    $ratio,
                ));
            }

            // D-2 (owner ruling A, 2026-10-22): #FFFFFF must fail on the nine grade
            // fills in light theme. White on these light fills measures 1.20–1.44:1,
            // far below the 4.5:1 text threshold, which is why the badge ink is
            // `ink-strong` and not white. In dark theme the grade fills darken and
            // some (e.g. grade-g) become dark enough that white passes — the ban is
            // still correct there because the badge ink is `ink-strong` for theme
            // consistency, not because white fails; the assertion only runs in light.
            if ($theme === 'light') {
                $whiteRgb = [255, 255, 255];

                foreach ($badges as $badge) {
                    $whiteRatio = contrastRatio($whiteRgb, $colors[$badge]);

                    expect($whiteRatio)->toBeLessThan(4.5, sprintf(
                        'light theme: white on grade badge "%s" measures %.2f:1, at or above 4.5:1. This would mean the'
                        .' G-47 ban on white ink is wrong and the badge ink should be revisited.',
                        $badge,
                        $whiteRatio,
                    ));
                }
            }
        }

        // Named-token coverage: --color-risk, --color-pick-line, and --color-ring
        // are referenced by the new pairs above and by the focus-ring base rule.
        // Each must resolve to a colour in both themes — an unresolved name would
        // inherit silently (D-288, rule 36), so these are resolved alongside the
        // contract pairs rather than assumed.
        $namedTokens = ['--color-risk', '--color-pick-line', '--color-ring'];

        foreach (['light', 'dark'] as $theme) {
            /** @var array<string, array{raw: string, resolved: string}> $resolved */
            $resolved = (array) $page->script(resolvedTokenScript($namedTokens, $theme));

            foreach ($namedTokens as $name) {
                $value = $resolved[$name]['raw'] ?? '';

                expect($value)->not->toBe('', sprintf(
                    '%s theme: %s resolved to an empty string. It is declared in app.css and must resolve.',
                    $theme,
                    $name,
                ));

                expect(parseComputedColor($resolved[$name]['resolved'] ?? ''))->not->toBeNull(sprintf(
                    '%s theme: %s resolved to "%s", which is not a colour.',
                    $theme,
                    $name,
                    $value,
                ));
            }
        }
    });

    /*
     * The card surface against the page behind it, measured and floored rather than
     * contracted at 3:1 (frontend-fidelity Slice 0.4, 2026-10-14).
     *
     * `app.css`'s dark override widened the `page`/`panel` step and this test is the
     * check that the widening holds and that the sanctioned separator still carries
     * WCAG 1.4.11. Three numbers matter here, and the third is the honest one:
     *
     *  1. `rule on page` clears 3:1 in both themes. That is the card boundary and the
     *     mechanism DESIGN.md §2.3 names first, and it is the row that actually owes the
     *     3:1 in the contract above.
     *  2. The dark `panel`/`page` step is floored at 1.25:1, up from the 1.04:1 the two
     *     surfaces read at before this slice. It is a floor, not the target, and moving
     *     it up requires a text token to move with it.
     *  3. The light step is floored at 1.05:1, which is where it was and stays. The
     *     light theme never had the dark theme's dissolution problem, and this slice did
     *     not touch its ramp.
     *
     * Why the surface step cannot be contracted at 3:1: a surface that carries
     * `--color-ink-muted` is already capped by the G-18 row `ink-muted on panel` at
     * 4.5:1, which bounds dark `panel` at a relative luminance of 0.0522. The contrast
     * formula's +0.05 floor means a pure black page against that bound still only
     * reaches 2.04:1, and light would have to be driven near-black to reach 3:1 against
     * its own panel. Reaching 3:1 on the surface means failing 4.5:1 on the text set on
     * it. So the target is recorded here as the arithmetic fact it is, and the rule
     * edge keeps the obligation it already clears.
     */
    it('measures the card surface step and keeps the rule edge above 3:1', function (): void {
        $page = visit('/umamusume');

        foreach (['light', 'dark'] as $theme) {
            /** @var array<string, array{raw: string, resolved: string}> $resolved */
            $resolved = (array) $page->script(resolvedTokenScript(
                ['--color-panel', '--color-page', '--color-rule'],
                $theme,
            ));

            $panel = parseComputedColor($resolved['--color-panel']['resolved'] ?? '');
            $pageColor = parseComputedColor($resolved['--color-page']['resolved'] ?? '');
            $rule = parseComputedColor($resolved['--color-rule']['resolved'] ?? '');

            expect($panel)->not->toBeNull()
                ->and($pageColor)->not->toBeNull()
                ->and($rule)->not->toBeNull();

            /** @var array<int, int> $panel */
            /** @var array<int, int> $pageColor */
            /** @var array<int, int> $rule */
            $surfaceStep = contrastRatio($panel, $pageColor);
            $boundaryStep = contrastRatio($rule, $pageColor);

            // The boundary, which is the part WCAG 1.4.11 asks about.
            expect($boundaryStep)->toBeGreaterThanOrEqual(3.0, sprintf(
                '%s theme: the card boundary (--color-rule on --color-page) measures %.2f:1, under the 3:1'
                .' WCAG 1.4.11 needs for the visual information identifying the component.',
                $theme,
                $boundaryStep,
            ));

            // The surface step, floored per theme at what each ramp was widened to.
            $floor = $theme === 'dark' ? 1.25 : 1.05;

            expect($surfaceStep)->toBeGreaterThanOrEqual($floor, sprintf(
                '%s theme: the card surface (--color-panel on --color-page) measures %.2f:1, under the %.2f:1'
                .' floor this slice widened the ramp to. 3:1 is not reachable here: a surface carrying'
                .' ink-muted is capped at 4.5:1 by the ink-muted contract row, which bounds dark panel at'
                .' a relative luminance of 0.0522, and even a pure black page against that bound only'
                .' reaches 2.04:1.',
                $theme,
                $surfaceStep,
                $floor,
            ));
        }
    });
});
