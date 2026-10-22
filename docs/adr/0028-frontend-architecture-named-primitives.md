# ADR-0028: Frontend architecture — named primitives over utility spells

<!-- ADR-CONTRACT
  decision: proposed — adopt the HFW's design-system architecture (tokens, named primitives, components, checked pages) inside the existing Inertia + Vue 3 + Tailwind v4 stack, with the HFW as a source of measurements rather than an implementation
  code: not built — no primitive layer exists in resources/css/app.css; tools/ui-lint.mjs (2 rules, 2-entry allowlist) is the only visual check
  superseded-by: —
  amended-by: —
  open: the parity classification is deferred to D2 for every component a concurrent fix pass is changing; the primitive-to-token map's CONFLICT rows are owner questions FQ-01 to FQ-09 and none is resolved here; docs/proposals/PARITY.md does not exist yet
-->

Status: **Accepted (2026-10-22)** — drafted 2026-10-22, accepted by the owner.

## Context

The repository holds two renderings of one product. The running app spells its visual system with
Tailwind utility classes; the High Fidelity Wireframe set (`docs/design-research/frontend-v2-wireframes`,
"the HFW") spells the same system with named semantic classes in one `assets/shell.css`, and holds
them to four static checks plus a rendered audit. The two have converged on the token layer and
diverged on the component layer.

The divergence has a measurable cost. Measured 2026-10-22 on `15f7ee1b`: the app's visual rules live
in `tools/ui-lint.mjs`, which implements two rules (Card-in-Card, arbitrary values) with a
**2-entry** allowlist; `AGENTS.md` §9 calls that list "debt, not a place to add". The HFW's
`framework-check.mjs` fails on violation instead of naming debt, and `framework-audit.mjs` measures
contrast, target size, reflow and focus in a rendered frame.

Three facts bound the decision and are the reason it is an ADR rather than a refactor:

- `DESIGN.md` is the single source of truth for the visual contract (`ADR-0006` Decision 1), and it
  rules the values: a declared radius scale, `1px --color-rule` borders, no shadows except overlays
  via `filter: drop-shadow()`, and MOTION 2's five scopes at `duration-150 ease-out`.
- The HFW's `shell.css` is measured against the client's frames, and its measurements are the
  **source**, not the implementation. It carries values the house contract does not permit: four
  `box-shadow` declarations, a `.18s ease` layout transition, ten off-scale radii, 115 literal hex
  colours and 514 literal `px` lengths. A mechanical port would import those contradictions.
- The `turn-chip` precedent settles the one case where a measured value may not move: it is **pinned**
  to `--radius-sm` because "a scale amendment must not move a measured motif" (`DESIGN.md` §2.3).

## Owner rulings promoted from the plan (2026-10-22)

The five scope questions this ADR's plan raised were ruled by the owner on 2026-10-22. They are
reproduced here so this ADR is self-contained: each question as asked, each ruling as given, and the
consequence that binds the phases. The plan records the same five under *Owner rulings*, with the
divergence each settles.

| # | question as asked | ruling as given | consequence |
|---|---|---|---|
| 1 | What does "replace the frontend architecture" mean — adopt the design-system architecture inside Vue, or a larger rewrite? | **Adopt the design-system architecture inside Vue.** Keep Inertia + Vue 3 + Tailwind v4; replace the styling and enforcement layer. | The non-goals stand as written. No framework, routing or props-contract change is in scope. |
| 2 | `FormTabs`: restore the link-tab control, or accept the HFW's flat table and the form-switch regression? | **Restore the link-tab control**, and add the HFW's `FormDetail` block beside it. | The HFW's flat costume table is superseded; the set page gains the strip and keeps the table as the detail body. What the strip controls is still open (FQ-11). |
| 3 | `review-detail`: keep it as its own page, or fold it back into the queue? | **Keep it as its own page.** | The plan gains a route: the queue links to a real detail screen and the inline detail in `Review/Index.vue` is removed. This is the one behaviour change, so it carries its own browser spec and `SCREEN_SPEC.md` row; that row requires a `SCREEN_SPEC.md` §4.5 amendment which is **not** made here (FQ-10). See **D6**. |
| 4 | The six undrawn screens: draw them into the HFW set, or record them as out of scope? | **Draw all six** — the three registry screens and the three Snapshot screens. | The set's page count rises from 49; all six enter the five checks on the commit that adds each. |
| 5 | The mock catalogue: promote it to the live pipeline, or retire it once the pages render from the API? | **Keep it as a frozen dated snapshot.** | `assets/data/` stays as it is, is labelled with its capture date, and is regenerated only when the set is revised. "Frozen" binds the dataset, not the pages. |

## Decision

**D1 — The architecture, not the framework.** Adopt the HFW's layering — tokens, named primitives,
components, checked pages — inside Inertia + Vue 3 + Tailwind v4. The HFW performs no writes and no
visit states, so this cannot mean "make the app static". It is bounded by the owner
rulings above.

**D2 — Tokens keep one home.** `resources/css/app.css`'s `@theme static` block remains the single
source of token truth (`DESIGN.md` §9). The `static` qualifier is load-bearing: without it Tailwind
prunes a property no utility references, an undefined `var()` inherits silently, and a contrast check
passes on a token absent from the stylesheet (D-288). No primitive reads a value from elsewhere.

**D3 — Every primitive resolves to a token.** A primitive ported from `shell.css` keeps its measured
geometry and intent, and takes colour, radius, elevation and motion from an existing token where one
exists. A measured value with no token is promoted by owner ruling, not invented; a value that
contradicts a `DESIGN.md` ruling is a **conflict**, an owner question, never a variant. No primitive is
designed that the HFW does not carry.

**D4 — The parity document.** The per-component classification lives at
`docs/proposals/PARITY.md`, beside the plan that consumes it. It is not a root document (`AGENTS.md`
§3 closes that set), not a research master (it is derived and revised whenever either side moves), and
not part of `DESIGN.md` §3, which owns the durable inventory and may cite it. `PARITY.md` is written in
pass D2, not by this ADR.

**D5 — The classification scheme.** Each component carries exactly one of: `near-copy`, `renamed`,
`absent — enhancement`, `absent — replacement`, `absent — out of scope by construction`, `dead`,
**`app-only — not in the set`**, **`the set is behind`**. The last two exist because the first six
cannot express them: a component the HFW never drew is not `absent` (which means the set has a
counterpart), and a screen whose HFW treatment is older than the app's is not `near-copy`. Classification
keys on **rendered signature** — ARIA roles, class families, node counts — never on names, because the
HFW rewrites all copy.

**D6 — One behaviour change, and no second.** Making `review-detail` its own route is the only change
to product behaviour this architecture work sanctions (owner ruling 3 above). It carries one route, one controller action, one
page, one browser spec and one `SCREEN_SPEC.md` row, and the queue's inline detail is removed in the
same commit so no second rendering exists. Everything else is a re-spelling or an addition.

**D7 — Strangler, with a recorded rollback.** Pages migrate one at a time behind the same routes.
Reverting `resources/css/app.css`, the migrated components and the `review-detail` route restores the
prior architecture; the remaining routes and the data never move.

**D8 — The ADR does not accept itself, and the checks are the enforcement.** This ADR is `Proposed`.
The parity matrix is a map, not a gate: no phase is "done" because a row is classified. The gates are
the ported checks and the existing suites.

## Consequences

### 1. What becomes possible

`DESIGN.md` §3 stops being a narrative inventory and gains an authoritative classification, cited from
`PARITY.md`. A new primitive has a written home (`@theme static` for its token, a component layer for
its class) and a check that can fail. A migration commit is reviewable as a class-spell diff plus an
unchanged snapshot.

### 2. What this deliberately does not fix

The app's behaviour verification is already stronger than the HFW's (200 top-level `tests/Feature`
files, 76 browser specs). This ADR closes the *visual-system enforcement* gap only. It does not add a
framework, a dependency, a route, or a data change beyond D6.

### 3. The cost this accepts

The HFW is a static artifact that cannot prove interaction. Adopting its discipline means adopting
checks that are cheaper to satisfy than the product is: `framework-audit.mjs` exits 0 when Playwright
is absent, and `framework-check.mjs` currently *requires* the `.app` transition that MOTION 2 does not
sanction. Porting either verbatim would import a vacuous pass and a motion conflict. Both are
preconditions (see Non-licences 6).

### 4. Sequence

Phase 0 records this ADR and refreshes `DESIGN.md` §3's inventory. No code moves before both. The
primitive layer (Phase 2) opens only when the contrast gate in Non-licence 5 is implemented and
asserting.

## Non-licences

Recorded so a later pass does not re-propose them.

1. **Not a framework change.** Vue 3 and Inertia stay. No new router, no SSR change, no second styling
   engine.
2. **Not a static rebuild.** The HFW's `file://` architecture is not adopted; only its layering is.
3. **No new dependency.** `@playwright/test` is installed; `@vue/compiler-sfc` is present as a
   transitive of `@vitejs/plugin-vue` but is **not declared** in `package.json`, so anything using it
   directly is a new declared dependency and needs approval.
4. **No token deleted without a migration of its uses**, and no token proposed that duplicates an
   existing one under a new name.
5. **The D-288/G-18 contrast gate is a Phase 2 precondition.** The block in
   `tests/Feature/DesignTokensTest.php` must be implemented and must assert the pair list over both
   themes. Whether that block is two `markTestIncomplete` stubs, or a landed rewrite, or a rewrite held
   uncommitted by another session is **not asserted here**; the requirement is that the gate exists and
   measures before Phase 2 opens. If a rewrite has landed by Phase 0, Phase 0 consumes it; if not,
   Phase 0 sequences it before Phase 2 opens.
6. **No check ported verbatim.** A ported check must fail on violation, must not pass when its driver
   is absent, and must not require motion the house contract forbids.
7. **No conflict resolved as a variant.** A radius outside the declared scale, a `box-shadow`, a
   transition outside MOTION 2, and a literal colour where a token exists are conflicts. They are owner
   questions, never normalised into the token set by an agent.
8. **No re-litigation** of `ADR-0006`, `ADR-0021`, `G-SK-20`, `D-101` or `G-19`.

## Ledger

- 2026-10-22 · **correction pass** — the Context's "115 literal hex colours" is the **whole-file**
  figure; the number the framework check will forbid is **21**, the literals inside rules, because the
  token blocks' own hex is legitimate definition (lines 1016 and 1018 are comment prose and are
  excluded). The 514 `px` figure is unchanged. Re-derived from `shell.css` in the same pass; the full
  numbered inventory is `docs/proposals/primitive-reconciliation.md` §5 and the plan's own
  *Literal inventory* section. Also added `## Owner rulings promoted from the plan` (owner ruling set
  of 2026-10-22) so this ADR no longer depends on the plan for its scope.
- 2026-10-22 · **drafted** — `Proposed` by the frontend-architecture reconciliation pass (D1). Rests on
  the verification ledger and the primitive-to-token reconciliation of the same date, both measured on
  `15f7ee1b`. No code, token, primitive or screen changed by this ADR.
