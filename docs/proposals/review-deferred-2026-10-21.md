# Review queue deferrals (2026-10-21)

Six proposals are deferred. Each names what it is waiting on, so a later dispatch can pick one up
without re-deriving the decision.

## #9 Umamusume id as a picker

The id field accepts a raw integer with no type-ahead. `Combobox.vue` exists (from `1.9a`) and may
fit, but whether it does is a decision rather than a measurement: the field takes a catalog
*primary key*, while the combobox is built for choosing from a list of labelled options, and the
queue's rows are decision cards where a wrong pick merges two trainees. Unblocking: a ruling on
whether `Combobox` fits this field or a new picker component is warranted. The measuring work is
reading `Combobox`'s props against the field's contract.

## #10 Two badge languages unified

The queue shows "Fuzzy match" (a `StatusPill`, a *search* state: how close the engine's guess was)
and "? Unknown" via `StatusBadge` (a *record* state: what the catalog row's provenance is). They
are different axes, and collapsing them needs a decision the tree does not contain: what the record
state should mean when a row is neither Confirmed nor Estimated. Unblocking: a badge taxonomy
ruling.

## #12 Keyboard triage

A real feature (j/k to move, digits to set a verdict, Enter to commit) rather than a fix. It needs
its own dispatch with its own test discipline: the queue is the keyboard-first screen, and the
44px/tap-target and axe cases already pin its current baseline. Unblocking: its own dispatch.

## Confidence score display

The proposal asks for a confidence number on the fuzzy card. The matcher computes a tier, not a
score: this is a backend change (compute and expose a number) plus a new badge variant, not a UI
fix. The honest interim already holds: "Fuzzy match" is a label, not a measurement, and the card
does not pretend otherwise. Unblocking: the matcher computes confidence and the payload carries it.

## Sidebar grouping

Grouping the shell's navigation (Training / Library / System) is a cross-cutting change to
`AppLayout.vue`, the same deferral the dashboard dispatch recorded as its #12. Unblocking: a shell
grouping ruling.

## Left-edge alignment

**Closed as already-satisfied**, by measurement rather than by argument. The page's first card edge
sits exactly on the shell's content edge; the 17px between that edge and the form inside it is the
card's own `p-4` plus its 1px border. The reported x=352 was measured from inside a card, not from
the page's first element. `tests/browser/review.spec.ts` now pins the finding, so a gutter on this
page would red there.

## #3 Bulk action (deferred on the controller, not on a ruling)

No bulk route exists: `routes/web.php` declares `GET /review` and `POST /review/{candidate}` only,
and `ReviewController` has `index` and `resolve`. A bulk resolve is a controller method plus a route
plus its own Form Request, which is its own dispatch. Unblocking: a bulk-resolve controller method
and route land; the UI half is then the select-all header the proposal describes.
