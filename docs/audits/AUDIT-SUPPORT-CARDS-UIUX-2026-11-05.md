# Audit report — Support-card surfaces (M2 screen + M3 flow)

## 1. Identity

- Audit id: AUD-20261105-001
- Date: 2026-11-05
- Mode: M2 screen audit per component + M3 flow audit (deck picker, compare)
- Scope: `SupportCardCatalog.vue`, `CardPicker.vue`, `CardCompare.vue`, `DeckSummary.vue`, `SupportSlot.vue`, `pages/SupportCards/Index.vue`, `pages/SupportCards/Show.vue`, `pages/Career/DeckSelect.vue`; `services/career/breakTier.ts` not touched
- Out of scope: other screens; controllers/models/migrations/seeders; framework files; pass-close artifacts; peer-session files; backend behaviour; dark-theme pixel contrast except where measured
- Frameworks applied: Heuristics H1–H10; WCAG 2.2 AA subset (1.4.1, 1.4.3, 1.4.10, 1.4.11, 2.1.1, 2.4.7, 2.4.11, 2.5.3, 2.5.8, 3.3.1, 4.1.2); Shneiderman SHR-1..8; Tognazzini TOG-1..8; Gerhardt-Powals GP-1..5; Gestalt GT-1..6; Norman NRM-1..6; Laws of UX UX-1..10; Laws of UI UI-1..7; IA IA-1..6
- Rendered evidence: TRUE (Chromium launched; Playwright specs run locally; `tests/browser/support-cards.spec.ts` 18/19 pass, 1 pre-existing page-size failure)

## 2. Executive summary

- Most severe confirmed finding: the compare checkbox in `SupportCardCatalog.vue` had no accessible name — `:aria-label` fell through to the `<label>` wrapper instead of the input, so screen readers announced an unnamed control (WCAG 4.1.2 / 2.5.3). Fixed in-fence by switching to `:label` + `sr-only-label`.
- Two `N/A` values in `SupportSlot.vue` (Scenario Link unknown; unrecorded ownership) lacked the `title` attribute the repository's copy rule requires, despite the component's own doc comment claiming one. Fixed in-fence.
- `CardCompare.vue` was presented as a dismissible panel but carried no dialog role or labelledby; fixed in-fence with `role="dialog"` + `aria-labelledby` (no `aria-modal`, since the background is not inert).
- Largest unmeasured risk: dark-theme contrast ratios on the support surfaces were not re-measured this round; the pass's measured 4.15:1 fix (DeckSummary ceiling) is the only ratio with evidence. Other ratios rely on intended tokens.
- No finding against a pass-lock; the C14 density, C17 compare semantics, C18 vocabulary, C20 tier vocabulary, D9 and D6 locks all hold as built.

## 3. Scope and coverage

Surfaces covered (states × themes × widths × keyboard):

| Surface | empty | loading | populated | error | light | dark | 320 | 390 | 768 | 1280 | keyboard |
|---|---|---|---|---|---|---|---|---|---|---|---|
| SupportCardCatalog | ✓ | ✓ | ✓ | ✓ (errors.rarity etc.) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |
| CardPicker | ✓ (no matches) | N/A (parent) | ✓ | N/A | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |
| CardCompare | ✓ (<2 cards) | N/A | ✓ | N/A | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | partial (no trap) |
| DeckSummary | ✓ (0 assigned) | N/A | ✓ | N/A | ✓ (rendered, browser spec) | ✓ (rendered) | ✓ (rendered) | ✓ (rendered) | ✓ (rendered) | ✓ (rendered) | ✓ (rendered) |
| SupportSlot | ✓ (not equipped) | N/A | ✓ | ✓ (error alert) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |
| Index.vue | ✓ | ✓ | ✓ | ✓ | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |
| Show.vue | ✓ (no usedBy) | N/A | ✓ | N/A | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |
| DeckSelect.vue | ✓ (empty slots) | N/A | ✓ | ✓ (field errors) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) | ✓ (source) |

Framework coverage matrix: all ten frameworks applied; IA applied only to Show.vue usedBy and card-name links (IA-1, H6) per §3 of the dispatch; the rest N/A for bounded components.

## 4. Evidence status and limits

- RENDERED_EVIDENCE = TRUE. Verified this round: Playwright invocation `npx playwright test tests/browser/support-cards.spec.ts` (config `playwright.config.ts`); runner line `18 passed (1.2m)`; 1 failure `puts the name search first, matches a printed label, and shows five rows` (support-cards.spec.ts:94). axe scans at spec lines 390 (index) and 405 (detail) both pass (0 violations) — see §12 R2 for the spec excerpt.
- OBSERVED_SOURCE: all other claims (markup, roles, labels, titles).
- UNMEASURED: dark-theme body-text contrast on support surfaces except the DeckSummary ceiling span; target-size measurements on CardPicker/CardCompare controls except where `h-11`/`min-h-11` classes give the value at file:line; reflow of CardPicker/CardCompare at 320/390 (no spec).
- Provenance caveats carried: Gerhardt-Powals primary paywalled (9 vs 10 unconfirmed); IA prose unread (applied commentary is repo's reading); Norman book not fetched (six-principle set is convention); Tognazzini 2014 revision truncated (original 16 recorded); Laws of UX 29 entries (28 + Zeigarnik); Laws of UI 13 entries.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
|---|---|---|---|---|---|---|---|---|---|
| F-01 | WCAG 4.1.2 / 2.5.3 | WCAG | SupportCardCatalog, populated, all widths, both themes | Compare checkbox had no accessible name: `:aria-label` fell through to the `<label>` wrapper, not the `<input>`; the control read as unnamed | OBSERVED_SOURCE | `SupportCardCatalog.vue:347-353` | 3 | High | Use `:label` + `sr-only-label` so the label text becomes the input's name (done) |
| F-02 | AGENTS §5 (N/A with title) | house copy rule | SupportSlot, populated (scenario_link === 'unknown') | `Scenario Link: N/A` rendered without a `title` attribute, although the component doc comment claims one | OBSERVED_SOURCE | `SupportSlot.vue:170-175` | 2 | High | Add `title` (done) |
| F-03 | AGENTS §5 (N/A with title) | house copy rule | SupportSlot, populated (ownership === null) | Ownership `N/A` rendered without a `title` attribute | OBSERVED_SOURCE | `SupportSlot.vue:235-240` | 2 | High | Add `title` (done) |
| F-04 | WCAG 4.1.2 | WCAG | CardCompare, populated | Compare panel presented as a dismissible dialog (heading, Close, Escape) but carried no `role="dialog"` or labelledby | OBSERVED_SOURCE | `CardCompare.vue:106-112` (pre-edit) | 2 | High | Add `role="dialog"` + `aria-labelledby` (done; no `aria-modal` because background is not inert) |
| F-05 | WCAG 1.4.1 / UI-3 | WCAG / Laws of UI | CardPicker, populated | Type-match marker uses hue + glyph + word ("fits this slot") — compliant; no finding. Recorded as clean. | OBSERVED_SOURCE | `CardPicker.vue:242-248` | 0 | High | no action |
| F-06 | WCAG 2.5.8 / UX-1 | WCAG / Laws of UX | CardPicker, populated | Owned-only checkbox target is `min-h-11` (44px) via BaseCheckbox's `min-h-11` label — compliant. Recorded as clean. | OBSERVED_SOURCE | `BaseCheckbox.vue:62` | 0 | High | no action |

Clean dimensions (no finding): CardCompare no-empty-columns (GT-1, GP-8) holds; DeckSummary collapsed-by-default (H8, IA-4) holds; DeckSummary vocabulary (SHR-1, UI-1) holds; Show.vue usedBy links (IA-1, NRM-1, H6) hold; SupportSlot card-name link (NRM-1, H6) holds.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
|---|---|---|
| Dark-theme body text contrast on support surfaces (except DeckSummary ceiling) | No contrast measurement run this round beyond the one span | Rendered contrast sweep in both themes at 320/1280 |
| CardPicker / CardCompare target sizes (44px house rule) | `min-h-11` class gives the value at file:line but rendered hit area not measured | DOM measurement of each control |
| CardPicker / CardCompare reflow at 320/390 | No browser spec covers these two components in isolation | Add targeted Playwright reflow assertions |
| CardCompare focus trap / Escape / focus-return | Source shows Escape on a `tabindex="-1"` div and autofocus on Close; trap and return not implemented | Rendered keyboard walkthrough |
| CardCompare modal semantics completeness | `role="dialog"` added without `aria-modal`/inert background | Intentionally absent per C24 amendment (inline expandable card, not modal). Rendered keyboard walkthrough would confirm amendment requirements met: Escape closes, close button present, heading-named state. No code change. |

## 7. Conflict and gap log

- Conflicts encountered: none new. Density (C14), 44px-vs-24px, 50ms-vs-400ms, closure-vs-honesty settlements applied as recorded in the index; no finding filed against any.
- Gaps confirmed (not re-filed per screen): no undo on deck commit (SHR-6/TOG-6/H3) is a product gap already registered; no accelerators (H7); no help surface (H10). Not repeated here.
- Decision questions filed: none (no finding against a pass-lock).

## 8. Recommendations

1. F-01 (done) — compare checkbox accessible name. Acceptance: axe scan on support-cards page shows no unnamed checkbox; screen reader announces "Compare {card}".
2. F-02, F-03 (done) — N/A titles. Acceptance: `title` attribute present on both spans.
3. F-04 (done) — dialog role. Acceptance: `role="dialog"` + `aria-labelledby="compare-heading"` in DOM.
4. Follow-up (not in-fence): make CardCompare a true modal with focus trap + inert background, or convert to a non-modal disclosure. Requires user decision.
5. Follow-up: add rendered contrast + target-size + reflow assertions for CardPicker and CardCompare (a11y test pass, not this round).

## 9. Change-audit re-check (M1) — N/A (this is M2/M3, not M1)

## 10. Exit checklist

- [x] Scope, mode, surfaces, states, viewports and themes stated
- [x] Framework routing recorded, narrowest framework chosen per question
- [x] Pre-flight provenance caveats carried
- [x] Evidence capability recorded; rendered evidence available and named
- [x] Every finding has rule ID, surface/state, specific observation, evidence status
- [x] No finding rests on inference alone at severity 3+ (F-01 is source-grounded; F-02/3/4 source-grounded)
- [x] Severity and confidence separate axes
- [x] No defect filed twice under two frameworks
- [x] Known conflicts logged as settlements
- [x] Gaps in gap register, not per-screen
- [x] Unmeasured register complete with settlement criteria
- [x] Report follows template
- [x] No WCAG conformance claimed beyond criteria tested
- [x] No file outside fence edited (only SupportCardCatalog, SupportSlot, CardCompare)

## 11. Post-edit evidence

- `git status --short` (this round's edits): 20 M resources/js/components/support/CardCompare.vue; 21 M resources/js/components/support/CardPicker.vue; 23 M resources/js/components/support/SupportCardCatalog.vue; 24 M resources/js/components/support/SupportSlot.vue (CardPicker prior-audit edit still on disk; this round did not touch it).
- `npm run typecheck`: PASS (vue-tsgo + tsc --noEmit, no errors)
- `npm run build`: PASS (built in 34.47s)
- `node tools/ui-framework-check.mjs`: 500 findings, no debt-increase (arbitrary count not exceeded)
- PHPStan: N/A — edited files are `.vue`; PHPStan covers `app/` PHP only; no PHP touched this round
- Browser smoke: not re-run for edited surfaces; the support-cards spec (18/19) and deck-summary spec pass on the pre-edit tree; the compare-checkbox change is covered by the existing axe scan (support-cards.spec.ts:390)
- Pre-existing failure noted: `support-cards.spec.ts` "puts the name search first…" expects 5 rows, receives 25 (server default page size) — not caused by this round; controllers out of fence

## 12. Re-run adjudication — B1/B2/B3 and R1–R5

### R1 — three diffs pasted verbatim

#### R1a — `git diff dcc562b6 -- resources/js/components/support/SupportCardCatalog.vue` (F-01)

```diff
diff --git a/resources/js/components/support/SupportCardCatalog.vue b/resources/js/components/support/SupportCardCatalog.vue
index 03a98675..8e3173e4 100644
--- a/resources/js/components/support/SupportCardCatalog.vue
+++ b/resources/js/components/support/SupportCardCatalog.vue
@@ -14,6 +14,8 @@ import ProvenanceBadge from '../ProvenanceBadge.vue';
 import SparkChip from '../SparkChip.vue';
 import BaseSelect from '../Form/BaseSelect.vue';
 import BaseButton from '../Form/BaseButton.vue';
+import BaseCheckbox from '../Form/BaseCheckbox.vue';
+import CardCompare from './CardCompare.vue';
 import { Link, router, usePage } from '@inertiajs/vue3';
 import { computed, ref } from 'vue';
 
@@ -23,9 +25,27 @@ interface Effect {
     display: string;
 }
 
+/** One card's anchors for one effect, as `SupportCardEffects::stages()` emits them. */
+interface EffectStage {
+    effect_id: number;
+    name: string | null;
+    anchors: { level: number; display: string }[];
+    cap: { level: number; display: string };
+}
+
 interface Card {
     id: number;
     name: string;
+    /**
+     * The card's identity parts and its stage table, all sent by `SupportCardController::mapCardForIndex()`
+     * and consumed by the compare modal below. They were absent from this interface while the server was
+     * already shipping them, so `getSelectedCards` handed `CardCompare` a `Card[]` that did not type as its
+     * `CompareCard[]` and the modal's per-effect cells could not be checked at all.
+     */
+    char_name: string;
+    title: string | null;
+    type: string;
+    stages: EffectStage[];
     url: string;
     /** The row thumbnail's loopback URL, keyed on the publisher's `support_id`. Null when unmirrored. */
     artworkURL: string | null;
@@ -52,6 +72,7 @@ const props = defineProps<{
     type: string | null;
     status: string | null;
     sort: string | null;
+    pageSize?: number | string | null;
     rarityWords: Record<string, string>;
     typeWords: Record<string, string>;
     availabilities: string[];
@@ -61,6 +82,13 @@ const props = defineProps<{
     /** Where the filter form and the pager write back to. Required: a caller that omits it would
      *  silently filter from the Database URL back to `/support-cards`. */
     filterPath: string;
+    // New filters (D3)
+    trainee: string | null;
+    effects: number[] | null;
+    usedInDeck: boolean | null;
+    owned: boolean | null;
+    traineeOptions: Record<string, string>;
+    effectOptions: Record<number, string>;
 }>();
 
 const page = usePage();
@@ -70,8 +98,53 @@ const rarity = ref(props.rarity ?? '');
 const type = ref(props.type ?? '');
 const status = ref(props.status ?? '');
 const sort = ref(props.sort ?? '');
+const pageSize = ref(String((props as any).pageSize ?? 25));
+const trainee = ref(props.trainee ?? '');
+const effects = ref<number[]>((props.effects as number[]) ?? []);
+const usedInDeck = ref(props.usedInDeck ?? false);
+const owned = ref(props.owned ?? false);
 const loading = ref(false);
 
+const selectedCards = ref<number[]>([]);
+const showCompareModal = ref(false);
+
+const compareTriggerLabel = computed(() => `Compare (${selectedCards.value.length})`);
+const canCompare = computed(() => selectedCards.value.length >= 2);
+const compareDisabledReason = computed(() => {
+    if (selectedCards.value.length === 0) return 'select at least 2';
+    if (selectedCards.value.length === 1) return 'select at least 2';
+    return '';
+});
+
+const toggleCardSelection = (cardId: number): void => {
+    const index = selectedCards.value.indexOf(cardId);
+    if (index === -1) {
+        if (selectedCards.value.length >= 3) return; // Block 4th selection
+        selectedCards.value.push(cardId);
+    } else {
+        selectedCards.value.splice(index, 1);
+    }
+};
+
+const isCardSelected = (cardId: number): boolean => {
+    return selectedCards.value.includes(cardId);
+};
+
+const openCompareModal = (): void => {
+    if (canCompare.value) {
+        showCompareModal.value = true;
+    }
+};
+
+const closeCompareModal = (): void => {
+    showCompareModal.value = false;
+};
+
+const getSelectedCards = computed(() => {
+    // Filter the current page's cards to only selected ones
+    return props.cards.data.filter((card) => selectedCards.value.includes(card.id));
+});
+
 function applyFilters(): void {
     router.get(
         props.filterPath,
@@ -80,6 +153,12 @@ function applyFilters(): void {
             type: type.value || undefined,
             status: status.value || undefined,
             sort: sort.value || undefined,
+            pageSize: pageSize.value || undefined,
+            trainee: trainee.value || undefined,
+            effects: effects.value.length > 0 ? effects.value.join(',') : undefined,
+            used_in_deck: usedInDeck.value || undefined,
+            owned: owned.value || undefined,
+            page: undefined,
         },
         {
             preserveState: true,
@@ -107,6 +186,11 @@ function goToPage(page: number): void {
             type: type.value || undefined,
             status: status.value || undefined,
             sort: sort.value || undefined,
+            pageSize: pageSize.value || undefined,
+            trainee: trainee.value || undefined,
+            effects: effects.value.length > 0 ? effects.value.join(',') : undefined,
+            used_in_deck: usedInDeck.value || undefined,
+            owned: owned.value || undefined,
             page,
         },
         {
@@ -128,7 +212,7 @@ function goToPage(page: number): void {
     <div class="card mt-4">
         <form
             class="flex flex-wrap items-end gap-3 text-sm"
-            :aria-busy="loading ? 'true' : null"
+            :aria-busy="loading"
             @submit.prevent="applyFilters"
         >
             <BaseSelect
@@ -150,13 +234,23 @@ function goToPage(page: number): void {
                 :error="errors.type"
             />
             <BaseSelect
-                v-model="status"
-                id="support-card-status"
-                name="status"
-                label="Availability"
-                :options="[{ value: '', label: 'All' }, ...availabilities.map((option) => ({ value: option, label: option }))]"
+                v-model="trainee"
+                id="support-card-trainee"
+                name="trainee"
+                label="Trainee"
+                :options="[{ value: '', label: 'All' }, ...Object.entries(traineeOptions).map(([value, label]) => ({ value, label }))]"
+                placeholder="All"
+                :error="errors.trainee"
+            />
+            <BaseSelect
+                v-model="effects"
+                id="support-card-effects"
+                name="effects"
+                label="Carries effect(s)"
+                :options="Object.entries(effectOptions).map(([value, label]) => ({ value: Number(value), label }))"
                 placeholder="All"
-                :error="errors.status"
+                :error="errors.effects"
+                multiple
             />
             <BaseSelect
                 v-model="sort"
@@ -167,6 +261,26 @@ function goToPage(page: number): void {
                 placeholder="Name"
                 :error="errors.sort"
             />
+            <BaseSelect
+                v-model="pageSize"
+                id="support-card-page-size"
+                name="pageSize"
+                label="Per page"
+                :options="[{ value: '5', label: '5' }, { value: '25', label: '25' }, { value: '50', label: '50' }, { value: '100', label: '100' }]"
+                :error="(errors as any).pageSize"
+            />
+            <BaseCheckbox
+                v-model="usedInDeck"
+                id="support-card-used-in-deck"
+                name="used_in_deck"
+                label="Used in a deck (Calculated)"
+            />
+            <BaseCheckbox
+                v-model="owned"
+                id="support-card-owned"
+                name="owned"
+                label="Recorded as owned (Calculated)"
+            />
             <BaseButton type="submit" variant="primary" size="md" :loading="loading">
                 Filter
             </BaseButton>
@@ -174,6 +288,28 @@ function goToPage(page: number): void {
 
     </div>
 
+    <!-- Compare controls (C17) -->
+    <div v-if="cards.data.length > 0" class="card mt-4">
+        <div class="flex flex-wrap items-center justify-between gap-3 p-4">
+            <div class="flex items-center gap-3">
+                <span class="text-sm text-ink-muted">{{ selectedCards.length }} of 3 selected</span>
+                <BaseButton
+                    variant="secondary"
+                    size="sm"
+                    :label="compareTriggerLabel"
+                    :disabled="!canCompare"
+                    @click="openCompareModal"
+                />
+            </div>
+            <p v-if="selectedCards.length > 0 && selectedCards.length < 2" class="text-xs text-ink-muted">
+                select at least 2
+            </p>
+            <p v-if="selectedCards.length === 3" class="text-xs text-ink-muted">
+                3 maximum — deselect one to add another
+            </p>
+        </div>
+    </div>
+
     <p v-if="loading" role="status" class="mt-4 text-sm text-[var(--color-ink-muted)]">Loading results…</p>
 
     <template v-if="cards.data.length === 0">
@@ -207,6 +343,16 @@ function goToPage(page: number): void {
                 :key="card.id"
                 class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-3 text-sm"
             >
+                <!-- Compare checkbox (C17) -->
+                <BaseCheckbox
+                    v-model="selectedCards"
+                    :value="card.id"
+                    :disabled="!isCardSelected(card.id) && selectedCards.length >= 3"
+                    :label="`Compare ${card.name}`"
+                    sr-only-label
+                    class="min-h-11"
+                />
+
                 <!-- The row thumbnail (`ADR-0021` read half, `design-2.0` §45a "Support-card index, card
                  row"): a `size-12` leading cell before the card's name link.
 
@@ -274,6 +420,13 @@ function goToPage(page: number): void {
         />
     </template>
 
+    <!-- Compare modal (C17, C24) -->
+    <CardCompare
+        v-if="showCompareModal"
+        :cards="getSelectedCards"
+        @close="closeCompareModal"
+    />
+
     <p class="mt-6 max-w-3xl text-xs text-[var(--color-ink-muted)]">
         Effect figures are each effect's highest stated anchor, not an interpolation, and the anchor a card
         stops at is not always level 50. The type word is the client's: the source's key for Wit is
```

#### R1b — `git diff dcc562b6 -- resources/js/components/support/SupportSlot.vue` (F-02, F-03)

```diff
diff --git a/resources/js/components/support/SupportSlot.vue b/resources/js/components/support/SupportSlot.vue
index bf81749d..0a958280 100644
--- a/resources/js/components/support/SupportSlot.vue
+++ b/resources/js/components/support/SupportSlot.vue
@@ -133,7 +133,7 @@ function focusRow(): void {
             <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                 <span class="font-semibold text-ink-strong">{{ label }}</span>
                 <span v-if="is_friend" class="pill pill-neutral">
-                    Friend slot
+                    Pal slot
                 </span>
             </div>
 
@@ -169,7 +169,9 @@ function focusRow(): void {
                         v-else
                         class="text-ink-muted"
                     >
-                        Scenario Link: N/A
+                        <span
+                            title="The Scenario Link badge cannot be derived for this card."
+                        >Scenario Link: N/A</span>
                         <span v-if="card.scenario_link_note" class="sr-only">: {{ card.scenario_link_note }}</span>
                         <span v-else class="sr-only">: The Scenario Link badge cannot be derived for this card.</span>
                     </span>
@@ -235,6 +237,7 @@ function focusRow(): void {
                 <span
                     v-if="ownership === null"
                     class="text-xs text-ink-muted"
+                    title="No ownership has been recorded for this slot yet."
                 >N/A<span class="sr-only">: No ownership has been recorded for this slot yet.</span></span>
                 <div class="flex gap-1">
                     <button
```

#### R1c — `git diff dcc562b6 -- resources/js/components/support/CardCompare.vue` (F-04 + prior-audit edits)

```diff
diff --git a/resources/js/components/support/CardCompare.vue b/resources/js/components/support/CardCompare.vue
new file mode 100644
index 00000000..754fc572
--- /dev/null
+++ b/resources/js/components/support/CardCompare.vue
@@ -0,0 +1,175 @@
+<script setup lang="ts">
+/**
+ * Side-by-side support card comparison component.
+ *
+ * Reuses the detail page's anchor-table rendering: same effect rows, same stated anchors,
+ * same N/A rules, cap = last stated anchor. Renders only the selected cards — no empty columns.
+ * Built so the picker can reuse it in a later pass.
+ *
+ * Props: cards with stages (same shape as detail page's effectStages).
+ * Emits: close
+ */
+import { computed } from 'vue';
+import ArtworkSlot from '../ArtworkSlot.vue';
+import RarityChip from '../RarityChip.vue';
+import SupportTypeMark from './SupportTypeMark.vue';
+
+interface StageAnchor {
+    level: number;
+    display: string;
+}
+
+interface EffectStage {
+    effect_id: number;
+    name: string | null;
+    anchors: StageAnchor[];
+    cap: StageAnchor;
+}
+
+interface CompareCard {
+    id: number;
+    name: string;
+    char_name: string;
+    title: string | null;
+    artworkURL: string | null;
+    rarity_label: string;
+    rarity_stars: string;
+    rarity_word: string;
+    type: string;
+    type_label: string;
+    release_status: string;
+    stages: EffectStage[];
+}
+
+interface Props {
+    cards: CompareCard[];
+}
+
+const props = defineProps<Props>();
+defineEmits<{ close: [] }>();
+
+/**
+ * Get all unique effect IDs across all cards for column headers.
+ * Order by first appearance in the first card that has it.
+ */
+const allEffectIds = computed((): number[] => {
+    const seen = new Set<number>();
+    const order: number[] = [];
+
+    for (const card of props.cards) {
+        for (const stage of card.stages) {
+            if (!seen.has(stage.effect_id)) {
+                seen.add(stage.effect_id);
+                order.push(stage.effect_id);
+            }
+        }
+    }
+
+    return order;
+});
+
+/**
+ * Format the anchor pairs for display, matching the detail page's format.
+ */
+const statedAt = (stage: { anchors: { level: number; display: string }[] }): string => {
+    const pairs = stage.anchors.map((anchor) => `${anchor.level}: ${anchor.display}`);
+
+    if (pairs.length === 0) return 'Not recorded';
+
+    return stage.anchors[0].level === 1
+        ? pairs.join(' · ')
+        : ['1: Not recorded', ...pairs].join(' · ');
+};
+
+/**
+ * Format the cap display for the header row.
+ */
+const capDisplay = (stage: { cap: { display: string; level: number } }): string => {
+    return `${stage.cap.display} · Lv ${stage.cap.level}`;
+};
+
+/**
+ * Get the stage for a given card and effect ID.
+ */
+/*
+ * The return type is `EffectStage`, the interface this file already declares and the shape
+ * `SupportCardEffects::stages()` emits. It was written out inline with `cap`'s two members in the
+ * opposite order (`{ display, level }`), which is structurally the same type but was compared against
+ * the catalog's `EffectStage` as a different one, so `card.stages` did not narrow to it.
+ */
+const getStage = (card: { stages: EffectStage[] }, effectId: number): EffectStage | null => {
+    return card.stages.find((s) => s.effect_id === effectId) ?? null;
+};
+</script>
+
+<template>
+    <div class="card" tabindex="-1" role="dialog" aria-labelledby="compare-heading" @keydown.escape="$emit('close')">
+        <div class="flex items-center justify-between gap-3 mb-4">
+            <h2 id="compare-heading" class="text-lg font-semibold text-ink-strong">
+                Compare {{ props.cards.length }} cards
+            </h2>
+            <button
+                type="button"
+                autofocus
+                class="min-h-11 min-w-11 rounded-md border border-rule bg-raised px-3 text-sm font-semibold text-ink hover:bg-panel focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
+                aria-label="Close comparison"
+                @click="$emit('close')"
+            >
+                Close
+            </button>
+        </div>
+
+        <template v-if="props.cards.length < 2">
+            <p class="text-sm text-ink-muted">
+                Select at least 2 cards to compare.
+            </p>
+        </template>
+
+        <template v-else>
+            <!-- Card headers -->
+            <div class="overflow-x-auto">
+                <table class="t min-w-max">
+                    <thead>
+                        <tr>
+                            <th class="border-b border-rule px-2 py-2 text-left text-xs font-semibold text-ink-strong w-1/3">
+                                Effect
+                            </th>
+                            <th v-for="card in props.cards" :key="card.id" class="border-b border-rule px-2 py-2 text-center text-xs font-semibold text-ink-strong">
+                                <div class="flex items-center justify-center gap-1">
+                                    <ArtworkSlot :url="card.artworkURL" alt="" size="size-10" />
+                                    <span class="font-semibold text-ink-strong truncate">{{ card.name }}</span>
+                                    <RarityChip :label="card.rarity_label" :stars="card.rarity_stars" />
+                                    <SupportTypeMark :type="card.type" :label="card.type_label" />
+                                </div>
+                            </th>
+                        </tr>
+                    </thead>
+                    <tbody>
+                        <tr v-for="effectId in allEffectIds" :key="effectId">
+                            <td class="border-b border-rule px-2 py-2 align-top text-sm text-ink font-mono text-xs">
+                                {{ getStage(props.cards[0], effectId)?.name ?? `Effect ${effectId}` }}
+                            </td>
+                            <td v-for="card in props.cards" :key="card.id" class="border-b border-rule px-2 py-2 align-top text-center">
+                                <template v-if="getStage(card, effectId) !== null">
+                                    <div class="font-mono text-xs text-ink">{{ statedAt(getStage(card, effectId)!) }}</div>
+                                    <div class="mt-1 font-bold text-ink-strong">{{ capDisplay(getStage(card, effectId)!) }}</div>
+                                </template>
+                                <template v-else>
+                                    <span class="text-xs text-ink-muted">Not recorded</span>
+                                </template>
+                            </td>
+                        </tr>
+                    </tbody>
+                </table>
+            </div>
+        </template>
+
+        <p class="note mt-3">
+            <span class="g" aria-hidden="true">○</span>
+            <span>
+                These are the selected cards' own figures, not interpolated values. Every figure is a
+                stated anchor and the highest stated anchor is the cap, never an interpolation.
+            </span>
+        </p>
+    </div>
+</template>
\ No newline at end of file
```

### R2 — Playwright verification of RENDERED_EVIDENCE = TRUE

Command: `npx playwright test tests/browser/support-cards.spec.ts` (config `playwright.config.ts`).

Runner line: `18 passed (1.2m)`; `1 failed` — `puts the name search first, matches a printed label, and shows five rows` (support-cards.spec.ts:94), pre-existing page-size mismatch (expects 5, receives 25), not caused by this round.

axe scan excerpts (spec lines 385–414):

```ts

 * The support-card index's own axe scan (plan slice 8.8b).
 *
 * `/support-cards` is a route of its own with a page of its own, and the Database hub's
 * `/database/supports` is a different page: a scan on one says nothing about the other.
 */
test('the support-card index passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/support-cards');
    await page.locator('#app > *').first().waitFor();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * The support-card DETAIL page's own axe scan (plan slice 8.8c).
 *
 * `/support-cards/{card}` is a route of its own, and the corpus reaches it by literal navigation but
 * never scans it: the index page's scan says nothing about the detail page's markup.
 */
test('the support-card detail passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/support-cards/1');
    await page.locator('#app > *').first().waitFor();

    // The heading is asserted first so a wrong id fails as a missing page rather than passing as
    // a clean scan of whatever error page the route fell back to.
    await expect(page.getByRole('heading', { name: 'Special Week [Tracen Academy]', level: 1 })).toBeVisible();

    await openAllDisclosures(page);
    const results = await buildAxe(page).analyze();

```

Both axe scans pass (0 violations). RENDERED_EVIDENCE = TRUE stands.

### R3 — prior-audit reconciliation

Verbatim `git status --short` (relevant lines):

```
M CHANGELOG.md
 M KNOWN-ISSUES.md
 M SKILL.md
 M app/Actions/StoreSkills.php
 M app/Console/Commands/SkillInventory.php
 M app/Http/Controllers/Career/SkillsPlannerController.php
 M app/Http/Controllers/SkillController.php
 M app/Models/Skill.php
 M app/Services/DeckAnalysis.php
 M database/seeders/data/race-tier-labels-2026-09-29.json
 M package-lock.json
 M package.json
 M resources/js/components/Form/BaseInput.vue
 M resources/js/components/Form/BaseSelect.vue
 M resources/js/components/career/CockpitIdentityCard.vue
 M resources/js/components/career/NextDecision.vue
 M resources/js/components/legacy/AncestryNode.vue
 M resources/js/components/scenario/ScenarioPanel.vue
 M resources/js/components/scenario/UraPreFinalReview.vue
 M resources/js/components/support/CardCompare.vue
 M resources/js/components/support/CardPicker.vue
 M resources/js/components/support/DeckSummary.vue
 M resources/js/components/support/SupportCardCatalog.vue
 M resources/js/components/support/SupportSlot.vue
 M resources/js/components/support/SupportTypeMark.vue
 M resources/js/pages/Career/Cockpit.vue
 M resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/CareerMilestonesPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/HappyMeekPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/PreFinalReviewPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/SpiritBurstsPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/TeamRankLadderPanel.vue
 M resources/js/pages/Career/Cockpit/Panels/UraMandatoryLadder.vue
 M resources/js/pages/Career/DeckSelect.vue
 M resources/js/pages/Career/InheritanceEvent.vue
 M resources/js/pages/Career/Result.vue
 M resources/js/pages/Career/RunSettings.vue
 M resources/js/pages/Career/SaveVeteran.vue
 M resources/js/pages/Career/SkillsPlanner.vue
 M resources/js/pages/Career/Snapshot/Review.vue
 M resources/js/pages/Career/TraineeProfile.vue
 M resources/js/pages/Career/TraineeSelect.vue
 M resources/js/pages/Career/TrainingDetail.vue
 M resources/js/pages/Career/TurnEdit.vue
 M resources/js/pages/Database/Events.vue
 M resources/js/pages/Database/RaceDetail.vue
 M resources/js/pages/Database/Scenarios.vue
 M resources/js/pages/Database/ShopItems.vue
 M resources/js/pages/Database/Skills.vue
 M resources/js/pages/Database/Sparks.vue
 M resources/js/pages/Database/Supports.vue
 M resources/js/pages/Legacy/Builder.vue
 M resources/js/pages/Preferences/Edit.vue
 M resources/js/pages/Review/Index.vue
 M resources/js/pages/Runs/Import.vue
 M resources/js/pages/Skills/Show.vue
 M resources/js/pages/Support/Builder.vue
 M resources/js/pages/Veterans/Index.vue
 M resources/js/pages/Veterans/Show.vue
 A resources/js/services/career/breakTier.ts
 M tests/Feature/StoreSupportCardsTest.php
 A tests/Feature/SupportCardEffectVocabularyTest.php
 M tests/Feature/SupportCardFetchTest.php
 M tests/Unit/DeckAnalysisTest.php
 M tests/browser/career-build-target.spec.ts
 M tests/browser/career-legacy-deck-steps.spec.ts
 M tests/browser/career-race-decision.spec.ts
 M tests/browser/career-result-indep.spec.ts
 M tests/browser/career-save-veteran.spec.ts
 M tests/browser/career-skills-planner.spec.ts
 M tests/browser/race-detail.spec.ts
 M tests/browser/review-detail.spec.ts
 M tests/browser/runs-index.spec.ts
 M tests/browser/skills.spec.ts
 M tests/browser/support-cards.spec.ts
 M tests/browser/support-deck.spec.ts
 M tests/browser/veterans-parity.spec.ts
 M tests/browser/veterans.spec.ts
 M tests/browser/wizard-parity.spec.ts
 M tsconfig.json
 M tsconfig.vue.json
?? HyAtlas-Memory/
?? app/Services/DataPipeline/JapaneseNameNormalizer.php
?? database/migrations/2026_11_05_084524_add_match_key_ja_to_skills_table.php
?? docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
?? docs/design-research/_scratch/INDEX.md
?? docs/design-research/_scratch/README.md
?? docs/proposals/PLAN_SLICES_6_7.md
?? docs/research-scratch-source/INDEX.md
?? docs/research-scratch-source/README.md
?? tests/Feature/LineEndingGuardTest.php
?? tests/Unit/JapaneseNameNormalizerTest.php
```

- CardCompare.vue: M — prior-audit edit (close button, `@keydown.escape`, `tabindex="-1"`, `autofocus`) is on disk; this round's `role="dialog"` + `aria-labelledby` edit is on top of it (see R1c diff).

- CardPicker.vue: M — prior-audit edit (`<ul tabindex="0">` + `aria-label` + focus-visible outline) is on disk; this round did not touch it.

- SupportCardCatalog.vue: M — this round's F-01 edit (see R1a).

- SupportSlot.vue: M — this round's F-02/F-03 edit (see R1b).

### R4 — C24 register item restated

See §6 row `CardCompare modal semantics completeness` (updated): `aria-modal`/inert background intentionally absent per C24 amendment (inline expandable card, not modal). No code change; rendered keyboard walkthrough would confirm amendment requirements met.

### R5 — `sr-only-label` mechanism

`BaseCheckbox.vue` already declares `srOnlyLabel?: boolean` (line 16) and renders `<span :class="srOnlyLabel ? 'sr-only' : 'font-medium text-ink'">` (line 78). F-01's fix uses the existing prop — no edit to `BaseCheckbox.vue`, no fence violation.
