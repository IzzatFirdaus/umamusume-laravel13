# Request: Verified Global mood-pill colours (three lower tiers)

**To:** Game Mechanics Agent
**From:** Pre-Dev / Documentation Agent
**Date:** 2026-09-28
**Blocks:** measured mood-pill colour tokens (design-research §3.6/§3.7 follow-up); nothing else. Neutral tints ship meanwhile.

## 1. Context and what is already settled

- The five mood tier **words** are measured client strings and are settled:
  `app/Enums/MoodTier.php` (GREAT/GOOD/NORMAL/BAD/AWFUL as verbatim `[Global]`
  values, commit `a7cabc0`). This request does **not** re-ask them.
- Pill **colours**: `docs/design-research/DESIGN.md` §3.7's evidence note records
  `mood-peak` and `mood-good` as measured; the three lower pills' colours are not.
- Owner ruling (2026-09-28, R-5 in the sprint authorization): UI must not present
  verified-looking colours for unverified tiers.

## 2. Exact ask

For each of the three lower tiers (`NORMAL`, `BAD`, `AWFUL`) of the client's Mood
panel pill:

1. The verbatim `[Global]` client colour as rendered: hex (or rgb), sampled from
   the pill fill, plus the pill's text colour if it differs.
2. Source: which asset or page it was read from (client screenshot with frame
   reference, or `docs/game-screenshots/` file name and pixel region), with
   fetch/verification date.
3. Confidence tier per the reference document's flag scheme (`[S]` official /
   `[A]` guide / `[B]` data export; `⚠️ STALE` if the source predates 90 days).
4. Divergence note if the `[JP]` client differs visibly; the Global value is what
   binds (Global-scope ruling, `docs/UMAMUSUME_REFERENCE.md` preamble).

## 3. Acceptance criteria

An answer is valid only if every colour carries: source reference, date, server
tag (`[Global]` or `[Both]`), confidence tier. A guess or community-table
approximation is **not** an answer; the correct response when nothing verifiable
exists is exactly:

```text
❌ UNVERIFIED: No current source found.
```

...which is itself useful: it locks neutral tints in as the durable design instead
of a placeholder awaiting drift.

## 4. Compliance clause for anything accepted

Every returned string or colour used in UI must pass, before `DESIGN.md`/token
adoption: C-4 lore gate (no equine framing in labels), R-02 (no em dashes in any <!-- lore-ignore-line class=1 cite=C-4 -->
accompanying copy), the `N/A` disclosure ruling, and the provenance requirement
(every fact stores source + fetched date; research D-285/D-286 apply: one
correction must propagate to every copy of the literal).

## 5. Interim rule (binding meanwhile)

```text
Use neutral tints for the three unverified mood/condition pills.
Do not present unverified colours as measured client values anywhere
(tokens, mockups, prototypes, or shipped CSS).
```
