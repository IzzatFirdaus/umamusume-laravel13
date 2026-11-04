# Career Setup Audit & Corrections Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Audit and correct the Career Setup phase and Cockpit UI to align with `UMAMUSUME_REFERENCE.md` mechanics, terminology, and data.

**Architecture:** The Trainer Desk is a Laravel 13 + Inertia + Vue 3 application. Corrections span backend (PHP controllers, services, models, config) and frontend (Vue 3 components, TypeScript types). Changes follow the existing pattern: config-driven scenario data, thin controllers, Form Requests for validation, API Resources for JSON.

**Tech Stack:** Laravel 13, PHP 8.3+, Pest 4, PHPStan Level 6, Vue 3 + TypeScript, Vite, Tailwind CSS v4, Inertia.js v2.

**Spec:** `UMAMUSUME_REFERENCE.md` (sections 1.1, 1.3, 1.4, 1.5, 2.1, 2.2) and `AGENTS.md` (quality bar, lore gate, Floor rules).

---

## Global Constraints

- **Lore gate:** `composer lore` and `composer lore-code` must run clean; every hit needs a one-line ruling
- **Floor rules:** No new suppressions, no stub bodies, no deleted tests without approval, no business logic in controllers, no inline `$request->validate()`
- **Terminology:** "Wit" not "Intelligence"; "Front Runner/Pace Chaser/Late Surger/End Closer"; "Spark" not "Factor"; "Pal" not "Friend"; "Scout" not "Gacha"; "Trainee Exchange Points" not "Spark Cost"; "Veteran Umamusume" for finished runs
- **No CI:** All gates run locally; hand-off sequence in `AGENTS.md` §9 must pass
- **Config-driven:** Scenario data only in `config/scenarios.php`; no hardcoded scenario names in components
- **Design tokens only:** Zero `dark:` utilities, zero arbitrary Tailwind values beyond baseline

---

## Review Focus

1. **Growth rate display** — Raw integers `[0,10,0,20,0]` must render as `+10% Stamina, +20% Guts` with % signs
2. **Advisor Rest logic** — When Energy < 50, must check Wit (0 energy), Extreme Spirit Bursts (0% fail), and scheduled races before recommending Rest
3. **Support card level caps** — SSR 30→50, SR 25→45, R 20→40 must be enforced in deck builder
4. **Unique Perk vs Card Level** — Level ladder must show Perk unlock level separately from card level
5. **Terminology sweep** — Every user-visible string must use Global client vocabulary; `lore-code` catches violations

---

## Task 1: Growth Rate Display Correction (§1.3.5)

**Files:**
- Modify: `app/Http/Controllers/Career/CockpitController.php:553`
- Modify: `resources/js/components/Career/IdentityBlock.vue` (or similar component rendering growth_rate)
- Test: `tests/Feature/Career/CockpitGrowthRateTest.php`

**Interfaces:**
- Consumes: `TrainingRun::$growth_rate` (array<string, int|null>)
- Produces: Formatted growth rate with percentages for UI

- [ ] **Step 1: Write failing test** — Assert growth rate displays as "Speed +0%, Stamina +10%, Power +0%, Guts +20%, Wit +0%"
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `CockpitController::identitySection()`** to format growth_rate values as percentages with + sign
- [ ] **Step 4: Update Vue component** to render formatted percentages
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `feat(career): display growth rate as percentages per §1.3.5`

---

## Task 2: Support Card Level Caps Verification & Enforcement (§1.4.2)

**Files:**
- Modify: `app/Enums/CardRarity.php` (verify `levelAtBreak()` returns correct caps)
- Modify: `app/Http/Controllers/Career/DeckSelectController.php:335-351` (level ladder display)
- Test: `tests/Unit/Enums/CardRarityLevelCapsTest.php`

**Interfaces:**
- Consumes: `CardRarity::cases()` with level caps
- Produces: Correct max level per rarity at each limit break

- [ ] **Step 1: Write failing test** — Assert SSR: 30/35/40/45/50, SR: 25/30/35/40/45, R: 20/25/30/35/40
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Verify/fix `CardRarity::levelAtBreak()`** to match reference
- [ ] **Step 4: Update `DeckSelectController::levelLadder()`** to show perk unlock level separately
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `fix(support-cards): enforce correct level caps per rarity per §1.4.2`

---

## Task 3: Remove "Max Stat" Effects from Support Cards (§1.4.8)

**Files:**
- Modify: `database/seeders/data/support_effects.*.json` (verify no 限界値アップ effect exists)
- Modify: `app/Services/DataPipeline/Parsers/GametoraSupportEffectParser.php` (verify parser doesn't create it)
- Test: `tests/Feature/SupportEffectMaxStatAbsentTest.php`

**Interfaces:**
- Consumes: GameTora support_effects.json
- Produces: SupportEffect rows without "Max Stat" effect

- [ ] **Step 1: Write failing test** — Assert no SupportEffect has name containing "Max Stat" or "限界値アップ"
- [ ] **Step 2: Run test to verify it fails** (if any exist)
- [ ] **Step 3: Remove any "Max Stat" effect from seed data and parser**
- [ ] **Step 4: Run `php artisan uma:reparse gametora-support-effects`** to refresh
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `fix(support-effects): remove Max Stat effect per §1.4.8 (zero cards carry it)`

---

## Task 4: Unique Perk Level vs Card Level Separation (§1.4.7)

**Files:**
- Modify: `app/Http/Controllers/Career/DeckSelectController.php:335-351`
- Modify: `resources/js/components/Career/DeckSelect/CardLevelLadder.vue`
- Test: `tests/Feature/Career/DeckSelectPerkLevelTest.php`

**Interfaces:**
- Consumes: SupportCard effects at each level anchor
- Produces: Level ladder showing card level AND perk unlock level as separate rows

- [ ] **Step 1: Write failing test** — Assert level ladder shows "Card Lv 30 (Perk unlocks at Lv 25)" not summed
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `DeckSelectController::levelLadder()`** to include perk unlock level per effect
- [ ] **Step 4: Update Vue component** to render card level and perk level separately
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `fix(deck): separate Unique Perk level from Card Level per §1.4.7`

---

## Task 5: Blue Spark Bonus Calculation Display (§1.5.1, §1.5.3)

**Files:**
- Modify: `app/Models/Legacy/LegacySelectionPayload.php` (add bonus calculation helper)
- Modify: `app/Http/Controllers/Career/InheritanceEventController.php:164-173`
- Modify: `resources/js/components/Career/InheritanceEvent/InheritanceSparks.vue`
- Test: `tests/Feature/Career/InheritanceBlueSparkBonusTest.php`

**Interfaces:**
- Consumes: Blue sparks with star ratings (1★/2★/3★)
- Produces: Starting stat bonus (+5, +12, +21 per blue spark per star)

- [ ] **Step 1: Write failing test** — Assert 1★ Blue Spark = +5, 2★ = +12, 3★ = +21 per stat
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Add `blueSparkBonus()` method to `LegacySelectionPayload`**
- [ ] **Step 4: Update `InheritanceEventController::legacySection()`** to include bonus per spark
- [ ] **Step 5: Update Vue component** to show bonus calculation
- [ ] **Step 6: Run test to verify it passes**
- [ ] **Step 7: Commit** — `feat(inheritance): display Blue Spark stat bonus per star rating per §1.5.3`

---

## Task 6: Dynamic Deck Builder — Scenario-Aware Hints (§2.1, §2.2)

**Files:**
- Modify: `app/Http/Controllers/Career/DeckSelectController.php` (add scenario-aware hints to picker)
- Modify: `resources/js/components/Career/DeckSelect/DeckPicker.vue`
- Test: `tests/Feature/Career/DeckSelectScenarioHintsTest.php`

**Interfaces:**
- Consumes: Selected scenario key, support card types
- Produces: UI hints — "Wit-heavy deck recommended for Unity Cup (Wit cap 1800)", "Grade Points replace Race Goals in Trackblazer"

- [ ] **Step 1: Write failing test** — Assert Unity Cup shows Wit highlight, Trackblazer shows Grade Points prompt
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Add scenario-aware hints to `DeckSelectController::picker()`** return payload
- [ ] **Step 4: Update Vue picker component** to render scenario-specific guidance
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `feat(deck): dynamic scenario-aware deck builder hints per §2.1`

---

## Task 7: Hint Level Input in Career Setup (§1.1.4)

**Files:**
- Modify: `app/Http/Controllers/Career/BuildTargetController.php` (add hint_level to skill_priorities)
- Modify: `app/Http/Requests/Career/StoreDraftBuildTargetRequest.php` (validate hint_level)
- Modify: `resources/js/components/Career/BuildTarget/SkillPriorityPicker.vue`
- Test: `tests/Feature/Career/BuildTargetHintLevelTest.php`

**Interfaces:**
- Consumes: Skill name + hint level (1-4, max)
- Produces: Hint-adjusted SP cost displayed in priority list

- [ ] **Step 1: Write failing test** — Assert skill priority accepts hint_level and shows discounted cost
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `BuildTargetPayload`** to include hint_level per priority skill
- [ ] **Step 4: Update `StoreDraftBuildTargetRequest`** validation
- [ ] **Step 5: Update Vue picker** to show hint level selector per skill
- [ ] **Step 6: Run test to verify it passes**
- [ ] **Step 7: Commit** — `feat(build-target): hint level input for SP discount calculator per §1.1.4`

---

## Task 8: Advisor — Rest Recommendation Alternatives (§1.1.5, §2.4, §2.5)

**Files:**
- Modify: `app/Services/Advisor/TrainerAdvisor.php:225-227, 123-135`
- Modify: `app/Services/Advisor/NextDecision.php` (if needed)
- Test: `tests/Unit/Advisor/TrainerAdvisorRestAlternativesTest.php`

**Interfaces:**
- Consumes: Current energy, available training types, spirit burst state, race schedule
- Produces: Rest recommendation ONLY when no Wit training, no Extreme Burst, no 0-energy race

- [ ] **Step 1: Write failing test** — Energy=30, Wit available → recommends Wit not Rest; Extreme Burst available → recommends training not Rest
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `TrainerAdvisor::rank()`** to check alternatives before recommending Rest:
  - Wit training (0 energy cost, recovers energy)
  - Extreme Spirit Burst available on target stat (0% failure)
  - Scheduled race with 0 energy cost (Kyoto Daishoten, etc.)
- [ ] **Step 4: Run test to verify it passes**
- [ ] **Step 5: Commit** — `fix(advisor): evaluate alternatives before Rest per §1.1.5, §2.4`

---

## Task 9: Spirit Burst Countdown to Senior November Payout (§2.4, §2.5)

**Files:**
- Modify: `app/Services/Career/TeamPanelBuilder.php` (add countdown logic)
- Modify: `app/Http/Controllers/Career/CockpitController.php` (pass countdown to UI)
- Modify: `resources/js/components/Career/Cockpit/TeamPanel.vue`
- Test: `tests/Feature/Career/SpiritBurstCountdownTest.php`

**Interfaces:**
- Consumes: Current combined bursts, extreme bursts, current turn, scenario (Unity Cup)
- Produces: "You are at 11 combined bursts. Need 13 for Gold Lv3. X turns left to trigger 2 more before Senior November."

- [ ] **Step 1: Write failing test** — Assert countdown message appears with correct turns remaining
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Add `burstCountdown()` to `TeamPanelBuilder`** using `burst_payout_timing` from config
- [ ] **Step 4: Update `CockpitController`** to pass countdown in team section
- [ ] **Step 5: Update Vue component** to render countdown warning
- [ ] **Step 6: Run test to verify it passes**
- [ ] **Step 7: Commit** — `feat(cockpit): Spirit Burst Senior November countdown per §2.4, §2.5`

---

## Task 10: Mood Effect Full Tier Display (§1.1.6)

**Files:**
- Modify: `config/uma.php:588-591` (add all five tiers)
- Modify: `app/Http/Controllers/Career/CockpitController.php:289-292`
- Modify: `resources/js/components/Career/Cockpit/MoodEffectPanel.vue`
- Test: `tests/Feature/Career/MoodEffectTiersTest.php`

**Interfaces:**
- Consumes: Current mood state (GREAT/GOOD/NORMAL/BAD/AWFUL)
- Produces: Exact training/race multipliers per tier

- [ ] **Step 1: Write failing test** — Assert all five tiers show correct percentages
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `config/uma.php`** with full mood_effect table:
  - GREAT: training +20%, race +4%
  - GOOD: training +10%, race +2%
  - NORMAL: training 0%, race 0%
  - BAD: training -10%, race -2%
  - AWFUL: training -20%, race -4%
- [ ] **Step 4: Update `CockpitController`** to pass full table
- [ ] **Step 5: Update Vue component** to render current tier with exact math
- [ ] **Step 6: Run test to verify it passes**
- [ ] **Step 7: Commit** — `feat(mood): full mood effect tier display per §1.1.6`

---

## Task 11: Skill Purchasing — Budget Highlighting & Gold Skill Warning (§1.1.4, §2.3)

**Files:**
- Modify: `app/Http/Controllers/Career/SkillsPlannerController.php:101-144` (purchasableSection)
- Modify: `resources/js/components/Career/SkillsPlanner/PurchasableSkills.vue`
- Test: `tests/Feature/Career/SkillsPlannerBudgetHighlightTest.php`

**Interfaces:**
- Consumes: Current SP, skill costs (base + hint-adjusted), skill rarity (Gold/White)
- Produces: Greyed-out unaffordable skills, highlighted affordable, Gold skill savings warning

- [ ] **Step 1: Write failing test** — Assert skills > SP are greyed, skills ≤ SP highlighted, Gold skill warning shows
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Update `purchasableSection()`** to include `affordable` flag and `is_gold` per skill
- [ ] **Step 4: Update Vue component** to grey out unaffordable, highlight affordable, show Gold warning
- [ ] **Step 5: Run test to verify it passes**
- [ ] **Step 6: Commit** — `feat(skills): budget-aware purchasing with Gold skill warning per §1.1.4, §2.3`

---

## Task 12: Terminology Sweep — Global Client Vocabulary

**Files:**
- Modify: `lang/en/uma.php` (verify/replace terms)
- Modify: Vue components across `resources/js/components/Career/` and `resources/js/pages/Career/`
- Test: `composer lore-code` (must run clean with rulings)

**Interfaces:**
- Consumes: All user-visible strings
- Produces: Consistent Global terminology

- [ ] **Step 1: Run `composer lore-code`** — Capture current hit count
- [ ] **Step 2: Search/replace across codebase:**
  - "Intelligence" → "Wit"
  - "Friend" (support card type) → "Pal"
  - "Gacha" → "Scout"
  - "Spark Cost" → "Trainee Exchange Points"
  - "Factor" → "Spark" (inheritance context)
  - "Runner/Leader/Betweener/Chaser" → "Front Runner/Pace Chaser/Late Surger/End Closer"
- [ ] **Step 3: Update `lang/en/uma.php`** terms map
- [ ] **Step 4: Run `composer lore` and `composer lore-code`** — Verify zero new hits, rule each
- [ ] **Step 5: Commit** — `chore(terms): terminology sweep to Global client vocabulary per §6`

---

## Execution Order & Dependencies

```
Task 1 (Growth Rate) ──┐
Task 2 (Level Caps)    │  Independent, can run in parallel
Task 3 (Max Stat)      │
Task 4 (Perk Level)    ├─→ Task 6 (Deck Builder hints)
Task 5 (Blue Spark)    │
Task 7 (Hint Level)    ├─→ Task 11 (Skill Purchasing)
Task 8 (Advisor Rest)  │
Task 9 (Burst Countdown)│
Task 10 (Mood Tiers)   │
Task 12 (Terminology)  └─→ Run last (touches all files)
```

---

## Verification Checklist (per AGENTS.md §15)

- [ ] Plan-first contract followed: goal, acceptance criteria, TODO list, verification evidence exist
- [ ] Implementation matches architecture and conventions (§7, §8)
- [ ] Behavior changes ship with tests; copy-only changes without
- [ ] Narrow tests pass; broader suite + hand-off sequence run for cross-cutting changes
- [ ] `vendor/bin/pint --test --format agent <paths>` on changed files (never `--dirty`)
- [ ] PHPStan Level 6 clean
- [ ] `npm run typecheck` clean (if TS/Vue/Blade changed)
- [ ] Lore gate ran (`composer lore`, `composer lore-code`), every hit has ruling
- [ ] No unrelated file changes; diff read before reporting
- [ ] Documentation moved with behavior (SCREEN_SPEC.md, DESIGN.md, KNOWN-ISSUES.md)
- [ ] Security considered (untrusted input, secrets, SSRF allowlist)
- [ ] Known limitations and unrun gates stated plainly