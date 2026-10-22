# ADR-0021: Sourced character and support-card artwork, mirrored locally and derived from ids

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-10-05) — record the findings, then build the local mirror; the path is derived from the id at render time and never stored
  code: built — app/Services/DataPipeline/ArtworkMirror.php; SourceFetcher::fetchAsset(); tests/Feature/ArtworkMirrorTest.php; ArtworkSlot.vue + the loopback artwork.show route
  superseded-by: —
  amended-by: 0012 (its Decision 2's blocker is obsolete; see Erratum 4 there)
  open: which screens carry a frame is PRD.md OQ-6; the pre-run pick screen still carries no frame, and the reason recorded here is stale — its trainee picker is the TraineeCombobox component, not a native select (2026-10-22 framework check); skill icons have no stored column
-->

Status: **Accepted** (owner ruling 2026-10-05). The owner chose "A then C": record the findings, then build
the local mirror. Decisions 2 to 5 landed on 2026-10-05 as `uma:fetch-art`,
`App\Services\DataPipeline\ArtworkMirror` and `SourceFetcher::fetchAsset()`, with
`tests/Feature/ArtworkMirrorTest.php` proving them against `Http::fake`. **Both halves are built**: the
fetch half and the display half (`ArtworkSlot.vue` plus the loopback `artwork.show` route). Nothing here
changes `PRD.md` §6.13.

## Decision

**No uploads, and no stored URL that no fetch can satisfy.** The vendor's asset host resolves by id, keyed
by the **6-digit `card_id`** and never by `char_id`, and the base URL is a constant this repository owns
because no committed export carries an image field and no dump names the base path. The id is already
present in `gametora-characters` as the numeric prefix of `url_name`, so nothing new is fetched to reach
it.

**The path is derived at render time, not written to a column.** A column holding a URL is the
always-broken-image defect `ADR-0008`'s Provenance section was written to prevent, and it is why
`ADR-0012` Decision 2's reasoning survives even though its factual premise does not.

**A miss is never detected by "bytes came back".** The asset host answers a miss with **27,150 bytes of
`text/html` at HTTP 404**, so every check gates on status. `SourceFetcher::fetchAsset()` returns null on any
non-2xx, and `ArtworkMirrorTest` keeps it that way: a build that stored the body of a 404 would put an HTML
document in place of a PNG and render it as a broken frame forever.

**Nothing enters the database and no `data_sources` row is written.** Files land under gitignored
`storage/app/private/artwork/` with a sibling `manifest.json` carrying url, sha256 and size. They reach the
browser through the loopback `artwork.show` route and never from the asset host.

**`uma:fetch-art` is manual.** Nothing schedules it, and an unrun mirror renders nothing at all, so a fresh
worktree shows no frames and no error — `ArtworkSlot.vue` renders nothing for a null url. On this tree the
first pass resolved 665 ids with zero unresolved.

**The legacy repo supplies nothing.** Its image column is a fanart upload keyed by slug, gitignored, and
its app contains zero outbound HTTP calls. It is the feature `PRD.md` §6.13 cut, and the cut was sound.

## What this does not license

- **No upload surface.** `PRD.md` §6.13 ("No trainee image uploads") is untouched, and this ADR does not
  narrow it. Art the tool fetches itself by id from an allowlisted host is a different object.
- **No new host.** `media.gametora.com` rides the same `config('uma.sources')` allowlist as Fetch; it is not
  a second one. `uma:fetch` steps over any source entry that declares no parser, which is how the asset
  host stays allowlisted without being parsed.
- **No assumption that a figure typed here is current.** The id count and the bytes on disk move with the
  catalogue; read them from `uma:fetch-art --dry-run` and the manifest, never from this file.

## Consequences

- The pre-run pick screen cannot host a frame while its trainee picker is a native `<select>`, and skill
  icons have no stored column. Both stay open under `PRD.md` OQ-6.
- `DESIGN.md` §4.7 owns slot behaviour and how an absent file renders; §6 owns alt text inside C-4.
- An empty artwork mirror is **invisible, not obviously broken**: no frames and no error. Check
  `uma:fetch-art --dry-run` before reading missing images as a frontend defect.

## Links

- `PRD.md` §6.13, FR-A, FR-B, OQ-6; `ARCHITECTURE.md` §6 and §8; `DESIGN.md` §4.7, §6, §11
- `ADR-0012` Decision 2 (the blocker this obsoletes), `ADR-0008` (the always-broken-URL reasoning)
- `app/Services/DataPipeline/ArtworkMirror.php`, `SourceFetcher::fetchAsset()`,
  `tests/Feature/ArtworkMirrorTest.php`, `resources/js/components/ArtworkSlot.vue`
- `config/uma.php` (the allowlist); `php artisan uma:fetch-art --dry-run` (the live id count)
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules TOG-3, NRM-1,
  IA-5

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — no rule is broken, and one premise in
  the open item is stale.** The rules that touch this decision are **TOG-3** (status may be stale or
  absent, but never wrong), **NRM-1** (every available action carries a visible signifier) and **IA-5**
  (an empty state shows an example of what would fill it). On the first two the decision passes: the
  mirror's emptiness is an absence, not a false claim, and the slot's own space is reserved by
  `ArtworkSlot`'s `reserve` prop rather than silently collapsing. **The premise that needed correcting
  is in this ADR's own open item.** It reads "the pre-run pick screen cannot host a frame while its
  trainee picker is a native `<select>`", and the pick screen — `resources/js/pages/Runs/Create.vue`,
  the "New career" form — mounts `TraineeCombobox`, not a native select; the component is a combobox
  with its own listbox and its own key handling. **The conclusion still holds and the reason has
  changed:** the screen carries no frame, because neither the combobox nor the form renders one. The
  reason mattered because it pointed at the next piece of work — a control type that could not host a
  frame — and that work is no longer what blocks it.
- 2026-10-05 · **Erratum 1 — the live pass has been run, and the falsifier named in the first draft did not
  fire.** The status asserted "No pass has been run against the live host, so the mirror is empty on this
  tree". **665 files, 45 MB**, in `storage/app/private/artwork/` with a sibling `manifest.json`. What the
  run settles: the asset host answers every id the catalog holds, so the `ADR-0012` Erratum 4 falsifier (a
  media source gaining a `card_id` key) never fires and is not what opened this. The byte figure is a dated
  measurement and is not restated here; `uma:fetch-art --dry-run` is the pointer.
- 2026-10-05 · **first-draft byte estimate corrected.** The decision's own "roughly **51 MB**" was an
  estimate against the seeded tree (106 portraits at 256 px plus 559 support thumbnails); the run measured
  45 MB. Kept because the estimate is the shape of the error: a figure typed into an ADR rots the day the
  catalogue moves.
- 2026-10-06 · **the display half is built**, and the status that said it was open was corrected:
  `ArtworkSlot.vue` renders `card_portrait` on the catalog index and the trainee detail and `support_thumb`
  on the support-card index and detail, with the deck picker carrying a thumb as a sixth surface.
