# Flows — Help

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/HelpController.php`, `resources/js/pages/Help.vue`, `resources/js/layouts/AppLayout.vue:550`.

---

## 1. Route and reachability

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/help` | `help` | `HelpController::show` |

`HelpController::show()` renders `Help.vue` with no props — the screen is static copy.

Reachable from the topbar's `?` on every screen (`href="/help"`), and **deliberately absent from the rail**, which already carries ten destinations. Help is a utility, not a destination.

---

## 2. Content shape

Four questions in the wireframe's order:

1. How a turn goes.
2. What the keys do.
3. What the tags mean.
4. Where a number came from.

Two rules the page holds itself to:

- **Nothing links to a route that does not exist.** The decision, timeline and Cockpit screens are all scoped to one career, so the turn-loop section names them as text and links only the screens a Trainer can open from here.
- **Nothing claims a control the build does not have.** The keyboard table lists what is wired; Undo is described as the gap it is.

---

## 3. Recorded overrides

The page carries several `HFW-OVERRIDE` notes in its script block, each recording where the wireframe and the build disagree and which one shipped:

| Override | What shipped |
| --- | --- |
| `keyboard: keys` | Only Tab/Shift+Tab, Enter/Space, Esc (the mobile More disclosure) and the skip link are wired; printing `1-5` would document controls that do nothing |
| `rules: career length` | The 72-turn grid the app enforces (`CareerCalendar::TURNS_PER_CAREER`); the mock draws five |
| `rules: deck` | Six support-card slots, and two copies of one card cannot be equipped together (`UMAMUSUME_REFERENCE.md:533`). The old sentence about a guest rental is deleted — a rental is a parent, not a deck slot |
| `rules: sparks` | The four `[Global]` spark colours (reference §1.5.2); the mock names only Blue and Pink |
| `rows: diagnostics` | The fourth row is this build's own: "A date looks old" leads to the source ledger, which is the page's answer to a stale figure |
| `copy: import` | CSV import persists every supplied turn, not just a current position |
| `copy: undo note` | The absence is stated once, in the present tense |
| `style: button primitive` | The mock's `.btn` class was never ported; the ledger door uses `LinkButton` |

The wireframe's `.keybox` annotation block and its "Static wireframe" chip are document apparatus and do not ship.

Documentation follow-up on record: no PRD citation exists for this screen yet. It is recorded in release notes rather than treated as a blocker.

---

## 4. Verification surface

| Claim | Pinned by |
| --- | --- |
| Route exists and renders | `RouteCoverageTest` |
| No dead route in Help copy | reviewed at each copy pass; the page's own comment states the rule |
| Copy hygiene (no identifiers, no dashes) | `RenderedCopyHygieneTest`, `UiHygieneTest`, `CopyDensityTest` |
