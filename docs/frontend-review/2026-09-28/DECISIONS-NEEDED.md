# Decisions needed — frontend audit 2026-09-28

Raised against `fix/frontend-audit-2026-09-28` @ `7d4b8cf` base. These are not fixes:
each needs either a schema change (outside this slice's fence) or a product ruling.
Nothing here was acted on.

---

## F-6 — Unity Cup's Team Rank and Spirit Bursts widgets are permanently N/A

**Evidence.** `config/scenarios.php:92` gives `unity_cup` the widget list
`['turn', 'energy', 'fans', 'team_rank', 'spirit_bursts']`. `turn_entries` has no
`team_rank` and no bursts column (`database/migrations/2026_09_26_162818_create_turn_entries_table.php:14-25`,
plus the ADR-0003 additions of energy/mood/fans). Neither `StoreTurnEntryRequest` nor
`guided-step.blade.php` accepts or posts such a field. `TrainingRun::stripValues()`
therefore can only ever return null for both keys, and the strip renders its
documented withheld state — `N/A / not yet recorded` — on every Unity Cup run, forever.

The only surface that ever showed real values was `/design-preview`, which
fabricated them; that surface is now deleted (F-2/F-15), so nothing fakes it any more.

**Why this is not a frontend fix.** Making the widget populatable means a column and
an input path. The slice fence forbids migrations, and the repo's own rule
(`audit` §17 equivalent, PRD FR-C-2) puts bounds and field ownership in one place.

**Path A — add the data.** Two nullable columns on `turn_entries` (team rank tier,
burst count), guided-form fields, validation, export parity (the F-9 lesson says the
screen, the log and the export must agree or the next audit files it again), and a
source for what a rank tier actually is on Global. Cost: one schema slice plus the
fixture. Benefit: two honest widgets. Risk: `docs/UMAMUSUME_REFERENCE.md` must
actually document both as trainer-readable values, or we are storing numbers the game
does not show.

**Path B — remove the widgets.** Drop `team_rank` and `spirit_bursts` from
`config/scenarios.php:92`. Cost: one config line, and the Unity Cup strip gets shorter
than the design sketch. Benefit: the screen stops reserving space for something it can
never report, which is its own kind of dishonesty under D-220.

**Recommendation: B, now; A only if a sourced definition of both values exists.**
Reason: the withheld state is already honest (it says "not yet recorded"), so the
argument for A is capability, not truthfulness — and capability without a citable
source is exactly what PRD §6 non-goals were written to stop.

---

## F-19 — the framework health route loads off-origin assets

**Evidence.** `GET /up` returns 200 and its HTML references `fonts.bunny.net`
(Figtree stylesheet) and `cdn.jsdelivr.net/npm/@tailwindcss/browser@4`, both fetched at
page time. The route is registered by the framework's health check, not by
`routes/web.php`, which contains 16 app routes and no `/up`.

**Why this is not a frontend fix.** Editing it means overriding vendor routing, which
the fence puts out of scope, and the audit itself marks it "needs a decision, not a fix".

**Path A — accept and document.** It is a local-only tool (PRD NFR-1) and `/up` is not
a Trainer surface; nothing links to it. Record the acceptance in `KNOWN-ISSUES.md` next
to KI-3, which closed the identical pattern in `welcome.blade.php`, so the next reader
does not re-file it. Cost: a live off-origin dependency remains on an indexable route.

**Path B — app-owned handler.** Register our own `/up` before the framework's and
return a token-rendered page or a plain JSON body. Cost: we now own a health endpoint,
and `php artisan health:list`-style tooling may expect the vendor page. Benefit: no
off-origin request at all, consistent with F-1's outcome for `/`.

**Path C — disable outside production.** Bind the route to local only. Cost: the health
check disappears from the environment where it is most useful (a trainer's own machine).

**Recommendation: B.** Reason: F-1 removed the same class of violation from `/` by
putting the page inside the product's own shell, and a one-route override is the
smallest change that keeps the promise "no page this tool serves reaches the internet".
If B is refused, A is acceptable only as a written acceptance, not silence.

---

## One more the audit did not file

`/up` was never in the audit's route table for capture, yet the audit's §2 lists it as a
body capture for F-19 only. Fine. But note that `welcome.blade.php` was the sole reason
`resources/views/welcome.blade.php` carried remote links, and after F-1 the repo has no
off-origin reference in any app view — a state worth a line in KI-3's record so the
residual can be closed rather than left "partially visible". Not acted on.
