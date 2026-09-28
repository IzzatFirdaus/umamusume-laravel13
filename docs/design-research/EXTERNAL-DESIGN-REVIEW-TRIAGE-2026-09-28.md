# External design review — triage, disposition, erratum

**verified-against `ee6786c`** (`docs/audit-remediation`, 2026-09-28). Every line reference and
count in this file was read from that tree or re-run against it; §5 lists the commands. The rule
that put this header here is the owner's own, recorded in §1.

The review arrived as pasted text with no instruction attached. It was triaged rather than
executed: each finding was checked against the tree, then accepted, refused, or re-scoped. Three
of its claims did not survive that check and the owner withdrew them.

---

## 1. Erratum — three withdrawn claims (owner ruling, 2026-09-28)

These claims were made in the review, were found false here, and are **withdrawn**. They are
recorded rather than deleted: a doc that silently loses a wrong claim teaches nobody that the
wrong claim was ever possible.

| # | Claim as written in the review | Ruling | What the tree shows |
|---|---|---|---|
| 1 | "Server-side/pre-paint theme lives only in design-preview; the real layout ships without it" | **Withdrawn** | `resources/views/components/layout.blade.php:10` renders `data-theme="{{ $theme }}"`, fed by the `View::composer('components.layout', …)` registered in `app/Providers/AppServiceProvider.php` |
| 2 | "The script hardcodes a dark fallback instead of reading prefers-color-scheme" | **Withdrawn** | the same file's inline head script reads `window.matchMedia('(prefers-color-scheme: dark)')`; nothing hardcodes a theme |
| 3 | "The mood column lives only on the unmerged `feat/adr-0003-schema` branch" | **Withdrawn** | `turn_entries` on this branch carries `energy`, `mood`, `fans`; the migration is tracked at HEAD (`database/migrations/2026_09_27_093945_add_energy_mood_fans_to_turn_entries.php:17`) |

**Root cause, in the owner's words:** "I reused `FRONTEND-BRIEF-AUDIT` findings without
re-verifying against HEAD. The audit was true when written and went stale as T-work landed."

**Two process rules took effect at that moment, and they bind this document:**

1. Every review or audit carries a `verified-against <SHA>` line at the top, so its evidentiary
   horizon is visible to any later reader.
2. Withdrawn claims are recorded as an erratum note on the document, never silently edited.

---

## 2. A caveat on the withdrawal of claim 1, found while reconciling the branch (T8/T9)

Claim 1 was withdrawn against the working tree, and the working tree does server-render the
theme. Re-verifying it against **HEAD** for this document found the two halves of that path sit
in different places:

```
$ git grep -c data-theme HEAD -- resources/views/components/layout.blade.php   -> 1
$ git grep -n "View::composer" HEAD -- app/Providers/AppServiceProvider.php    -> no match
```

The template that *consumes* `$theme` is committed. The composer that *supplies* it is not on any
ref — it is inside the concurrent session's uncommitted `AppServiceProvider.php` (+25/−1), and
`app/Models/Preference.php` with its migration exist on no ref at all (`git log --all --` on them
returns zero commits; KI-13). `layout.blade.php:15` gates the inline fallback with
`@empty($theme)`, so a clean checkout of `master` or of this branch renders **no** `data-theme`
attribute and falls through to `prefers-color-scheme` alone. US-11's stored-preference path is
unshipped on every ref.

This does not resurrect the review's recommendation and it did not change any file T4–T9 touched,
so the withdrawal stands as issued: the claim was tested against the tree in front of it and the
tree was right. It is recorded because the disposition's own §6 asks to be told about anything
that "revives one of the withdrawn claims", and a claim that is true of the branch while false of
the working tree is exactly that shape. The fix belongs to KI-13, not to the theme default, which
stays an owner call.

---

## 3. Disposition of every finding, as accepted

| Finding | Verdict | Disposition |
|---|---|---|
| Theme shell absent / no system-follow | False | Withdrawn (§1) |
| Mood column unmerged | False | Withdrawn (§1) |
| "No stat band" / "no guided flow" | Misframed | Re-scoped: both components exist, are tested and measure clean; the gap is that `design-preview.blade.php` is their only mount. → S4 |
| Raw turn form as only door; no preview, failure branch, Energy advisory | True | S4 (D-50/51/53/171/200) |
| `app.ts` 21 bytes; no keyboard path (G-11) | True | S4, last (D-55) |
| No two-region frame | True | S4 (D-40) |
| Strip not persistent | True | S4 (D-170) |
| "1 aliases" pluralization | True | Drive-by only if a file already touched by T4–T9 carries it → **not applied**, see §4 |
| Inventive skill names in the live select | True | Landed in T5 (`5c65597`) |

The reframe that matters: four "unbuilt features" were one wiring gap on the run view. That is
why it became one slice instead of four.

## 4. What T4–T9 actually did with it

- **T5 skill names (`5c65597`).** `SkillSeeder` now holds exactly the ten names from the approved
  D-210 pool, verbatim — `Gourmand`, `Unstoppable`, `Up-Tempo`, `Come What May, See Ya Later!`,
  `In Body and Mind`, `Homestretch Haste`, `Professor of Curvature`, `Traightaways`,
  `Playtime's Over`, `564 Escapades`. Nothing was composed, shortened or "corrected":
  `Traightaways` keeps D-210's spelling. `sp_cost` is seeded `null` and `type` left unset, because
  D-210 evidences names and nothing else, and attaching the old invented 120/60 to real skills
  would have been the worse failure (D-227). Both `Illustrative …` rows are deleted at the end of
  the seed — `updateOrCreate` alone would leave them in the live select. `Light Hello` was not
  seeded: it is a Pal support card, not a skill.
- **"1 aliases" — not applied, on the review's own condition.** The string is
  `resources/views/catalog/index.blade.php:43` (`{{ $umamusume->aliases_count }} aliases`). That
  file is not in T4–T9's touched set, and it is currently dirty in the concurrent session's
  working tree, so the instruction's fallback governs: left for the copy pass, no standalone
  commit.
- **Refused, with evidence.** Retirement of `--color-green-tint` (R14). The pair is live spec in
  `DESIGN.md` §6.15 for the unbuilt Safe-band colouring and the converged prototype uses it; the
  token is declared in both themes and survives `@theme static` (55 declared, 0 pruned against the
  built sheet). Recorded as the open half of KI-11 rather than deleted to quiet a count.

---

## 5. Commands behind §2

```
git grep -c data-theme HEAD -- resources/views/components/layout.blade.php
git grep -n "View::composer" HEAD -- app/Providers/AppServiceProvider.php
git log --all --oneline -- app/Models/Preference.php \
    database/migrations/2026_09_27_121500_create_preferences_table.php
git diff --stat -- app/Providers/AppServiceProvider.php
```

Line references for the withdrawn claims: `layout.blade.php:10` and `:15`,
`AppServiceProvider.php` (`View::composer('components.layout', …)`, the matchMedia script in the
same block), and the `mood` column at
`2026_09_27_093945_add_energy_mood_fans_to_turn_entries.php:17`.

## 6. S4 — opened, not started

Sequence as ruled, and it does not begin until T4–T9 is green and reported:

1. Mount `x-stat-band` and `x-guided-step` on the run view; add the `MoodTier` field to the turn
   form; wire preview-before-commit, the success/failure branch, and the Energy advisory beside the
   commit control (D-50/51/53/171/200, D-202/259, G-57).
2. Two-region desktop frame and non-scrolling resource strip (D-40, D-170).
3. Keyboard path for the guided flow (D-55, G-11) — last, because it only bites once the guided
   flow is the live path.

**Owner/Architect calls, unchanged and untouched by this slice:** theme default (light vs dark),
rounded-font vs system stack (C-8 plus `DESIGN.md` §11), mid-run "Change scenario" semantics.
