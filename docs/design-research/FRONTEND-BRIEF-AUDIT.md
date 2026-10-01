# Audit — "Frontend Development Document" brief vs the tree as it actually stands

Audited 2026-09-27. Read-only: this file is the only artifact produced, and it is untracked.

**Subject of the audit:** the 945-line brief `Frontend Development Document — Umamusume Trainer
Companion`, Phase 1 scope, supplied as an attachment (temp path
`…/attachments/677cf4da-8e76-43dc-bebb-d9fa6cab91ce/90fce50e-dc5a-4216-a9cf-b51b785ef2a8.txt`).

**Method note.** Every line marked VERIFIED below was checked directly against a file at a cited
line. Lines marked REPORTED come from a delegated cross-read of §6/§7 component literals that I
spot-checked but did not re-measure one by one; treat those as indicative, not certified.

---

## 0. The situation this audit starts from

The brief is not a greenfield instruction. A second concurrent session is implementing the same
surface **right now, uncommitted**:

| Path | State |
|---|---|
| `resources/css/app.css` | uncommitted, 11 → **243 lines**, `@theme static` at :21 |
| `resources/views/components/{guided-step,race-calendar,resource-strip,stat-band}.blade.php` | uncommitted |
| `DESIGN.md`, `PRODUCT.md`, `docs/design-research/{CONSTRAINTS,DESIGN,SCENARIO-DIFFERENCES,SCREENSHOT-MANIFEST}.md` | uncommitted |
| `KNOWN-ISSUES.md` (180 lines), `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` (229), `MECHANICS-TRANSLATION-TRIAGE.md` (234) | untracked, authored by that session |

Two consequences. First, any implementation of this brief edits files another agent is holding
dirty, which is a clobber, not a merge. Second, **a divergence audit of a frontend spec already
exists** (`FRONTEND-SPEC-DIVERGENCE.md` §1.1–1.7) and reaches the same conclusions I do on theme
direction and the font gate — so this file should be read beside it, not instead of it. That audit
argues about *Nunito*; this brief mandates *M PLUS Rounded 1s* and never names Nunito, so the two
documents are not pointing at the same artifact.

---

## 1. Blocking findings

### B1 — The brief's font mandate contradicts the document that outranks it. VERIFIED
The brief states its own precedence in line 5: where it and `DESIGN.md` disagree, `DESIGN.md` wins.
Root `DESIGN.md` then rules, at §2.2:

- `DESIGN.md:106` — "no **external fonts** (offline constraint + C-8 dependency gate)"
- `DESIGN.md:108` — "UI text: `--font-sans: ui-sans-serif, system-ui, sans-serif`"

The brief's §4.2 mandates self-hosting M PLUS Rounded 1s at weights 400–900, and §20.3 forbids
"system-ui as primary". Both directly oppose the higher authority. The tree already implements the
*ruling*, not the brief: `resources/css/app.css:29` is `--font-sans: ui-sans-serif, system-ui,
sans-serif`. The brief's own §19.1 concedes the font is an unapproved open question, and §17 says a
dependency not listed in §2 must stop and escalate — so the brief contradicts itself here as well.
`KNOWN-ISSUES.md` KI-6 warns not to "fix" this by fetching a font.

**No `resources/fonts/` or `public/fonts/` directory exists. This is not a build task; it is an
owner C-8 decision.**

### B2 — The brief's `DESIGN.md` citations resolve to a different file than precedence implies. VERIFIED
The brief cites `DESIGN.md` §3.5 (tokens), §4.1/§4.2 (type), §6.x, §8.x, §11 (open questions).
Root `DESIGN.md` is 317 lines and its only headings are 1, 2, 2.1, 2.2, 2.3, 3, 4, 4.1–4.6. There
is **no §3.5, no §11, and §4.1 means "Catalog index", not a type scale.** Those sections exist in
`docs/design-research/DESIGN.md` (1540 lines). So "DESIGN.md wins" silently imports the research
package as the authority while root `DESIGN.md` — the file at the cited path — contradicts it on
fonts, radius, shadows and theme direction. Every citation in the brief should be re-qualified to
one file or the other before a single component is built.

### B3 — Energy and Fans are authorised on paper and absent from the schema. VERIFIED
`grep -rniE "energy|fans|turn_events|scenario_slots" database/migrations/` returns **nothing**, and
neither appears in `app/Models/`. Meanwhile:

- `docs/adr/0003-…:3` — "Status: **accepted by owner 2026-09-27.** Absorbs the pending items in
  ADR-0001 and ADR-0002", defining Energy/Fans/`turn_events` column names.
- `docs/adr/0001-…:3` — "accepted in part… §5 (schema) is superseded by ADR-0003".

So the brief's §6.7 energy gauge, §7.1 fan readout and §7.2 low-energy advisory are **buildable in
principle but blocked on a migration that does not exist yet**. The brief's §17 says "a screen needs
a field the schema does not have → do not add a column" — here the column is already ruled, so the
correct move is to write the migration, which is a separate, sequenced change, not a silent one.

Note the reverse error too: ADR-0001:16 lifts §6.11 **for Energy guidance only**, while brief §1.2
restates the §6.11 non-goal flatly with no carve-out, then §7.2 asks for the energy advisory. Right
outcome, wrong stated rule.

### B4 — The layout entry point is currently broken, independent of this brief. VERIFIED
`resources/views/components/layout.blade.php:7` requests `resources/js/app.js`; the only files in
`resources/js/` are `app.ts` (0 bytes) and `bootstrap.ts`. That is a `ViteManifestNotFoundException`
on every real page — logged by the other session as `KNOWN-ISSUES.md` KI-1. The brief's §16 asks for
`resources/js/app.js`, so it would *coincidentally* fix KI-1, but nothing in the brief acknowledges
it. Any JS work here also collides head-on with the `app.ts`/TypeScript stack that `tsconfig.json`
and `vite.config.js` already commit to.

### B5 — Alpine.js is named but not installed. VERIFIED
`package.json` devDependencies are `@tailwindcss/vite`, `axios`, `concurrently`,
`laravel-vite-plugin`, `tailwindcss`, `vite`. Brief §2 lists "Vanilla JS / Alpine.js" and §16 puts
"Alpine components" in `app.js`. §2 also permits vanilla JS, so every listed interaction is
achievable without a new dependency — **recommend dropping Alpine from §2 rather than seeking C-8
approval for it.**

### B6 — The brief omits the two screens the repo calls highest priority. VERIFIED
Root `DESIGN.md:179` heads §4.1 "Catalog index `/umamusume` **(first surface, owner priority)**",
and §4.5 specifies the review queue at `/review`. The brief specifies four screens — dashboard,
guided input, run list, skill search — and **has no file for catalog, catalog detail, review queue
or the CSV/JSON export affordance**, none of which appear in §7 or §16. `routes/web.php` carries 17
routes today. Adopting the brief as written therefore deletes owner-priority surface unless
"three screens plus a skill search" in §1.1 is meant as *additional*, not *exhaustive*. That reading
needs the owner, not an implementer.

---

## 2. Citation integrity (spot-checked)

| Brief claim | Verdict | Evidence |
|---|---|---|
| §1.2 "§6.12 — no race-day snapshots" | **MIS-CITED** | `PRD.md:92` = §6.11 "No race simulation, prediction engine, **or race-day snapshots**"; `PRD.md:93` §6.12 is "no dual storage modes". §6.12 is double-cited by rows 5 and 7. |
| §1.2 rows for §6.1, §6.9, §6.6, §6.13 | CONSISTENT | `PRD.md:81,89,86,94` |
| §2 "Governing: ARCHITECTURE.md §2" | **MIS-CITED** | §2 is System Design; stack is §1, layout is §9 (REPORTED, not re-read) |
| §13 "Governing: DESIGN.md §1.7" | **BROKEN POINTER** | no §1.7 exists in either DESIGN.md; in `docs/UMAMUSUME_REFERENCE.md` the terminology map is **Section 6**, §1.7 survives only as a stale cross-reference |
| §19.7 product name "closed: Trainer Desk" | CONSISTENT ruling, **contradicted by the brief's own title** | root `DESIGN.md:1` "Trainer Desk design system"; brief H1 says "Umamusume Trainer Companion" |
| §19.5 mood strings "resolved from client capture" | CONSISTENT | reference §1.1.6 confirms all five tiers from the client panel; `a7cabc0` is that commit. **But** the tier *colours* are provisional, and §6.14 then renders tiers with raw Tailwind `amber-*` instead of the `--color-mood-*` tokens §3.1 mandates. |
| §19.6 dark mode listed as open | **MIS-CITED as open** | research `D-104` already decides it; brief §3.2 implements exactly that (REPORTED) |
| §19.9 tint surface "decided" | **MIS-CITED as decided** | research §11.9 keeps it open (REPORTED) |
| §6.11 "fixed" discipline glyphs, Power = dumbbell | **CONFLICTS** | research §6.0b measures a *flexed arm* and marks it unresolved; Guts-as-flame triple-loads under D-251 (REPORTED) |
| §13 mandates `Pal` as a client label | **UNSUPPORTED** | reference sources "Pal" to game8.co, not a captured client string; `D-278` forbids shipping it as client copy and `D-20` bars promoting a `❌ UNVERIFIED` item into UI copy |
| §11/§15/§17/§18 vs root CONSTRAINTS C-1..C-8, Floor | CONSISTENT | no threshold in the brief requires relaxing the root bar — except the font, which *uses* C-8's approval route unilaterally |

---

## 3. Section status — as built today

Compact; component-literal rows are REPORTED.

| Brief § | Status |
|---|---|
| 3.1 `@theme` tokens | **PARTIAL + CONFLICTS** — most hexes present and exact; `ink-muted`, `--color-up`, `--color-down` differ; 5 mood tokens, radius/shadow/font tokens **missing**; ~15 unlisted tokens added, which brief:64 forbids; block is `@theme static`, not `@theme` |
| 3.2 dark theme | **PARTIAL** — dark override present at `app.css:152`; the head script exists *only* in `design-preview.blade.php`, not in the real shell, and ignores `prefers-color-scheme`. Root `DESIGN.md` reverses dark-first → **light-first**, uncommitted |
| 3.3 colour roles (gains orange, losses blue) | **MET** — honoured in `guided-step:90`, `stat-band:116` |
| 4.1 type scale | **NOT BUILT** — no `numeral-xl/lg`, `title`, `micro` roles; literals use `text-2xl`/`text-xs` |
| 4.2 font loading | **BLOCKED (B1)** |
| 5.1 two-region frame | **NOT BUILT** — `layout.blade.php:18` is one `max-w-5xl` column |
| 5.2/5.3/5.4 spacing, radius, elevation | **CONFLICTS deliberately** — `app.css:18-19` states "No shadows and no custom radius tokens… `rounded-md` throughout", matching root `DESIGN.md` §2.3 against the brief |
| 6 components (15 specified) | **1 PARTIAL, 4 inline, 10 NOT BUILT** — only 5 components exist and just `stat-band` name-matches; `race-calendar` is not in §16 at all |
| 7.1 dashboard | **NOT BUILT** — though scenario-composed strip and the two-marker cap rule are met |
| 7.2 guided input | **MET for the rail on the run screen (2026-09-28, `d50a0ec`/`6a53c15`)** — "Step N of M" ✓, bars not dots ✓, and keyboard 1–5/Enter/Escape now present: `resources/js/guided-flow.ts` binds the digits and Escape, arrow-key roving is the native radio group's, and Enter advances to the preview stage. Verified with pressed keys, not `element.focus()`. The audit's stated cause, "`app.ts` is 0 bytes", is fixed: `app.ts` is two imports. |
| 7.3 run list / 7.4 skill search | **NOT BUILT** — no skills route exists at all |
| 8 motion | **NOT BUILT** — zero transitions/keyframes/`prefers-reduced-motion` |
| 9 accessibility | **PARTIAL, narrowed 2026-09-28** — `tabular-nums` ✓; the focus-visible ring is now on the rail's choice banners (`2px solid --color-ring`, verified with a real Tab, `6a53c15`). The `role=radiogroup` ✓ this row counted was itself the defect: its children were `role="radio"` buttons claiming semantics they did not implement, now real radio inputs (KI-14). Still absent: `aria-live`. |
| 10 four states | **PARTIAL** — empty is a bare `<p>`; loading and error states absent |
| 11 iconography | **MET for the 5 discipline glyphs** (filled paths, no stroke, `stat-band:32-36`); sparkle for `is_unique` absent |
| 13 terminology | **PARTIAL** — `turn_entries.condition` still renders as "Condition", which §13 bans for Mood; and `mood` vs `condition` is ADR-0001's own open schema item |
| 15 testing | **NOT BUILT** — all 14 test files are backend/fetch/parse; §15.1 visual regression has no harness in `package.json` |

---

## 4. Interaction with the label-layer commit

`feat/enum-labels` @ `b5864ed` (3 behind `master`) adds `app/Enums/HasLabel.php`, `lang/en/uma.php`
and converts 10 Blade display sites. It is **orthogonal to and compatible with** the brief: it adds
no schema, and its `lang/en/uma.php` is the file §13's terminology belongs in — §13's bans
(Carats/Spark/Scouts/Mood/Trainee) already clear the `lore-code` patterns. Two frictions:

1. It edits six views that a §5.1/§7 redesign would rewrite → rebase `b5864ed` **before** the
   redesign, not after, or its label calls get lost.
2. `lore-code` does not currently grep `lang/**`; that commit widens it. Anyone reordering merges
   should keep that widening, since it already caught banned vocabulary inside the brief's own
   recommended phrasing.

---

## 5. Not verified by this audit

- Per-pixel component literals in §6 (grade badge 22px vs the built 20px at `stat-band:98`, the 50
  energy-risk tick, 26px capsule strip) are REPORTED from a delegated read; I confirmed the 20px
  finding's file and line but did not re-measure the rest.
- Whether `FRONTEND-SPEC-DIVERGENCE.md` audits *this* brief or an earlier draft of it. Its §1.6
  argues about Nunito, which this brief never mentions — likely a different revision.
- `docs/design-research/DESIGN.md` and `CONSTRAINTS.md` are dirty; every citation into them
  describes the working tree, not any commit.

---

## 6. Decisions needed before a line of this is built

1. **Font (B1).** Confirm root `DESIGN.md` §2.2 stands (system stack, no font dependency) and order
   §4.2/§20.3 deleted from the brief — or grant C-8 approval and say so in writing.
2. **Which DESIGN.md is authoritative (B2).** Re-point every citation in the brief, or state that
   `docs/design-research/DESIGN.md` outranks root `DESIGN.md` on tokens and type.
3. **Schema sequence (B3).** Land the ADR-0003 migration (Energy, Fans, `turn_events`,
   `scenario_slots`) as its own change before building §6.7/§7.1/§7.2 on top of it.
4. **Screen scope (B6).** Confirm whether catalog, catalog detail, review queue and export stay in
   Phase 1. If yes, the brief needs four more screen specs; if no, PRD US-1/US-5/US-6 are being cut
   and that is a scope change for the Architect.
5. **JS stack (B4/B5).** Drop Alpine and settle `app.ts` vs `app.js`, fixing KI-1 either way.
6. **Merge order.** Rebase `b5864ed` onto `master` before any view rewrite begins, and get the other
   session's dirty files committed so implementation starts from a tree nobody is holding.
