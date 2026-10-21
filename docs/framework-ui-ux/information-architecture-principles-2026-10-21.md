# The Eight Principles of Information Architecture

Status: **settled reference, with a provenance note.** Records Dan Brown's eight IA principles and how
they bind the navigation and grouping decisions in this repository.

## Provenance, and what was and was not fetched

| Field | Value |
| --- | --- |
| Author | Dan Brown |
| Original | *Eight principles of information architecture*, Bulletin of the American Society for Information
and Technology, August/September 2010 |
| Intended sources | [asis.org HTML](https://www.asis.org/Bulletin/Aug-10/AugSep10_Brown.html) — redirects, not
followed; [Wiley full text](https://asistdl.onlinelibrary.wiley.com/doi/full/10.1002/bult.2010.1720360609) —
**HTTP 403**, interstitial |
| Enumeration source | [a mirror of the article's own principle list](https://github.com/clemergo/principles.design/blob/64c71ca7198b53e3a86ad2ee5f78ba26c8f8bf6f/_examples/eight-principles-of-information-architecture.md), fetched 2026-10-21 |
| Fetched | 2026-10-21 |

**What this means for confidence:** the eight names and their one-line summaries are reliable — they are
the article's own list, mirrored with its `link:` field still pointing at the ASIS PDF. **The article's
prose discussion is not fetched**, so anything below beyond the one-line summary is this repository's
reading of the principle, marked as such.

## The eight

### 1. Principle of objects

> Treat content as a living, breathing thing with a lifecycle, behaviors and attributes.

**Applied here:** a run, a veteran, a support card and a skill are objects with a lifecycle, not rows in a
table. A run is created, played and completed; a card has a level and a bond; a skill has an acquisition
state. The screens treat each as an object with its own states rather than as a record to be edited.

### 2. Principle of choices

> Create pages that offer meaningful choices to users, keeping the range of choices available focused on a
> particular task.

**Applied here:** one decision per screen. The cockpit carries one recommended action; the wizard carries
one step; `training-decision` carries one selection. This is the IA argument behind the Hick's Law sizing:
the point is not merely fewer options, it is that the options offered belong to one task.

### 3. Principle of disclosure

> Show only enough information to help people understand what kinds of information they'll find as they
> dig deeper.

**Applied here:** the index pages preview and the detail pages hold. `catalog` shows a name and three
aptitude letters; `catalog-detail` holds the nine. `support-cards` shows the deciding effects and pushes
the rest behind a note.

**Where this repository departs, deliberately:** the Database area is dense by design. Brown's principle
would have it preview and defer; the recorded ruling is that a reference table is dense by nature and the
density is confined there (`PROCESS-PLANS.md` §13, "Deliberately traded"). The departure is named rather
than quietly taken.

### 4. Principle of exemplars

> Describe the contents of categories by showing examples of the contents.

**Applied here:** the scenario cards show a worked example of what the scenario offers rather than
describing the category; the event example in the navigation annotations; the empty states that name what
would appear.

**Where it is weak:** several empty states state the absence and offer an action without showing an
example of what would fill them. That is a genuine gap against this principle and is recorded in the gap
register below.

### 5. Principle of front doors

> Assume at least half of the website's visitors will come through some page other than the home page.

**Applied here:** every screen is reachable directly and carries its own breadcrumb, its own page head and
its own way back. A deep link to `cockpit-ura-finale` is fully usable without having passed through the
dashboard.

**Where it is tested:** the error pages. A 404 is the definition of arriving somewhere unexpected, so it
carries the rail, the crumb and the way back rather than an apology alone.

### 6. Principle of multiple classification

> Offer users several different classification schemes to browse the site's content.

**Applied here:** the libraries are reachable more than one way — support cards by type, by rarity and by
server; skills by category, distance and running style; runs by scenario and by status. Where a single
classification is imposed, it is imposed deliberately and visible as such.

### 7. Principle of focused navigation

> The principle of focused navigation — Don't mix apples and oranges in your navigation scheme.

**Applied here:** ten rail destinations in four named bands (Play, My data, Library, System). The bands are
the oranges and the apples; the rail is not one flat list of ten. The mobile bar is a different scheme
again — five destinations chosen for reach — and it does not pretend to be the rail.

**The one live tension:** Help is reachable from every page's top bar but is deliberately absent from the
rail. That is Brown's own distinction — a navigation scheme should not carry what is not part of the task —
and it is recorded in the help page's annotation key so the omission reads as a decision.

### 8. Principle of growth

> Assume the content you have today is a small fraction of the content you will have tomorrow.

**Applied here:** the scenario descriptor is data the layout reads, not branching the layout contains
(D-240). A fifth scenario reaches the same shell for one config entry. Our Grand Concert degrades to
baseline plus known caps rather than inventing chrome (D-241), which is what makes growth survivable.

## How the eight map onto this repository

| Principle | Implemented by | Where it is visible |
| --- | --- | --- |
| **1 Objects** | the domain models; the run lifecycle | A run has states; a card has a level; a spark has a family. |
| **2 Choices** | one decision per screen | The cockpit, the wizard step, the decision desks. |
| **3 Disclosure** | index previews, detail holds; progressive disclosure | Index and detail pairs across all six libraries. |
| **4 Exemplars** | scenario cards, the worked example on the annotation keys | Strong on the wizard, weak on several empty states. |
| **5 Front doors** | breadcrumbs, page heads, direct reachability, the error pages | Any screen can be the first screen. |
| **6 Multiple classification** | three browse axes on cards, skills and runs | No library has a single imposed scheme. |
| **7 Focused navigation** | four named rail bands; a separate mobile scheme | The rail is banded, not flat. |
| **8 Growth** | D-240 the descriptor is data; D-241 degrade to baseline | A fifth scenario costs one config entry. |

## Gap register

| Principle | Gap | Where it is tracked |
| --- | --- | --- |
| **4 Exemplars** | Several empty states name the absence and offer an action without showing an example of what would fill them. The principle asks for an example of the contents. | noted here; the four-state primitives (D-45) name an action but not an exemplar |
| **3 Disclosure** | The Database area is deliberately exempt. That is a ruling, not a defect, but it means the principle does not hold uniformly across the product. | `PROCESS-PLANS.md` §13 "Deliberately traded" |

## What this framework decides that the others do not

Every other framework in this set is about a **screen**. Brown's are about the **space between screens**:
what an object is, what a page may offer, how much it reveals, whether a deep link works, how content is
reachable, and what happens when there is ten times as much of it.

The two that bite hardest here are **3 Disclosure** and **8 Growth**, because both are about a future the
wireframes cannot draw: the density ruling and the fifth-scenario path are the two decisions that would be
invisible in a screenshot and expensive to retrofit.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| IA-1 | Every screen is directly reachable and carries its own crumb and way back. | 5 |
| IA-2 | Navigation is banded by task, never one flat list. | 7 |
| IA-3 | A library is browsable by more than one axis. | 6 |
| IA-4 | An index previews; a detail holds. Density is an explicit exemption, never a drift. | 3 |
| IA-5 | An empty state shows an example of what would fill it. | 4 |
| IA-6 | Adding a scenario must cost configuration, not layout. | 8 |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created. Enumeration from a mirror of the article's own list, because both primary HTML hosts were blocked; the prose discussion is therefore unread and the applied commentary is this repository's reading. |
