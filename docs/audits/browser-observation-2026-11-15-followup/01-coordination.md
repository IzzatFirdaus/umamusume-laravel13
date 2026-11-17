# 01 — Coordination

## The question, and the answer

**Asked (Task B).** A peer edited `resources/css/app.css` (+18/−1, seven `--color-stat-*-ink` tokens, "stat-colour
Slice A") at **01:19:56** on 2026-11-15, edited `tests/Feature/DesignTokensTest.php`, and rewrote
`public/build/manifest.json` at **01:26:56** — all *after* the 2026-11-15 walk finished at 01:04:54.

**Answer, verbatim from the owner:**

> **(a) Independent work — it happened to land during the walk (⇒ KI-70 for the second time in one day)**

## Consequence: KI-70 recorded a second time, not a peer-remediation overlap

The owner's answer is (a), so per the dispatch the outcome is a **KI-70 recurrence record**, not a
Peer-remediation overlap row. The dispatch offered one or the other; the answer selects the first.

**KI-70 recurrence, 2026-11-15.** The 2026-11-14 pass recorded a live peer `playwright test`, a second worktree, and
`test-results/` churning throughout (EX-07). The 2026-11-15 pass recorded a peer `npm run build` landing 22 minutes
after its last capture, rewriting the shared manifest and adding seven CSS tokens. Two independent passes, two
independent collisions, one day apart, both in the same `resources/css` / `public/build` surface every browser-based
pass must read.

**What this costs.** A build-manifest rewrite is not a cosmetic collision: `public/build/manifest.json` is the single
file every browser gate in this repository reads to resolve asset hashes. A pass that measures contrast before a
rebuild and reports after one describes a build that no longer exists — and nothing in the output signals the
substitution. This is exactly why the owner has now ruled on the quiet-tree rule (A for full audits, B for narrow
follow-ups); the recurrence is the evidence that motivated the ruling.

**What it did *not* cost, here.** Task C's question was "did stat text colours change?" and the answer is no, in both
themes, on all five surfaces — so the collision cannot have moved a conclusion this pass drew. It did force the
re-measurement to be run at all, which is the point.

## One thing worth flagging to whoever runs the next full audit

The peer's tokens are **declared, built, resolving as CSS variables, and wired to nothing** (Task C). So the collision
produced no observable change. That is luck, not safety: the same collision with a wired token would have silently
invalidated the prior pass's contrast rows on five screens. The signed-window rule is not bureaucratic overhead; it is
the only thing that makes a colour claim attributable to a build.

## Live confirmation, while this follow-up was being written

The collision is not historical. Measured at the end of this pass:

| File | Last write | Added lines | Note |
|---|---|---|---|
| `resources/css/app.css` | 01:28:15 | **40** | was 18 when the 2026-11-15 pass first looked, 18 when this pass started |
| `tests/Feature/DesignTokensTest.php` | 01:30:39 | 32 | |
| `AGENTS.md` | 01:32:13 | 4 | |
| `DESIGN.md` | 01:32:30 | 11 | |

`public/build/manifest.json` is **01:29:07** — *older* than the latest CSS write, so the peer has edited CSS after
the most recent build. The build that served my Task C captures was **01:26:56**; the newer CSS is not in it.

**This is precisely the case the timestamped-evidence rule exists for, and it worked.** Task C's claims are pinned to
a named build, so they stay true of the build they describe even while the disk moves underneath them. Under a
"quiet tree or do not walk" reading of the old precondition, this pass would have had to stop at the first sample and
produce nothing — which is worse, because the four documentation fixes are the durable output and they need no build
at all.

**One concrete risk this raises for the next pass, and one correction to my own first read.** The `app.css` diff
carries **21** `-ink` lines, which briefly looked like the dispatch's "seven tokens" was wrong. It is not: those 21
lines are **7 distinct names declared across 3 theme blocks** (light, dark, eva01). The dispatch's seven is correct.

What matters is that the two themes *resolve different values* — measured: light `speed #166eaf / stamina #ad3e3b /
power #a15b10`, dark `speed #2f94ee / stamina #f05b4f / power #f7820c`. So when the peer wires them, the Result/dark
pair current at `4.83:1` is the first thing to re-measure, because the dark ink values are tuned for the tinted panel
(`#2A2A2A`), not for the `#333333` result surface. Recorded at `02-stat-ink-remeasure.md` §"the one pair worth
watching".

## Second live change, and one that touches the data

Between the Task C measurement and this write-up, the **dev database lost a run**: it held 19 training rows after the
EX-12b seeding and now holds **18** (ids 14–31). The missing row is **#13** — the throwaway Unity Cup run created in
the 2026-11-14 pass and used in the 2026-11-15 pass as the empty-state case for **BO-002**.

**What this costs.** BO-002's evidence is unaffected — its screenshots are captured, its verdict (empty-state-only,
Medium, open) is settled and this pass does not re-litigate it. But the *live reproduction case is gone*: a future pass
that wants to re-verify BO-002 must first create a fresh zero-turn run, because `:8000` no longer has one. That is worth
knowing before someone opens `/training-runs/13/cockpit` and gets a 404, concludes "BO-002 no longer reproduces", and
closes it.

I did not delete it. My pre-seed backup (`.scratch-uma/browser-obs-followup/dev-db-pre-seed-backup.sqlite`) still
contains run #13 and can restore it.

**This is the third live change during this follow-up** (CSS tokens → build → new component → run deletion), all by a
peer session, none announced. It is no longer an incident; it is the working conditions, and the timestamped-evidence
ruling is the only thing that keeps any claim attributable.
