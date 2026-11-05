# Plan: Slices 6, 7, and Final Project Gate

## Goal
Complete the remaining tangible work for Trainer Desk:
- **Slice 6: Deck Summary** — Extract level→break-tier helper, rebuild DeckSummary.vue with collapsed default + tier toggle, add Expand/Collapse all, handle N/A for null level
- **Slice 7: Cross-links + Vocabulary** — Verify usedBy target, add card-name links on wizard slot cards, vocabulary consistency test
- **Final Project Gate** — Full suite green, PHPStan, UI lint, typecheck, build, migrate:status, fence audit

## Scope
### In Scope
- `resources/js/services/career/breakTier.ts` (new helper file)
- `resources/js/components/support/DeckSummary.vue` (rebuild)
- `resources/js/pages/Career/DeckSelect.vue` (wire to helper)
- `resources/js/pages/SupportCards/Show.vue` (verify usedBy target)
- `resources/js/components/support/SupportSlot.vue` (add card-name link)
- New test files for breakTier helper and vocabulary consistency
- Full suite verification commands

### Out of Scope (separate pass)
- Skills-planner work (`SkillController.php`, `SkillsPlannerController.php`, `SkillsPlanner.vue`, `SkillSearchScreenTest.php`, etc.)
- `base_veteran.vue` deletion and `package-lock.json` (pre-existing)

## Acceptance Criteria

### Slice 6 (Deck Summary)
1. ✅ Helper extracted to `breakTier.ts` with `BREAK_TIER_LABELS` constant
2. ✅ `DeckSelect.vue` uses helper, existing tests pass unchanged
3. ✅ `DeckSummary.vue`: `<details>` collapsed by default
4. ✅ Expand all / Collapse all in section header, right-aligned, quiet
5. ✅ Tier toggle in block header beside disclosure, reuses existing toggle component
6. ✅ Tier vocabulary exactly: `Unbroken`, `1 break`, `2 breaks`, `3 breaks`, `Max Limit Break`
7. ✅ `null` level renders `Not recorded` — toggle disabled, no default tier
8. ✅ Honest §8 tags (helper tests measured; component interactions unmeasured unless Vue runner exists)
9. ✅ Raw-evidence bar met (full diffs in report)

### Slice 7 (Cross-links + Vocabulary)
1. ✅ `usedBy` target verified and stated verbatim (route name, URL pattern, user path)
2. ✅ Card-name links on wizard slot cards (`SupportSlot.vue`); Replace still opens picker; Owned toggle works
3. ✅ Vocabulary consistency test exists and passes; both surfaces resolve names from `SupportCardEffects::dictionary()`
4. ✅ No data changes; no migrations; no seeders; no controller edits
5. ✅ Raw-evidence bar met

### Final Gate
1. ✅ Full suite: 238+ tests pass
2. ✅ PHPStan level 6 clean on modified files
3. ✅ `node tools/ui-framework-check.mjs` — count at or below baseline 209
4. ✅ `npm run typecheck` (honest: `tsc --noEmit` doesn't check `.vue`)
5. ✅ `npm run build` success
6. ✅ `php artisan migrate:status` clean
7. ✅ Fence audit: all modified files in declared fences; `tools/ui-lint-baseline.json` identical to `dcc562b6`

## TODO List (Ordered)

### Prerequisites (Orientation)
- [ ] Read `docs/research-scratch/GOVERNANCE.md` §CONSTRAINTS.md (quality bar)
- [ ] Read `.ai/rules/index.md` and relevant rule files for paths in scope
- [ ] Confirm working tree: `git status --short`

### Slice 6: Deck Summary

**Step 1: Extract breakTier helper**
- [ ] Create `resources/js/services/career/breakTier.ts` (decision: services folder exists at `resources/js/services/`)
- [ ] Move `tierWord` logic from `DeckSummary.vue` verbatim
- [ ] Export `BREAK_TIER_LABELS = ['Unbroken', '1 break', '2 breaks', '3 breaks', 'Max Limit Break']`
- [ ] Export `breakTier(level: number | null)` returning `{ tier: number | null; label: string; isMaximum: boolean; level: number }`
- [ ] Handle `null` → `{ tier: null, label: 'Not recorded', isMaximum: false, level: 0 }`
- [ ] Friend-slot exception if existing derivation has one (check `DeckSummary.vue`)

**Step 2: Wire DeckSelect.vue to helper**
- [ ] Import `breakTier` and `BREAK_TIER_LABELS` in `DeckSelect.vue`
- [ ] Replace inline tier logic with helper call
- [ ] Run existing `DeckSelect` tests — must pass unchanged
- [ ] If any test needs change → finding + decision question

**Step 3: Rebuild DeckSummary.vue**
- [ ] Wrap each card block in `<details>` (already there) — default closed (no `open`)
- [ ] Add tier toggle in block header beside disclosure
- [ ] Identify existing toggle component (likely `BaseButton` or pill toggle — check project)
- [ ] Toggle values = `BREAK_TIER_LABELS`; filters ladder to that tier's row
- [ ] Default toggle = tier implied by slot's current level
- [ ] Vocabulary from constant, not inlined
- [ ] Keyboard-operable, 44px targets (reuse component contract)

**Step 4: Expand all / Collapse all**
- [ ] Add to section header (right-aligned, quiet)
- [ ] Use existing secondary button component
- [ ] Single reactive ref `expandSignal: 'expanded' | 'collapsed' | null`
- [ ] Controls toggle `<details>` open attribute
- [ ] Keyboard-operable, 44px targets

**Step 5: N/A handling for null level**
- [ ] Slot with `null` level → `Not recorded` for tier
- [ ] Toggle disabled with visible `Not recorded` reason
- [ ] Ladder table: `Not recorded` in tier column, whole ladder shown or single row `Not recorded` (match existing convention)
- [ ] Helper returns explicit sentinel for `null`

**Step 6: Tests**
- [ ] Helper tests: `resources/js/services/career/__tests__/breakTier.spec.ts` (if Vitest exists) or PHP-side
- [ ] Boundary tests for each tier's lower/upper bounds
- [ ] `null` → `{ tier: null, label: 'Not recorded' }`
- [ ] Friend-slot exception if applicable
- [ ] Existing `DeckSelect` tests pass unchanged
- [ ] DeckSummary tests: if Vue runner exists → measured; else → unmeasured with follow-up

**Step 7: Slice 6 verification report**
- [ ] Full diffs, base stated, labeled cumulative
- [ ] git status --short
- [ ] Runner test names verbatim
- [ ] Runner totals + per-file composition
- [ ] §8 table with honest tags
- [ ] §0.3 sections
- [ ] Fence findings
- [ ] One-line confirmation: no out-of-fence files

### Slice 7: Cross-links + Vocabulary

**Step 8: Verify usedBy target**
- [ ] Open `Show.vue`, locate `usedBy` block
- [ ] Verify link target: route name, URL pattern, user path
- [ ] Confirm link lands on reachable page with card visible
- [ ] State target verbatim in report
- [ ] If wrong → finding + decision question

**Step 9: Card-name links on wizard slot cards**
- [ ] Identify wizard slot-card component: `SupportSlot.vue` (already confirmed)
- [ ] Add link on card name: `<Link :href="route('support-cards.show', card.id)">{{ card.name }}</Link>`
- [ ] Verify: name is link; Replace still opens picker; Owned toggle works
- [ ] Style: existing link primitive, no new accent

**Step 10: Vocabulary consistency test**
- [ ] Locate two surfaces: support-card list/detail and deck summary ladder
- [ ] Create test asserting both resolve effect names from same `SupportCardEffects::dictionary()` keys
- [ ] Implementation: compare payload keys (`effect_id`) from both surfaces
- [ ] Fix divergence in-fence only (rendering fix, not data)

**Step 11: Slice 7 verification report**
- [ ] Full diffs, base stated
- [ ] git status --short
- [ ] Runner test names verbatim
- [ ] Runner totals + per-file composition
- [ ] §8 table with honest tags
- [ ] §0.3 sections
- [ ] Fence findings
- [ ] `usedBy` target stated verbatim
- [ ] One-line confirmation: no data changes/migrations/seeders/controller edits

### Final Project Gate

**Step 12: Full suite green**
- [ ] Run full suite: support + database + career deck + compare + slice-6/7 tests
- [ ] Paste runner totals line verbatim

**Step 13: PHPStan level 6**
- [ ] Run on all modified files
- [ ] Paste `[OK] No errors` or fixed error list

**Step 14: UI lint**
- [ ] Run `node tools/ui-framework-check.mjs`
- [ ] Paste totals line + `arbitrary-value count` line (or absence = at/below baseline 209)

**Step 15: TypeScript**
- [ ] Run `npm run typecheck`
- [ ] State honest limitation: `tsc --noEmit` doesn't check `.vue`

**Step 16: Build**
- [ ] Run `npm run build`
- [ ] Paste success line

**Step 17: Migrate status**
- [ ] Run `php artisan migrate:status`
- [ ] Paste last line (no pending)

**Step 18: Fence audit**
- [ ] `git status --short` — all modified files in declared fences
- [ ] `git diff dcc562b6 --stat` — review every file
- [ ] Skills-planner work labelled as separate pass
- [ ] `git diff dcc562b6 -- tools/ui-lint-baseline.json` → empty diff

**Step 19: Close pass**
- [ ] Record pass-close entry with all metrics
- [ ] Note pass-log findings (slice 4: 8 rounds, 1 suppression, 1 fabrication; slice 5: 6 rounds, evidence discipline)
- [ ] Note project-level gaps for future pass (no `vue-tsc`, no Vue component test runner)

## Verification Commands

### Slice 6
```bash
# Helper tests (if Vitest)
npm run test:js -- resources/js/services/career/__tests__/breakTier.spec.ts
# OR PHP-side
php artisan test --compact tests/Feature/Career/DeckSelectScenarioHintsTest.php tests/Feature/CareerDeckSummaryTest.php

# Existing DeckSelect tests
php artisan test --compact tests/Feature/Career/DeckSelectScenarioHintsTest.php
```

### Slice 7
```bash
# Vocabulary consistency test
php artisan test --compact tests/Feature/SupportCardVocabularyTest.php
# OR if new file
php artisan test --compact tests/Feature/CrossLinkVocabularyTest.php
```

### Final Gate
```bash
# Full suite
php artisan test --compact

# PHPStan
vendor/bin/phpstan analyse --no-progress --memory-limit=1G

# UI lint
node tools/ui-framework-check.mjs

# TypeScript
npm run typecheck

# Build
npm run build

# Migrate status
php artisan migrate:status

# Fence audit
git status --short
git diff dcc562b6 --stat
git diff dcc562b6 -- tools/ui-lint-baseline.json
```

## Risks and Dependencies

1. **No Vue component test runner** — DeckSummary interaction tests will be unmeasured with browser-test follow-ups
2. **No `vue-tsc`** — `.vue` type safety relies on Vite runtime; documented as project limitation
3. **Skills-planner files in working tree** — Must be labelled as separate pass in fence audit
4. **Friend-slot exception** — Need to verify if existing derivation has one (check `DeckSummary.vue` line 96: `level === null ? null : ...` — no friend exception visible; `SupportSlot.vue` has `is_friend` but tier logic doesn't branch on it)
5. **Existing toggle component** — Need to identify which component to reuse (likely `BaseButton` with pill variant or `BaseSelect`)

## Decision Questions
1. **Helper home**: `resources/js/services/career/breakTier.ts` — confirm `resources/js/services/` exists as the home (it does, per `SupportCardEffects.ts` location)
2. **Toggle component**: Which existing component to reuse for tier toggle? (Check `BaseButton`, `BaseSelect`, pill variants)
3. **Ladder rendering for null level**: Single row `Not recorded` vs whole ladder — match existing convention in `DeckSummary.vue` (line 265-269 shows `AbsenceValue` for tier when null)

## Existing Constraints (from AGENTS.md)
- No migrations, no seeders, no controller edits for Slice 7
- Record-only: N/A never rendered as 0, never COALESCEd
- Calculated treatment on every derived number/set with visible derivation clause
- JP/Global split preserved
- Analyzer suppressions fixed or reason-commented + declared
- Every UI claim tagged measured/unmeasured
- Fence is authoritative; out-of-fence edits are findings, never riders
- Raw-evidence bar: full diffs in report, not "pasted above"