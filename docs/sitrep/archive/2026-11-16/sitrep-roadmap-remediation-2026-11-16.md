# SITREP — Trainer Desk v3.0.0 audit remediation

**Written:** 2026-11-16 19:44 · **Session:** `hermes-roadmap-20261116`
**Baseline at session start:** HEAD `178a9079` (2 ahead / 0 behind `origin/master`)
**State at SITREP:** HEAD `ae26b799`, **0 ahead / 0 behind** `origin/master`
**Verdict:** P0–P5 executed. P3 is green with a committed-equivalent working tree awaiting owner commit. P6 not opened (gated on P3.8's clean sweep, which passed — but no walk was driven). P7 measured, tag not cut (owner's act).

---

## 1. Mission recap

| Phase | Attempted | Completed |
| :--- | :--- | :--- |
| P0 baseline | yes | **yes** — §0 of both docs corrected to the measured tree |
| P1 attribution | yes | **yes** — P1.1 closed by peer commits; P1.2 measurement-only; P1.3 dispositions recorded, 4 losses restored |
| P2 dependencies | yes | **yes** — 9 advisories classified not-reachable; no lock move; npm 0 vulnerabilities |
| P3 suite truth | yes | **yes** — P3.1/P3.4 closed (P3.1 by peer, P3.4 by me), P3.2/P3.3/P3.5/P3.6/P3.7 fixed, P3.8 swept green |
| P4 register | yes | **partly** — P4.1 reconciled; P4.2–P4.4 deferred (peer owns `KNOWN-ISSUES.md`); P4.5 executed by peer as deletion |
| P5 features | yes | **yes** — every gate resolved on its default branch and recorded |
| P6 walks | no | **no** — not opened |
| P7 release | yes | **partly** — P7.1 conditions measured; P7.2/P7.3 not written (owner-scoped) |

---

## 2. Checklist status per slice

| Slice | Status | Evidence |
| :--- | :--- | :--- |
| P0.1 | **verified** | roadmap §0 + plan P0.1 block corrected to 206 rows / 24 staged / HEAD `178a9079`; second correction re-applied at `ae26b799` |
| P1.1 | **verified (already landed)** | `f8e428d9` (17) + `78b54a71` (16) + `178a9079` (15) are all ancestors; 2 files remained dirty, now clean |
| P1.1 Playwright gap | **verified, 1 classified red** | `career-cockpit-forms.spec.ts` 13 passed / 1 failed at `:248`; red = P3.3 label split, pre-existing, not a form regression |
| P1.2 | **verified, no commit needed** | `:531 #FF6F3B`; guard at `DesignTokensTest.php:841`; build emits `--color-risk:#ff6f3b`; `17 passed (480 assertions)` |
| P1.3 | **verified** | 113 D / 37 ?? classified; 4 *loss* rows restored; 18 move pairs proven; 90 `jev-inspect` peer removals recorded |
| P2.1 | **verified** | 9 advisories / 6 packages, each not-reachable with the grep that proves it |
| P2.2 | **ruled: no move** | zero reachable CVEs; blanket update = risk with no gain; owner-overridable |
| P2.3 | **verified** | `npm audit --omit=dev` → 0 vulnerabilities across prod 79 deps |
| P3.1 | **verified (peer `e73ddfe0`)** | specs on `role=button`; component `min-h-11` = 44px ≥ SC 2.5.8's 24px |
| P3.2 | **committed-equivalent, green** | slug in 4 request paths + 2 controllers fixed at root |
| P3.3 | **committed-equivalent, green** | one direction `(combined)` in 3 files, config untouched |
| P3.4 | **committed-equivalent, green** | `EventDecision.vue` 444 CRLF → 0; spec 374 → 0 |
| P3.5 | **committed-equivalent, green** | `config('nativephp.secret')`; PHPStan OK |
| P3.6 | **verified, gate holds** | ratcheted `arbitrary-value` = **207** vs baseline **209** → 2 under, exit 0 |
| P3.7 | **verified** | new `veterans-transfer-requests.spec.ts`, **1 passed (27.0s)** |
| P3.8 | **verified** | **2435 passed, 1 risky, 0 failures, 37402 assertions, exit 0** (493.01s); the risky one then identified as `DocAccuracyTest` and fixed → `3 passed (86 assertions)` |
| P4.1 | **verified** | 39 OPEN status-line (worktree) vs 34 (HEAD) vs 53 body-read census, each with its method |
| P4.2–P4.4 | **deferred, recorded** | `KNOWN-ISSUES.md` peer-modified; register writes not this session's to land |
| P4.5 | **executed by peer** | `302c6e6b` "remove four orphaned cockpit panel components" |
| P5.1 | **ruled (b)** | FR-E stops at six |
| P5.2 | **ruled (deferral)** | NFR-19 terminal deferral |
| P5.3 | **premise false, recorded** | `QWEN.md` does not exist |
| P5.4 | **verified** | docker ABSENT; uploads cut; no schema file |
| P5.5 | **ruled (keep)** | 4.15:1 dark pill retained, rationale recorded |
| P6.1–P6.2 | **not started** | gated on P3.8, which passed; no walk driven |
| P7.1 | **measured** | see §6 |
| P7.2 | **not written** | owner-scoped |
| P7.3 | **not cut** | owner's act |

---

## 3. Commits landed

**By this session: none.** Every change is in the working tree, uncommitted, because no turn authorized committing specific paths (`AGENTS.md` §3 law 3, execution contract §4).

**By peers during the session (all now on `origin/master`):**

| sha | message |
| :--- | :--- |
| `f8e428d9` | feat(forms): adopt MWG form-capture hints across the 17 audited files |
| `78b54a71` | feat(forms): adopt MWG form-capture hints across 16 audited files |
| `0d046532` | chore(ci): record packaged main-process output and probe native:config startup |
| `c41abbb8` | docs(plans): mark phase status on the roadmap to prevent re-execution |
| `178a9079` | feat(forms): adopt MWG form-capture hints across 15 audited files |
| `e73ddfe0` | test(browser): retarget provenance marks to the button shape |
| `8d9172d2` | fix(nativephp): resolve packaged build path and restore the desktop provider |
| `302c6e6b` | chore(ui): remove four orphaned cockpit panel components |
| `ae26b799` | fix(test): find the packaged app's port by listening socket, not HTTP burst |

---

## 4. Files created / modified (all uncommitted, working tree)

**Modified — product code (2):**
- `app/Http/Controllers/Career/TraineeSelectController.php` — `profileUrl` resolves the draft id to its slug
- `app/Http/Controllers/Career/BuildTargetController.php` — `backUrl()` resolves id→slug, falls back to roster; docblock corrected; `Umamusume` imported

**Modified — config/middleware (2):**
- `app/Http/Middleware/EnforceNativePHPSecret.php` — `env()` → `config('nativephp.secret')`
- `config/nativephp.php` — `secret` key added (**+6 lines**; the file became tracked mid-session via peer `8d9172d2`, and its two existing `secret` hits are `AWS_SECRET_ACCESS_KEY`/`DO_SPACES_SECRET_ACCESS_KEY`, not this key — re-measured with `git diff`)

**Modified — tests (5):**
- `tests/Feature/CareerTraineeSelectTest.php` — 4 id→slug; CRLF normalised
- `tests/Feature/TraineeCardFormTest.php` — 1 id→slug; CRLF normalised
- `tests/Feature/CareerScenarioSelectTest.php` — `Spirit Bursts (combined)`
- `tests/browser/career-cockpit-forms.spec.ts` — `(combined)` at `:248`; whole-file CRLF→LF
- `tests/browser/scenario-panel.spec.ts` — `(combined)` regex

**Modified — source normalisation (1):**
- `resources/js/pages/Career/EventDecision.vue` — whole-file CRLF→LF (444 → 0)

**Created — test (1):**
- `tests/browser/veterans-transfer-requests.spec.ts`

**Modified — docs (2):**
- `docs/roadmap/implementation-roadmap-2026-11-16.md` — §0 corrected to the measured tree + second drift note
- `docs/plan/implementation-plan-2026-11-16.md` — P0.1 expect-block replaced with measured block

**Created — evidence only (does not belong in a commit):**
- `.scratch-uma/hermes-roadmap-20261116/{p13-dispositions,p2-advisories,p4-register,p5-rulings}.md`

---

## 5. Git state

```
HEAD                 ae26b79987677ca13a0a29751a568164764a67cc
origin/master...HEAD 0   0        (equal)
worktree rows        181
histogram            108 " D" / 35 " M" / 38 " ??"
staged index         empty
stashes              6
```

The 108 ` D` are 90 `jev-inspect/**` + 18 `docs/audits|research-scratch` moves (replacements untracked). Not this session's to stage.

---

## 6. Tests and outcomes

| Command | Summary | Exit |
| :--- | :--- | :--- |
| `vendor/bin/pest --filter='CareerScenarioSelectTest\|CareerTraineeSelectTest\|TraineeCardFormTest\|LineEndingGuard' --compact` (before fixes) | 6 failed, 41 passed (727 assertions) | 1 |
| same, after P3.2/P3.3/P3.4 fixes | **47 passed (819 assertions)** | **0** |
| `vendor/bin/pest --compact` (P3.8 full sweep) | **2435 passed, 1 risky, 0 failed (37402 assertions)**, 493.01s | **0** |
| `vendor/bin/pest --fail-on-risky --compact` (after the DocAccuracy fix) | **2436 passed, 0 risky, 0 failed (37419 assertions)**, 327.13s | **0** |
| `vendor/bin/pest --filter=DesignTokensTest --compact` (P1.2) | 17 passed (480 assertions) | 0 |
| `npx playwright test tests/browser/career-cockpit-forms.spec.ts --workers=1` (port 8191) | 13 passed, 1 failed at `:248` — P3.3 label split, pre-existing | 1 |
| `npx playwright test tests/browser/veterans-transfer-requests.spec.ts --workers=1` (port 8192) | **1 passed (27.0s)** | **0** |
| `npm run typecheck` | `vue-tsgo -p tsconfig.vue.json && tsc --noEmit`, no output | 0 |
| `npm run build` | 970 modules, 19.08s | 0 |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` (whole tree) | `[OK] No errors` | 0 |
| `vendor/bin/pint --test --format agent app database tests config` | `{"result":"passed"}` | 0 |
| `php artisan migrate:status` | 81 migrations, all `Ran`, none pending | 0 |
| `npm run lint:ui` | 516 findings reported; **ratcheted `arbitrary-value` = 207 vs baseline 209** | **0** |
| `php tools/lore.php` | **lore-docs: 580 hit(s), 75 exempt** | 0 |
| `composer audit --format=plain` | 9 advisories / 6 packages | 1 |
| `npm audit --omit=dev --json` | 0 vulnerabilities | 0 |

**The 1 risky test — identified and fixed.** `--stop-on-risky` halted at
`Tests\Feature\DocAccuracyTest > it pins every file path named in AGENTS.md §6 repository structure block`
(`! ... did not perform any assertions`).

Root cause, measured: the test's regex required a language-tagged fence
(``## 6\. Repository structure\s*?\r?\n```text``) but `AGENTS.md:125` opens a **bare** fence (` ``` `).
`preg_match` therefore matched nothing, `$structure` was `''`, the loop iterated zero times and
**zero `expect()` calls ran** — a gate that passed by reading nothing. `git blame` shows the assertion-less
shape predates this session (`828ead2eb` 2026-10-17, `0a093abbc` 2026-10-19); `AGENTS.md` is peer-modified
(`M`) so the fence was not touched.

Fix in `tests/Feature/DocAccuracyTest.php` (the only file changed): fence pattern relaxed to
`` ```(?:text)? ``, a per-path failure message added, and a floor asserted —
`expect($checked)->toBeGreaterThan(10, ...)` — so the sweep can never again pass on zero rows.

Measured after: `vendor/bin/pest --filter=DocAccuracyTest --compact` → **3 passed (86 assertions)**, exit 0
(was `1 risky (0 assertions)`). It now checks **16** paths, all of which exist.

---

## 7. Owner gates resolved (default branch applied)

| Gate | Default applied | Rationale | Owner may override |
| :--- | :--- | :--- | :--- |
| P1.1 | **B** (16, park `CardPicker.vue`) | peer-modified file never rides a commit it does not own | — (superseded: peer landed all 17 in `f8e428d9`) |
| P1.3 | move / peer-removal / **loss→restore** | 18 moves proven on disk; 90 foreign-tree rows untouched; 4 losses restored | yes |
| P2.2 | **no lock move** | no reachable CVE; blanket update is risk without gain | yes — command named in `p2-advisories.md` |
| P3.1 | **(a)** keep button, ≥24px | peer's `e73ddfe0` already retargeted specs; `min-h-11` = 44px | — |
| P3.3 | **follow config `(combined)`** | config is product data; specs are the claim | yes |
| P4.5 | **compose, do not delete** | KI-70 hazard | — (peer deleted in `302c6e6b`; recorded) |
| P5.1 | **(b)** FR-E stops at six | `ARCHITECTURE.md:313-316` documents six | yes |
| P5.2 | **deferral confirmed terminal** | building it adds a surface with no product intent | yes |
| P5.3 | **correct only what the tree falsifies** — and it falsifies the premise | `QWEN.md` absent; nothing to correct | yes |
| P5.5 | **keep 4.15:1** | shared token; raising it moves four other surfaces | yes |
| P7.3 | **prepare, do not cut** | tag is the owner's act | — |

---

## 8. Owner gates presented (stop conditions)

**None triggered.** No destructive action was taken outside the default-branch table; no acceptance criterion proved unreachable; no gate lacked a defensible default; no peer landing invalidated a slice's premise in a way the record did not absorb.

One item is **presented, not resolved**: P7.3's tag request (below), which the roadmap defines as the owner's decision by design.

---

## 9. Verified facts vs assumptions

**Verified by command (output quoted above):** HEAD/origin relation; worktree histogram; staged emptiness; 6 stashes; all cited shas' ancestry; 17-file MWG landings; `#FF6F3B` at `:531`; guard enumeration at `:841`; build emission of `#ff6f3b`; the 47-pass focused run; the 2435-pass full sweep; the 1-pass browser spec; typecheck; build; PHPStan whole-tree; pint scoped; 81 migrations Ran; lint:ui 207 vs 209; lore 580/75; both audits; `QWEN.md` absence; docker absence; `routes/api.php` six endpoints; the four orphan panels (before `302c6e6b`).

**Inferred, never used to satisfy an acceptance line:**
- ~~The identity of the 1 risky test.~~ **RESOLVED**: `DocAccuracyTest`'s AGENTS.md §6 sweep; see §6.
- That every one of the 112 ` D` rows is peer-or-move rather than loss — I classified all 113 as measured (90 jev + 18 move + 4 restored + 1 staged-deletion restored), so no row is unnamed, but I did not open each jev file to confirm it is foreign.
- The 516-vs-209 lint figure: I verified the *ratcheted* rule is 207, and that the gate exits 0; I did not audit whether the other 309 non-ratcheted findings are new.

---

## 10. Outstanding work, in execution order

1. **Commit the working tree** — 10 code/test files + 2 docs, explicit pathspecs, private index, message naming P3.2/P3.3/P3.4/P3.5/P3.7. Nothing is staged; nothing was pushed.
2. **P3.8 risky identification** — `vendor/bin/pest --compact --display-warnings` on a quiet box, or `--filter` walks, to name the risky test.
3. **P4.2–P4.4** — after the owner of `KNOWN-ISSUES.md` lands their edit: 23 CODE_FIX entries (KI-95's `profileUrl` class is now fixed and wants its register write), 8 OWNER_RULING requests, 10 stale/duplicate closures.
4. **P6.1/P6.2** — Trackblazer walk, then Grand Concert, then URA as control. Not started; P3.8 no longer blocks.
5. **P7.2** — `VERSION.md`/`CHANGELOG.md` for the phase exits landed.
6. **P7.3** — cut `3.0.0`, or record why it waits.

---

## 11. Deviations from the roadmap

| Deviation | Justification |
| :--- | :--- |
| P1.1 executed no commit | the peer landed all 17 MWG files in `f8e428d9`/`78b54a71`/`178a9079` while the session ran; committing would have been an empty or revert commit |
| P1.2 executed no commit | the peer's `ad4b09cc` already carried `#FF6F3B`; measurement proved the guard and the build |
| P2.2 moved no lock | measurement showed zero reachable advisories; the roadmap's own rule is "no lock on an unverified number" |
| P4.2–P4.4 not executed | `KNOWN-ISSUES.md` is peer-modified; register writes in a peer's live edit is a sweep, which law 6 forbids |
| P4.5 not executed by me | the peer executed it as deletion (`302c6e6b`) — the opposite of the default branch; recorded, not reverted |
| P5.3 wrote no `QWEN.md` | the file does not exist; correcting a non-existent file would be fabrication |
| P6 not opened | the sweep passed, so the gate opened, but the session ended before a walk was driven — recorded as not started rather than claimed |
| Extra work not named by a slice | `TraineeSelectController`/`BuildTargetController` slug fix was not in P3.2's file list. It is the *cause* of P3.2's red, found by tracing the 404 to its producer; fixing the tests alone would have left the app's own profile link broken. Recorded as a scope extension with its reason |

---

## 12. Known risks and blockers

- **Peer drift is continuous.** HEAD moved 6 times during the session (`178a9079` → `ae26b799`), including two commits that changed work this session had measured (`e73ddfe0` closed P3.1; `302c6e6b` deleted the four panels). Every figure above carries its HEAD.
- **`KNOWN-ISSUES.md` is peer-owned and uncommitted.** Two sessions cannot both write it; P4 is blocked on that fact, not on effort.
- **`public/build` is gitignored and rebuilt.** Any browser-token reading before `npm run build` is stale; this session's build is current at `ae26b799`.
- **One risky PHP test was identified and fixed** (`DocAccuracyTest`): it asserted nothing because its fence regex demanded ```text over a bare fence. Now `3 passed (86 assertions)` with a `>10` floor so it cannot pass on zero rows again. `--fail-on-risky` is now green tree-wide, so this class of defect fails the suite instead of warning.
- **`config/nativephp.php` became tracked mid-session** (`8d9172d2`). The file is a peer's; this session's contribution to it is a 6-line `secret` key addition, verified as genuinely absent from HEAD by re-reading `git show HEAD:config/nativephp.php | grep secret` (2 hits, both unrelated cloud credentials). A commit must take the whole file, i.e. the peer's 200 lines plus my 6.
- **A peer's test appeared** — `tests/browser/stat-visuals.spec.ts` plus `ui/Sparkline.vue`, `ui/StatRing.vue`, `polyfills.js` are untracked peer work; not touched.

---

## 13. First command next session

```bash
git rev-parse HEAD && git status --porcelain | cut -c1-2 | sort | uniq -c
```

Then, if the working tree still holds the ten code/test files and two docs:

```bash
npm run typecheck && npm run build    # the browser gate reads public/build
vendor/bin/pest --filter='CareerScenarioSelectTest|CareerTraineeSelectTest|TraineeCardFormTest|LineEndingGuard' --compact
```

Then commit those explicit paths through the private index, and only then open P4.2 on a `KNOWN-ISSUES.md` the peer has finished with.

---

# POST-SITREP UPDATE — owner authorization executed (2026-11-16 22:1x)

The owner named the paths and authorized the commits. This section supersedes the "0 commits" and
"unclassified risky" statements above; everything else in the SITREP stands.

## Commits landed

| sha | message | files | private-index verified |
| :--- | :--- | :--- | :--- |
| `50e31d14` | `fix(career): resolve slug at source for trainee profile routes and retire stale test fences (P3.2-P3.7)` | **11** | `git show --name-only` lists exactly the 11 authorized paths; no peer file |
| `aefd6549` | `docs(plans): correct §0 to measured tree at 50e31d14` | **2** | the two docs only |

`aefd6549`'s message names `50e31d14`, not `aefd6549`, by design: the roadmap's §0 describes the tree it was
measured on, and HEAD moved to `50e31d14` between the two commits. Naming the parent is the true claim; naming
the child would have required amending a protected sha.

Both are ancestors of HEAD `2be83336` (`git merge-base --is-ancestor` → ANCESTOR for both).

## Deviation from the authorization: one path removed

`resources/js/pages/Career/EventDecision.vue` was authorized but **not committed**, on measurement:
`git hash-object` of the worktree equals `HEAD`'s blob `8b006262`, and that blob already carries **0** `\r\n`.
The committed content was already LF; the CRLF repair was a *working-tree* fix that the guard reads, with nothing
to land in history. Committing it would have produced an empty-content entry. The file remains untouched on disk.

Commit A therefore carries **11** paths, not the 12 the authorization listed.

## Checks 5a / 5b / 5c — all three resolved

**5a — P1.3 restore provenance.** Only one of the four restored paths has a `D` commit: `SKILL.md`, deleted by
`8e30714e` ("rename SKILL.md to SKILL_REGISTRY.md", 2026-10-20). That commit is **superseded**: `2f0b34b6`
("add root index of local and global agent skills") re-added `SKILL.md`, and the two are different files —
`8e30714e`'s rename produced `SKILL_REGISTRY.md`, which `59340099` later removed entirely. HEAD contains
`SKILL.md`, which is what the restore produced. `git cat-file -e HEAD:SKILL.md` → present.
The other three (`.kilo/kilo.jsonc`, `tools/check-electron-env.php`, `tools/hermes-preflight.sh`) have **no `D`
commit** at any ref, so their deletions were working-tree accidents and the restore is correct.
`git diff HEAD` for all four is now empty: **the restore reproduces HEAD exactly** and lands nothing in any commit.

**5b — P5.3 `QWEN.md`.** `git log --all --oneline -- QWEN.md` → **empty**. The file was never tracked on any ref,
and no `QWEN*` exists on disk. The roadmap's "untracked at 15:45" reading was of a file that has since been
removed from the working tree, not a peer deletion from history. The SITREP's conclusion stands: **there is
nothing to correct**, and no restore is warranted.

**5c — `config/nativephp.php`.** Tracked; added by peer `8d9172d2`. HEAD's two `secret` hits are
`AWS_SECRET_ACCESS_KEY` and `DO_SPACES_SECRET_ACCESS_KEY` — unrelated. This session's contribution is the
**6-line** `'secret' => env('NATIVEPHP_SECRET')` block, confirmed by `git diff --stat` = `6 ++++++`. Committed
whole, as authorized, and `git show --name-only 50e31d14` confirms no other peer file rode along.

## Verification on the committed tree

| Command | Summary | Exit |
| :--- | :--- | :--- |
| `npm run typecheck` | `vue-tsgo -p tsconfig.vue.json && tsc --noEmit`, no output | 0 |
| `npm run build` | 970 modules, 20.37s | 0 |
| `vendor/bin/pest --filter='CareerScenarioSelectTest\|CareerTraineeSelectTest\|TraineeCardFormTest\|LineEndingGuard\|DocAccuracyTest' --fail-on-risky --compact` | **50 passed (905 assertions)**, 10.27s | **0** |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` | 0 |

## Index hygiene incident — noted, not hidden

After the two commits, `git diff --cached` reported **13 staged paths showing my commits inverted** (including
`D` for the two docs and the new spec). Cause: `.git/index` on disk was last written at **20:35**, two hours
before the 22:05 commits — the `GIT_INDEX_FILE` environment mapping did isolate the real index, as intended, but
HEAD then moved ahead of a stale index, so the stale entries read as reversions.

Resolved safely with `git read-tree HEAD` (index ← HEAD, working tree untouched; **not** `reset --hard`).
Result: staged 0, working tree intact, all committed files clean, HEAD unchanged at the time.

**Consequence for the next session:** a naive `git commit -a` between my commits and any `read-tree` would have
reverted the session's work. Anyone operating this tree should run
`git diff --cached --stat` before committing and treat "staged paths that equal my own recent commits, inverted"
as a stale-index signal rather than as work to commit.

## Row-count acceptance, corrected

The authorization predicted 181 → 167 rows (a drop of 14). Measured after both commits and the index refresh:
**171 rows** — `108 D` / `25 M` / `38 ??`. The prediction did not hold because (a) one authorized path was a
no-op and (b) peers committed their own work during the session, clearing rows that were in the 181. The figure
is recorded as measured, not reconciled to the estimate.

## Still deferred, with entry conditions

| Slice | State | Entry condition |
| :--- | :--- | :--- |
| P4.2–P4.4 | blocked | owner of `KNOWN-ISSUES.md` lands their edit; 23 CODE_FIX entries queued, KI-95's `profileUrl` class already fixed by `50e31d14` and wanting its register write |
| P4.5 | **done by peer** | `302c6e6b` deleted the four orphan panels; whether a KI entry accompanied it is unconfirmed — `grep -A6 'KI-' KNOWN-ISSUES.md | grep -i 'panel\|orphan\|302c6e6b'` |
| P6.1–P6.2 | not opened | signed quiet window (owner-named start/end, peers committed to no writes) |
| P7.2 | not written | owner's release-notes content decision |
| P7.3 | not cut | owner's act; P7.1's evidence is quoted in §6 above |

## Nothing was pushed

`origin/master...HEAD` reads `0 6`: six commits local (four peer, two mine). No `git push` was run, per the
standing instruction.
