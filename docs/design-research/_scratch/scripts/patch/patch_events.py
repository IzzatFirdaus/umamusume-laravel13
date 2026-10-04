import pathlib

p = pathlib.Path("docs/design-research/DESIGN.md")
s = p.read_text(encoding="utf-8")

NEW = """## 8.5 Dynamic event resolution

A turn is not always "pick a training". Four things can happen, and the UI must present each as itself rather than as another form variant. The classification below is the design's own; the sourced backing for each row is stated, and where the source is thin the design stays generic on purpose.

| Flow | Trigger | What the screen does | Source backing |
|---|---|---|---|
| **F1 Standard turn** | No event fired | The five banner options, preview, confirm | `UMAMUSUME_REFERENCE.md` §1.1.2 |
| **F2 Character event** | Round-triggered or random, specific to the trainee | Event panel slides in over the scene: narrative, then A/B/C choices each with previewed deltas | `training_events__char.json`, 135 chains (§1.4.4) |
| **F3 Support card event** | Trainer occupied a tile with a card on it | Same panel shape; the header names the card and the outcome list adds Bond and hint effects | `training_events__friend.json` (11 Pal), `training_events__group.json` (5 Group) (§1.4.4) |
| **F4 Scenario event** | Mandatory, scripted by the scenario | A single-choice panel, or a multi-step scenario sheet when the event genuinely has parts | §1.1.2, §1.6.1, described qualitatively only |

**Random world events are not modelled as a fifth flow.** No in-run dataset in the reference supports a category distinct from the three chains above, and §3.4 "recurring event types" is live-ops content on a different axis entirely. If such events exist they land in F2 or F4, and the `TurnEvent` row carries a free-text origin so the data does not lie about a taxonomy it does not have.

### 8.6 The event panel

F2 and F3 share one component, and it is the client's own object (`Screenshot 2026-07-14 124128.png`, `124926.png`), not a web modal:

- **The scene stays.** The panel is an overlay on the left region; the training scene, the turn chip and the stat band all remain visible behind it. A scrim that blanks the page destroys what the client is careful to preserve, which is the Trainer's sense of where in the run they are.
- **Tag ribbon, not a title bar.** A cyan parallelogram tag names the origin (`Support Card Event`, `Character Event`), and a wider blue gradient ribbon below it carries the event title in white bold. Both are slanted at one end.
- **Narrative block** in `body` at generous line height, with the speaker named above it.
- **Choices are §6.1 banner buttons**, never radio inputs, and each carries its own preview showing every consequence before commitment.
- **Delta colours are the client's**: gains orange, losses blue, never green and never red.
- **Confirm is a separate action** and is the only thing that writes.

### 8.7 Scenario sheet

F4 is the one place a stepped flow is allowed, and only when the event genuinely has multiple parts. A scenario sheet may show a step list; a standard turn may not. The sheet is a wider version of the same panel with the scene still behind it, and it keeps the tag ribbon, the banner choices and the preview-before-commit rule. It does not become a wizard with a progress rail and a Back / Next footer, because that shape signals software onboarding rather than a game beat.

The sheet must be **data-driven, not scenario-specific**. The summer camp mechanic is disputed between the brief and `UMAMUSUME_REFERENCE.md` §1.1.2, which says every discipline is set to Lv5 at once for the four-turn window rather than the Trainer picking two. Encoding either version into the UI would bake an unverified rule into the interface, so the sheet renders whatever steps and options the scenario record supplies and the mechanic question is raised separately rather than answered by a wireframe.

### 8.8 TurnEvent, and what it costs

Recording which event fired and which choice was taken needs a table the schema does not have. Proposed shape, mapped to the flows above:

| Column | Purpose |
|---|---|
| `training_run_id`, `turn` | locates the event inside the run, same key as `TurnEntry` |
| `event_type` | `Character` / `SupportCard` / `Group` / `Scenario`, enum-backed |
| `source_name` | the event title as displayed |
| `choice_index`, `choice_label` | which option the Trainer took, and its text at the time |
| `deltas` | json, the stat, SP, Energy and Mood changes actually applied |
| `support_card_name`, `bond_delta` | nullable, F3 only |
| `origin_note` | nullable free text, so an event that fits no category is recorded honestly |

This is a schema addition and it needs two things before it can be built: a PRD requirement to cite, and Architect sign-off. `AGENTS.md` states the rule plainly, every new table must cite a PRD requirement, no citation no merge. Nothing in FR-A through FR-E currently covers event logging. The nearest anchor is US-3, the promise to keep the history a spreadsheet used to, and a spreadsheet that logs training runs does record events, so the citation argument is reasonable. It remains the owner's and the Architect's call rather than something to assume.

Note also that `deltas` stores what happened, which keeps it inside Planner Rule 4. It is not a second copy of the stat series and must not drift from `turn_entries`: the reconciliation rule is that `turn_entries` holds absolute values and `TurnEvent.deltas` is derived detail, so any mismatch between them is a defect with a named owner.

---

## 9. Motion"""

assert "## 9. Motion" in s
s = s.replace("## 9. Motion", NEW, 1)
p.write_text(s, encoding="utf-8")
print("DESIGN event flows added")


c = pathlib.Path("docs/design-research/CONSTRAINTS.md")
t = c.read_text(encoding="utf-8")

RULES = """## 10e. Event resolution and scenario flows

**D-130. Four flows, one panel.** F1 standard turn, F2 character event, F3 support card event, F4 scenario event. F2 and F3 share the DESIGN.md §8.6 event panel and differ only in the tag ribbon text and the outcome list. A fifth random-world flow is not modelled, because no in-run dataset supports it: `UMAMUSUME_REFERENCE.md` §1.4.4 carries three chains and §3.4 is live-ops content.

**D-131. The event panel is an overlay, not a modal.** The training scene, the turn chip and the stat band stay visible behind it. A full-page scrim that blanks the run destroys the orientation the client deliberately preserves.

**D-132. Every choice previews its full outcome before commitment, and committing is a separate action.** Applies to all four flows without exception. D-51 extends to events.

**D-133. Delta colour is fixed by the client, not by web convention.** Gains orange, losses blue. Green means action and red means risk. A green up-arrow in an event preview is a review failure.

**D-134. A stepped flow is allowed only for F4, and only when the event genuinely has multiple parts.** D-117 still bans a step rail on a standard turn. A scenario sheet keeps the tag ribbon, the banner choices and the scene behind it. It gains no progress rail and no Back / Next footer.

**D-135. Scenario sheets are data-driven.** No game mechanic is hardcoded into a scenario UI. Where the brief and the cited source disagree about a mechanic, the sheet renders what the record supplies and the disagreement is raised rather than silently resolved by a wireframe. Live instance: summer camp, `UMAMUSUME_REFERENCE.md` §1.1.2 versus the brief.

**D-136. Never hardcode a run length.** No source in this repo records a total turn count and the brief's "~50 rounds" is unverified. The turn chip shows a turn number and a turns-left value, as the client does, and derives nothing from an assumed total.

**D-137. `TurnEvent` needs a PRD citation and Architect sign-off before any migration.** `AGENTS.md` makes an uncited table a no-merge. Its `deltas` column stores observed outcomes only; `turn_entries` remains the source of truth for absolute values.

**D-138. Event copy is unverified until sourced.** Event titles, choice labels and mood words appearing in a mockup are sample content and may not become UI strings without a citation (D-20).

---

## 11. Verification gates"""

assert "## 11. Verification gates" in t
t = t.replace("## 11. Verification gates", RULES, 1)

t = t.replace("| G-17 | Chart justification", """| G-17a | Event overlay | trigger an event panel and inspect what is behind it | scene, turn chip and stat band still visible (D-131) |
| G-17b | Delta colour | inspect every gain and loss in an event preview | gains orange, losses blue, no green or red (D-133) |
| G-17c | Run length assumption | grep views and JS for a total-turn constant | zero (D-136) |
| G-17 | Chart justification""", 1)

c.write_text(t, encoding="utf-8")
print("CONSTRAINTS event rules added")
