# 00 — Coverage, UI/UX comprehensive walk (2026-11-18)

**Observation surface:** standalone Playwright scripts driving a real Chromium instance against `http://127.0.0.1:8401`, with a session-private SQLite database at `.scratch-uma/uiux-walk-2026-11-18/browser-8401.sqlite`. No application source was inferred for a rendered claim; every finding below was re-measured in the browser. **No real-user usability testing occurred.**

**Version:** HEAD `816e923e5c9ba374ad2d7203ba53a585256231ab` at the start and end of the walk. Viewports: 1280x800 and 390x844 for the crawl; 320x900 for reflow re-measurement; additional 1280/390 contrast pass.

## Preconditions

| Precondition | Result | Evidence / consequence |
|---|---|---|
| Chromium + Playwright available | PASS | 140 route visits and 35 journey records completed |
| Session-private database | PASS | harness log `targets canonical: no`; shared `database/database.sqlite` untouched |
| Fresh production build | PASS | `npm run build` exit 0 before the walk; rebuilt after the fix |
| Clean worktree | **FAIL** | Shared tree carries peer edits (`composer.json`, ADRs, 2026-11-17 plans, two browser specs). No peer file was written; the fix touches only `resources/js/spa.ts` and its new spec |
| Visual/vision instrument | **FAIL** | `vision_analyze` returned HTTP 429 on four attempts including one retry after the indicated delay. Pure visual-craft judgement was therefore **not performed**; this pass detects measured copy, state, geometry, contrast, console and focus evidence only |
| Seeded populated data | PASS via creation | No run existed on the fresh scratch DB, so one throwaway run (run #1, Admire Vega) was created through the app's own six-step wizard and given 11 turns through its own write route. It is deliberately left in the scratch DB, not the dev file |

## Coverage numbers (derived from the recorded files)

| Measure | Count | Source |
|---|---|---|
| Crawled routes at 1280x800 | 70 | `walk-1280.jsonl` lines; all HTTP 200 |
| Crawled routes at 390x844 | 70 | `walk-390.jsonl` lines; all HTTP 200 |
| Document overflow != 0 | 0 | same files, `docOverflow` field |
| Console/page errors | 0 | same files, `errors` arrays |
| Pages with exactly one `h1` | 140 of 140 | same files, `h1` arrays |
| Journey records | 35 | `journey.jsonl` |
| Run-scoped routes visited | 17 | `journey.jsonl` `step: run-route` |
| Contrast readings | 20 (10 routes x 2 viewports) | `contrast.json`; zero failing pairs |
| Keyboard focus stops | 24 | `journey.jsonl` `step: keyboard-trail` |
| Screenshots shipped in this package | 35 | `screenshots-index.md` |
| Regression browser cases after fixes | 14 + 39 + 4 | ports 8402, 8404, 8405; all green |
| Primary sources fetched for the reference sheet | 28 | `05-references-2026.md` |
| Static candidates re-measured in Chromium | 6 confirmed, 2 cleared | `06-static-candidates.md`, `01-findings.md` |

The crawl saturated its 70-route cap on review-detail and support-card-detail links before reaching every route in `routes/web.php`. Career-workspace routes were therefore covered separately through the created run rather than by the BFS. Error documents (404/419/500), loading states and every variation of every detail page were **not** observed; they stay unmeasured, not passed.

## Observation method limits

This pass can detect state-dependent copy, count contradictions, computed contrast, geometry, console errors, empty states, focus movement and rendered-versus-document divergence. It cannot judge whether a populated screen *reads* well: the vision instrument failed and no human visual review was performed. A clean geometry and contrast section is not evidence that the visual design is good.
