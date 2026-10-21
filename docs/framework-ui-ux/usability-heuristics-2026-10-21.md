# Usability heuristics for Trainer Desk

Status: **settled reference**. This file records Jakob Nielsen's ten usability heuristics as NN/g
states them, the detail behind each one, and how they bind the screens in this repository.

Read from the source on **2026-10-21**. The heuristics are quoted, not paraphrased, because a
paraphrase of a heuristic is a different heuristic.

## Provenance

| Field | Value |
| --- | --- |
| Primary source | [10 Usability Heuristics for User Interface Design](https://www.nngroup.com/articles/ten-usability-heuristics/), Jakob Nielsen |
| Published | 1994-04-24 |
| Last reviewed by NN/g | 2024-01-30 (names, descriptions and examples updated by Kate Moran and Feifei Liu) |
| Fetched | 2026-10-21 |
| Detail sources | the per-heuristic articles linked below, each fetched 2026-10-21 |

NN/g's own framing, which matters for how this file is used: these are **"broad rules of thumb and
not specific usability guidelines"**. They are therefore a review instrument, not a test suite. Where
a heuristic collides with a rule already ratified in this repository, the repository rule wins and the
collision is recorded here rather than resolved silently (`AGENTS.md` §2).

## The ten, verbatim

### H1. Visibility of System Status

> The design should always keep users informed about what is going on, through appropriate feedback
> within a reasonable amount of time.

**Tips:** no action with consequences to users should be taken without informing them; present feedback
as quickly as possible, ideally immediately; build trust through open and continuous communication.

**Example NN/g gives:** *You Are Here* indicators on mall maps show people where they currently are, so
they can determine where to go next.

### H2. Match Between the System and the Real World

> The design should speak the users' language. Use words, phrases, and concepts familiar to the user,
> rather than internal jargon. Follow real-world conventions, making information appear in a natural
> and logical order.

**Tips:** users must be able to understand meaning without looking a word up; never assume your reading
of a word matches theirs; user research uncovers their terminology and their mental models.

**Mechanism NN/g names:** *natural mapping*. The stovetop whose controls match the layout of the heating
elements is the canonical case.

### H3. User Control and Freedom

> Users often perform actions by mistake. They need a clearly marked "emergency exit" to leave the
> unwanted action without having to go through an extended process.

**Tips:** support Undo and Redo; show a clear way to exit the current interaction, such as a Cancel
button; make the exit clearly labelled and discoverable.

**Digital equivalent NN/g draws:** digital spaces need quick emergency exits in the same way physical
spaces do.

### H4. Consistency and Standards

> Users should not have to wonder whether different words, situations, or actions mean the same thing.
> Follow platform and industry conventions.

**Tips:** maintain both internal consistency, within one product or a product family, and external
consistency, by following established industry conventions.

NN/g cites **Jakob's Law** as the mechanism: people spend most of their time in products other than
this one, and those products set their expectations. Failing to keep consistency raises cognitive load
by forcing the user to learn something new.

### H5. Error Prevention

> Good error messages are important, but the best designs carefully prevent problems from occurring in
> the first place. Either eliminate error-prone conditions, or check for them and present users with a
> confirmation option before they commit to the action.

NN/g's [error-prevention article](https://www.nngroup.com/articles/error-prevention/) names four
system-design causes of **slips**, the case where a user intends one action and takes a similar other:

| Cause | What it looks like |
| --- | --- |
| **Miscue** | the design gives the wrong cue, for example by violating a convention |
| **Poorly differentiated options** | several choices with similar names or near-identical styling |
| **Problematic presentation or interaction** | no safeguard in the layout or flow, for example two buttons placed very close together |
| **No confirmation for destructive actions** | an irreversible action commits without an explicit check |

The same article adds the rule this repository now inherits: whoever is authorised to take an action
ought also to be authorised to **undo** it. In the missile-alert case the retraction took 38 minutes
because no such path existed.

### H6. Recognition Rather than Recall

> Minimize the user's memory load by making elements, actions, and options visible. The user should not
> have to remember information from one part of the interface to another. Information required to use
> the design (e.g., field labels or menu items) should be visible or easily retrievable when needed.

NN/g's [recognition and recall article](https://www.nngroup.com/articles/recognition-and-recall/)
grounds the heuristic in the number of retrieval cues: **recognition carries more cues than recall**,
so it is easier. A menu is the classic recognition interface because it shows the available commands.
A login is the classic recall interface because it supplies almost no cue beyond the site itself.

**Recall rides on three factors**, all of which a design can influence: practice, recency, and context.
Search suggestions are the heuristic applied to query entry, because they convert query generation from
recall into recognition.

### H7. Flexibility and Efficiency of Use

> Shortcuts, hidden from novice users, may speed up the interaction for the expert user such that the
> design can cater to both inexperienced and experienced users. Allow users to tailor frequent actions.

NN/g's [flexibility article](https://www.nngroup.com/articles/flexibility-efficiency-heuristic/)
splits the heuristic into two deliverables:

- **Multiple methods** to accomplish the same task, so people can pick the one that suits their working
  style. The warning attached to it is that this is **not** permission to duplicate a function in several
  places; duplication forces users to learn what the difference is, if any.
- **Accelerators**: secondary, faster, less obvious paths for the same task. The design constraint is
  that an accelerator must be *discoverable but unobtrusive*; NN/g notes the swipe keyboard fails the
  discoverability half.

**Customisation is not a substitute.** Most users do not bother to customise a system, so a design that
only becomes efficient after configuration is not flexible by this heuristic.

### H8. Aesthetic and Minimalist Design

> Interfaces should not contain information which is irrelevant or rarely needed. Every extra unit of
> information in an interface competes with the relevant units of information and diminishes their
> relative visibility.

NN/g's [aesthetic and minimalist article](https://www.nngroup.com/articles/aesthetic-minimalist-design/)
contains the correction that matters most for this repository: **the heuristic is not the visual
minimalism trend.** A page can look minimalist and fail it by omitting necessary information, and a
dense page can pass it by carrying sufficient information and only sufficient information. The test is
**signal against noise**: labelling with high information scent, plain language, clear signifiers and
helper text are signal; decoration, unexplained technical terms and multipurpose visual cues are noise.

The article also records that users form an aesthetics-driven first impression in about **50
milliseconds**, roughly ten times faster than reading, which is why aesthetics cannot be dismissed as
surface.

### H9. Help Users Recognize, Diagnose, and Recover from Errors

> Error messages should be expressed in plain language (no error codes), precisely indicate the problem,
> and constructively suggest a solution.

NN/g's [error-message guidelines](https://www.nngroup.com/articles/error-message-guidelines/) expand it
into three groups of checkable rules:

**Visibility**

- Display the message **close to the error's source**, because proximity associates the message with the
  control that needs attention.
- Use **redundant and accessible** indicators: text, icon and highlight together. NN/g is explicit that
  error state must **never be carried by colour or animation alone**, citing the roughly 350 million
  people with a colour-vision deficiency.
- Design to **severity**. A "good to know" message is not a modal dialog.
- **Never display an error prematurely.** Flagging a field the user has not finished with is what NN/g
  calls a *hostile pattern*; it is like grading a test before the student has answered.

**Communication**

- Plain language, not jargon. NN/g points at the 404 page as the most common error message on the web
  and as a violation of this rule.
- Describe the issue **precisely**; "An error occurred" carries no context. Equally, avoid precision
  beyond the user's mental model.
- **Offer constructive advice**, not only the problem.
- **Positive, non-blaming tone.** Avoid *invalid*, *illegal* and *incorrect*, and avoid humour that goes
  stale on the hundredth encounter.

**Efficiency**

- **Safeguard against likely mistakes.** The worst error message is the one that does not exist: a
  mistake with no feedback produces a cascade of misunderstanding.

### H10. Help and Documentation

> Even though it is better if the system can be used without documentation, it may be necessary to
> provide help and documentation. Any such information should be easy to search, focused on the user's
> task, list concrete steps to be carried out, and not be too large.

NN/g's [help and documentation article](https://www.nngroup.com/articles/help-and-documentation/) splits
it into two modes:

| Mode | Purpose | Forms |
| --- | --- | --- |
| **Proactive** | familiarise the user before a problem occurs | tutorials, instructional overlays, templates, contextual help, tooltips, wizards |
| **Reactive** | answer questions, troubleshoot, build proficiency | FAQ, technical documentation, tutorials, training modules |

Proactive help is further split into **push** and **pull** revelations. Push help is not tied to the
user's current goal and is routinely ignored, so it must be **skippable** and must remain reachable
elsewhere. Pull help is triggered by the user's own context and is the preferred form.

Reactive documentation must be **comprehensive and detailed**, not a set of high-level overviews.

## What this repository adds on top

Nielsen's ten are the review spine. This repository already carries three further layers, and this file
does not replace them:

| Layer | Owner | Relationship to Nielsen |
| --- | --- | --- |
| **Laws of UX** (29 entries) and **Laws of UI** (13 entries) | `docs/research-scratch/PLANS-AND-BRIEFS.md` §ux-laws-audit-plan, `DESIGN-CORPUS.md` | The mechanism behind several heuristics. H4 *is* Jakob's Law; H1 is Doherty Threshold plus Visibility; H8 is Prägnanz plus Von Restorff. |
| **Game-specific heuristics** GH1 to GH5 | `docs/research-scratch/UX-DELIVERABLES.md` Part 3 | Domain additions that Nielsen does not cover: Feedback Loop Quality, Reward Cadence, Cognitive Load Management, Information Architecture, Progressive Disclosure. |
| **Design decisions D-1 to D-289** | `docs/research-scratch/DESIGN-CORPUS.md` | The ratified implementation of both layers. Every heuristic in this file resolves to one or more of them. |

## Where each heuristic lands in this product

The mapping below is the review checklist. A screen is reviewed against the heuristic, and the verdict
cites the rule that implements it.

| Heuristic | Implemented by | Screen-level evidence |
| --- | --- | --- |
| **H1** status | D-45 four states, D-104 pre-paint theme, Doherty Threshold, `ProvenanceMark.vue` | Every data view shows empty, loading, populated and error. The theme is resolved before first paint. Writes carry a pending label and a saved confirmation. |
| **H2** real world | D-235 terminology normalisation, `lang/en/uma.php`, Global client strings | The app's nouns are the client's: SP, Mood, Bond, Spark, Trainee Umamusume. Wiki vocabulary is normalised on import, not on display. |
| **H3** control | D-55 keyboard escape, `SetupLayout` cancel, the reset-to-defaults control | The guided flow steps back on Escape. A plan change is cancellable. **Undo after a committed turn is still open: see the gap register.** |
| **H4** consistency | D-1 token law, D-2 one source per value, `LAYOUT_WIDTH_CLASSES` | One token set, one type scale, one focus ring, one component behaviour per role. |
| **H5** error prevention | D-200 success and failure branches, D-201 failure as a log entry, the 44px target rule | A choice preview shows both branches. A blocked race row is disabled with its reason rather than hidden. |
| **H6** recognition | D-171 energy beside the confirm, the advisor printing the number it cites | The value a decision needs is on the decision screen, never one screen away. |
| **H7** flexibility | keyboard paths in the guided flow (D-55), `useLayout` | The flow is fully operable without a pointer. **Accelerators are absent: see the gap register.** |
| **H8** minimalism | D-80 every screen earns its elements, D-82 colour dose caps, D-75 ornament budget | A stat, chip or card that does not change what a Trainer knows comes out. |
| **H9** error recovery | D-201, the two error pages, `import-preview`'s per-field found/missing tags | Errors state the cause, what is still saved, and the way back. Never colour alone; never an error code in the user's face. |
| **H10** help | proactive: the preflight "what this scenario asks"; reactive: `docs/` | The preflight step states reversibility before the commit. **A first-class help surface does not exist: see the gap register.** |

## Gap register

Recorded because a heuristic with no implementation is a finding, not a checklist item.

| Heuristic | Gap | Severity | Where it is tracked |
| --- | --- | --- | --- |
| **H3** | **No undo after a committed training turn.** NN/g rates the absence of undo as the mechanism behind the missile-alert retraction failure. | 3 | `UX-DELIVERABLES.md` H3 issue table; `SLICE-RECORDS.md` |
| **H7** | **No accelerators.** The guided flow is keyboard-operable but offers no faster path for a repeat user, and there is no customisation. | 2 | `UX-DELIVERABLES.md` H7 |
| **H10** | **No help surface.** The preflight step is proactive help for one flow; there is nothing reactive, nothing searchable, and no in-product documentation. | 3 | `UX-DELIVERABLES.md` H10, rated 4/10 |

Two further NN/g points that bear on this product specifically:

- **H8 is not minimalism.** A Trainers' cockpit is legitimately dense. The test is whether every element
  carries information, not whether the page looks spare.
- **H9 never display an error prematurely.** Inline validation on a field the Trainer is still filling
  is the hostile pattern; a turn is committed once and cannot be re-answered, which makes premature
  error copy here strictly worse than on a form.

## How to run the review

The method is the one `UX-DELIVERABLES.md` Part 3 already uses, so a verdict from this file and a
verdict from that one are comparable.

1. Take one screen, in both themes, at 320, 390, 768 and 1280px.
2. Score each heuristic 0 to 10 with a one-line justification.
3. Every PASS, PARTIAL or FAIL carries **either** a rendered frame **or** a `file:line`. A claim resting
   on one frame is downgraded to *needs measurement*.
4. Severity uses the same 0 to 4 scale: 0 cosmetic, 1 minor, 2 moderate, 3 severe, 4 catastrophic. The
   audit plan's P0 to P3 mapping applies (`PLANS-AND-BRIEFS.md` §ux-laws-audit-plan).
5. A FAIL that a ratified D-rule already covers is filed against the D-rule, not against the heuristic,
   so no defect arrives twice.

**The measurement limit is real and is recorded here:** Chromium cannot launch under this repository's
sandbox (`spawn EPERM`), so no verdict in this file or the files it feeds is a rendered observation.
Every one is source-and-markup evidence. `DESIGN.md` §2.1 already records that the G-18 contrast gate
measures nothing yet.

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created from the NN/g fetches. Ten heuristics recorded verbatim with their detail articles, mapped onto the repository's D-rules, with the H3, H7 and H10 gaps registered. |
