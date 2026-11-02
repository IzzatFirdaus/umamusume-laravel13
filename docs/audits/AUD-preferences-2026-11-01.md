# Audit report — M2 screen audit: Preferences (SCR-SYS-002)

## 1. Identity

- Audit id: AUD-20261101-003
- Date: 2026-11-01
- Mode: **M2 screen audit** (A11y overlay applied as a separate track, Phase 5)
- Scope: `SCR-SYS-002` Preferences — route `GET /preferences` (`preferences.edit`), controller `app/Http/Controllers/PreferenceController.php`, page `resources/js/pages/Preferences/Edit.vue`, plus `SCREEN_SPEC.md` §SCR-SYS-002, `tests/Feature/PreferenceControlsTest.php`, `tests/browser/preferences.spec.ts`.
- Out of scope: backend behaviour beyond the controller's own queries and the Form Request, the career surfaces (audited as AUD-20261101-002, which already covers `RunSettings` F-01/F-11 — not duplicated here, only referenced), performance, content strategy, AAA criteria, the `eva01` third theme's palette values, any fix.
- Frameworks applied: `docs/framework-ui-ux/design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Information architecture, Gestalt, Norman, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run. All claims are `OBSERVED_SOURCE` or `UNMEASURED`.

## 2. Executive summary

- **F-02 — refused submissions that name no control are not announced.** The page-level error block below the cards (`<p class="text-risk">`) carries no `role="alert"` or `aria-live`, so a whole-blob or theme/layout/dashboard refusal is silent unless the Trainer is already focused on it. Severity 2.
- **F-03 — `aria-describedby` points at ids that are never rendered.** For theme, layout width, dashboard and failure-estimate refusals the page's `describedBy()` returns `error-*`, but the only rendered block is `error-block-*`; the reference is dangling. Severity 2.
- **F-04 — focus is not moved for four refusal classes.** `onError` looks up `field-${key}`, which does not exist for `theme`, `settings.dashboard_layout`, `settings.dashboard_hidden_cards` (their ids are `field-theme_light`, `field-settings.dashboard_layout_standard`, `dashboard_card_*`). Severity 2.
- **F-05 — "Download a backup" promises a download the action does not perform.** The button label contradicts the server-side snapshot it actually writes; the helper line and the flash carry the truth, but the affordance does not. Severity 2.
- Largest unmeasured risk: contrast and target size on the dashboard table and the 10 px / 12.5 px headers, which have no rendered pass at all; the preferences browser spec was not executed in this sandbox.

## 3. Scope and coverage

| Surface / state | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard | browser spec |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Preferences (SCR-SYS-002) | n/a (defaults) | yes | yes (refusal) | yes (submit) | src | src | named | named | named | named | src | preferences.spec.ts |

The screen has no true empty state: unset values render as empty fields or "Not set" (named absence, `SCREEN_SPEC` §SCR-SYS-002 "Named absence"). Loading is submit-scoped only (`:aria-busy` on the form, "Saving" state line and button); there is no page-level loading for an Inertia visit, which `SCREEN_SPEC` does not require for this screen.

Framework coverage (applied and produced findings): `H1`, `H2`, `H4`, `IA-1`, `NRM-1`, `TOG-1`, `WCAG 1.3.1`, `WCAG 2.4.3`, `WCAG 4.1.2`, `WCAG 4.1.3`. Applied, measured clean from source (no finding): `H5`/`NRM-3`/`SHR-5` (constraint vs validation — `UpdatePreferenceRequest` refuses unknown keys and out-of-range values, and the stat fields state the range in their hint rather than a browser-side bound, so the refusal is shown, not silent); `SHR-6`/`TOG-6`/`H3` (reversal — the save is reversible by re-saving; no destructive action on this screen); `SHR-7`/`SHR-8` (value on the decision screen — the stored values pre-fill every control); `GP-1`/`GP-3` (no arithmetic left in the user's head — the hard cap is printed in the hint); `GT-2`/`UI-4` (absent values carry a word, not a dash); `IA-2` (banded nav via `AppLayout`); `IA-5` (every empty default is explained); `WCAG 2.1.1`/`2.4.7`/`4.1.2` (links, buttons, labels present); `WCAG 1.4.1` (stale state carries word + border); `WCAG 3.3.1`/`3.3.2`/`3.3.3` (refusal messages are specific and constructive). `N/A` by the framework's own list: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `SHR-4` (no multi-step flow closure), `IA-3`/`IA-6` (structure between screens out of scope), AAA criteria. Not reached: `WCAG 1.4.4`/`1.4.10`/`1.4.12` (no resize pass), `WCAG 3.2.6`/`3.3.7` (not exercised), `WCAG 2.4.11` (focus not obscured — not measured).

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run.
- Structural and copy claims are `OBSERVED_SOURCE` against the page and controller source read this run; visual, contrast, target-size and reflow claims are `UNMEASURED` (§6).
- Provenance caveats carried (audit prompt §3.1): Gerhardt-Powals primary text paywalled, items 5/6 an unconfirmed split (nothing here rests on that boundary); IA prose unread, only names/summaries are the article's own (IA-1 used only as its derived sentence); Norman's book not fetched, quotes from the encyclopedia entry; Tognazzini sixteen are the original edition, 2014 count unverified; Laws of UX holds 29 (Zeigarnik from its own page); Laws of UI holds 13 and publishes Closure and Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.
- Cross-reference: `RunSettings` (audited in AUD-20261101-002 as F-01, F-11) is a different screen and is not re-filed here; its error-surface and lede defects are referenced, not duplicated.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `WCAG 4.1.3` (secondary: `H1`, `TOG-1`) | WCAG 2.2 / Heuristics / Tognazzini | Preferences, all states, all widths | The page-level error block below the cards (`<p v-for … class="text-risk">`) carries no `role="alert"` and no `aria-live`, so a refusal that names no control (a whole-blob `settings`/`preferences` refusal) is never announced; the control's `aria-describedby` points at an id that is not a live region either. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:524-531` (no `role`/`aria-live` on the block) | 2 | High | Add `role="alert"` (and keep the block always mounted, mirroring `Flash.vue`) so refusals announce. Acceptance: a refused submit produces an announcement without focus. |
| F-02 | `WCAG 4.1.2` (secondary: `WCAG 1.3.1`, `H1`) | WCAG 2.2 | Preferences, error state, all widths | `describedBy()` returns `error-${key}` for theme, layout width, dashboard and failure-estimate refusals, but the only rendered block is `error-block-${key}`; the inline error (which would carry `error-*`) is only rendered for stat inputs and the default scenario. The reference dangles for four refusal classes. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:211-215, 524-531` | 2 | High | Either render the page-level block with `id="error-${key}"`, or stop advertising `aria-describedby` when no element carries that id. Acceptance: every `aria-describedby` token resolves to an element. |
| F-03 | `WCAG 2.4.3` (secondary: `H1`, `TOG-1`) | WCAG 2.2 / Heuristics / Tognazzini | Preferences, error state, all widths | `onError` calls `document.getElementById(controlId(firstKey))`, i.e. `field-${key}`. That id exists for stat targets, the default scenario and failure estimate, but not for `theme` (`field-theme_light`…), `settings.dashboard_layout` (`field-settings.dashboard_layout_standard`…) or `settings.dashboard_hidden_cards` (`dashboard_card_*`). Focus is not moved for those refusals. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:267-276` (controlId at 213) | 2 | Medium | Map each error key to its first real control id (or focus the page-level block when no control applies). Acceptance: a theme/layout/dashboard refusal moves focus to a control or to the message. |
| F-04 | `H1` (secondary: `NRM-1`) | Heuristics / Norman | Preferences, populated, all widths | The Data card's primary action is labelled "Download a backup", but `backup()` writes a server-side `VACUUM INTO` snapshot under `storage/app/backups/` and downloads nothing; the truth lives only in the helper line and the flash. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:398-400, 402-404` | 2 | Medium | Relabel the control to "Write a backup" (or "Back up now") so the affordance matches the action; keep the path in the flash. Acceptance: the button's name no longer promises a download. |
| F-05 | `H1` (secondary: `H9`) | Heuristics | Preferences, populated, all widths | The "Wide" layout option is `disabled` while `dashboard.wide_bento_enabled` is false, but nothing on the card states why, so a Trainer meeting a disabled radio with no reason cannot tell whether it is broken or deliberate. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:340` (disabled with no hint/reason) | 1 | Medium | Add a one-line note on the card (or a disabled-option hint) naming the DL.14 hold, mirroring the Language card's reason. Acceptance: the disabled option carries its reason in text. |
| F-06 | `TOG-1` (secondary: `H1`, `WCAG 4.1.3`) | Tognazzini / Heuristics / WCAG | Preferences, populated, all widths | "Reset layout to defaults" arms a five-second second-click window whose only signal is the button label flipping to "Reset?"; there is no live region announcing the armed state, and the `sr-only` `role="status"` is created in the same tick as its message, so it may never announce. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:166-195, 519` | 1 | Medium | Announce the armed state through an always-mounted live region (as `Flash.vue` does) and state the five-second window in the button's accessible name. Acceptance: a screen reader announces "armed, click again within five seconds". |
| F-07 | `IA-1` (secondary: `H1`) | Information architecture / Heuristics | Preferences, all states, all widths | The breadcrumb's leaf reads "Settings" while the page's `h1` reads "Preferences" and the nav item reads "Settings"; the crumb and the heading disagree about where the Trainer is. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:287` (`breadcrumb="[{ label: 'Settings' }]"`) vs `PageHeader` title "Preferences" | 1 | Medium | Pass `label: 'Preferences'` (or add the missing `href` chain) so the crumb matches the heading. Acceptance: the crumb's last segment equals the `h1`. |
| F-08 | `H4` (secondary: `SHR-1`) | Heuristics / Shneiderman | Preferences, error state, all widths | Stat-target and default-scenario refusals render twice: once inline (BaseInput/BaseSelect, `role="alert"`) and once in the page-level block below the cards, so a screen reader hears the same message twice. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:370-391, 524-531` | 1 | High | Render each refusal once — either inline (preferred, since `aria-describedby` already points there) or in the block, not both. Acceptance: one announcement per refusal. |
| F-09 | `H2` (secondary: AGENTS.md copy rule) | Heuristics | Preferences, populated, all widths | The Theme card's `Note` ships an em dash ("only theme control — a four-option choice"), which the repository's copy rule forbids in shipped copy. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:441` (`&mdash;`) | 1 | High | Replace the em dash with a comma or period. Acceptance: no `—` in the rendered Note. |
| F-10 | `H1` (secondary: `TOG-1`) | Heuristics / Tognazzini | Preferences, populated, all widths | The Backup action's running state is conveyed only by the button label changing to "Backing up"; there is no `role="status"`/`aria-live`, so a Trainer not focused on the button is not told a backup is in progress. | OBSERVED_SOURCE | `resources/js/pages/Preferences/Edit.vue:398-399` (no live region for backup) | 1 | Medium | Add an always-mounted `role="status"` for the backup (or reuse the flash's live region) during the request. Acceptance: the running state is announced without focus. |

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Light- and dark-theme body text meets 4.5:1 on every card | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, risk text) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; spec was not executed | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on every control (checkboxes are `size-6` = 24 px at the input, but their labels carry `min-h-11`) | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and the 1.4.12 text-spacing override lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Screen-reader announcement of the error block, the reset arm and the backup running state | No AT attached | NVDA/VoiceOver pass over populated and error states |
| Whether the dashboard table's 10 px / 12.5 px headers stay readable at 200% zoom | No rendered measurement | Computed-style size comparison at 200% |
| Whether the disabled "Wide" radio is reachable/announced as disabled | No AT attached | Screen-reader pass over the Layout card |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** Target-size observations above are filed under the house rule (`UX-1`/`TOG-5`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`.
- **Density vs minimalism.** The preferences cards are deliberately dense (HFW clone); `H8` is about signal against noise, and every element carries a fact. No finding.
- **Closure vs honesty.** Empty/absent tiles look like closed shapes but carry the word; `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** 50 ms acknowledgement and 400 ms Doherty response read as the index reads them; the only acknowledgement surface on this screen is the `role="status"` state line and the `Flash` banner, whose latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on this screen. No finding.
- **Title-only disclosure (systemic).** Absence reasons and several `N/A` reasons are carried only in `title` attributes across most of the app. This screen's stat hint carries the range in text, so it is not the worst case; the systemic gap is recorded in AUD-20261101-002 and not re-filed here.
- **No undo / no accelerators / no help surface (systemic).** Registered gaps `H3`, `H7`, `H10`: the save is reversible by re-saving, and no destructive action lives on this screen. No per-screen finding.
- **Unregistered screens.** Not applicable — `SCR-SYS-002` is registered.
- **RunSettings cross-reference.** `RunSettings` (unregistered, audited in AUD-20261101-002 as F-01, no error surface, and F-11, lede wording) is a separate screen; its defects are referenced there, not duplicated here.
- **Backup failure is unhandled by design.** `PreferenceController::backup()` states that a failure is unhandled on purpose, because there is no second feedback channel to invent and the app's own 500 document (`SCR-SYS-004`) is the error state. Recorded as a documented absence, not a finding.

Gaps confirmed in the register rather than filed per screen: `H3` (no undo after a committed turn — not screen-specific here), `H7` (no accelerators), `H10` (no help surface), title-only disclosure channel.

## 8. Recommendations

1. **F-02** (S): make the page-level error block a live region (`role="alert"`), always mounted, matching `Flash.vue`. Check: a refused submit announces without focus.
2. **F-03** (S): align `aria-describedby` ids with the rendered block (or drop the dangling references). Check: every `aria-describedby` token resolves.
3. **F-04** (S): map refusal keys to real control ids in `onError` (or focus the message block). Check: theme/layout/dashboard refusals move focus.
4. **F-05** (S): relabel "Download a backup" to "Write a backup"/"Back up now". Check: the button's name no longer promises a download.
5. **F-06** (S): announce the reset arm through an always-mounted live region and state the window in the accessible name. Check: the armed state is announced.
6. **F-08** (S): de-duplicate refusal messages (inline or block, not both). Check: one announcement per refusal.
7. **F-07** (S): align the breadcrumb leaf with the `h1`. Check: crumb's last segment equals "Preferences".
8. **F-10** (S): add a live region for the backup running state. Check: announced without focus.
9. **F-09** (S): replace the em dash in the Theme Note. Check: no `—` in the rendered Note.
10. Register the screen's two open questions (recommendation aggressiveness control absent; `race_risk_thresholds` absent by ADR-0016) with `KNOWN-ISSUES.md` if they are to be tracked as defects rather than documented absences.

## 9. Change-audit re-check

Not owed (mode is M2). No commit is being made in this pass.

## 10. Exit checklist

- Scope, mode, surfaces, states, viewports and themes stated.
- Framework routing recorded, narrowest rule per question, secondaries listed.
- §3.1 provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status.
- No severity 3/4 finding rests on inference (highest severity here is 2).
- Severity and confidence are separate axes.
- No defect filed twice.
- Known conflicts logged as settlements.
- Gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed.
- No conformance claimed beyond criteria tested; no rendered observation claimed.
- Report does not modify app code.
- `RunSettings` F-01/F-11 referenced, not duplicated.
