# ADR-0038: The AI Advisor is an optional read-only layer over the deterministic engine

<!-- ADR-CONTRACT
decision: accepted — owner acceptance recorded 2026-11-25; all ten architectural boundaries confirmed
code: built — tests/Feature/AdvisorAnalysisRouteTest.php, AdvisorAiPipelineTest.php, AdvisorProvenanceTest.php, AdvisorRevisionTest.php; config/advisor_ai.php (off by default); route runs.advisor-analysis
superseded-by: —
amended-by: —
open: none — the live-provider verification runs in a separate session pass (no paid inference in the regression suites by design)
-->

Status: **accepted**. Owner acceptance recorded 2026-11-25. The code shipped with its switch off; this ADR is the boundary
record config/advisor_ai.php names in its header as owed. Acceptance finalizes the boundary
as the governing record; the ADR-owed note is removed from config/advisor_ai.php.

## Context

The Trainer Advisor has two layers. The deterministic one (TrainerAdvisor,
NextDecision, the frozen cockpit advisor.decision/finale contract) is the
product and predates this ADR. The AI layer — a provider that ranks the candidates the
engine already validated and one that writes an explanation sentence — landed on its own
read route, GET /training-runs/{run}/advisor-analysis (named runs.advisor-analysis),
because a page render would have dragged the whole cockpit payload along to reach one key,
and the cockpit prop is a recorded contract that must not widen.

The questions this ADR answers: what the AI layer may touch, what it may not, and what
happens when it is absent.

## Decision

1. **Optional, off by default.** advisor_ai.enabled defaults to false. Off means no cloud
   call, no local model call, no retrieval: the Advisor renders exactly what it rendered
   before the layer existed. Only the Trainer turns it on.
2. **The deterministic engine is the decision-safety floor.** TrainerAdvisor was not
   modified for this layer. Every provider state — validated, abstained, refused, timed
   out, malformed — lands back on the engine's own recommendation. A provider can change
   which eligible option is presented first and how it is explained; it can never add or remove an option or supply game-state.
3. **Clef selection is restricted to the engine's ranked candidates.** The provider is
   asked to choose only when the engine ranked more than one action. A response naming an
   option outside the supplied set — or a probability map naming an option never offered —
   is discarded and the deterministic recommendation stands. Zero or one candidate: no call.
4. **Read-only execution.** AdvisorAnalysis reads models and caches a payload; it has no
   save, no update and no dispatch. A failed analysis leaves the career byte-identical;
   this is regression-tested, not a convention.
5. **Cache boundary is the career revision.** The key is
   advisor-ai:{contract_version}:{career_id}:{revision}. The revision fingerprint (SHA256
   over run id, scenario key, next turn, status, build target and the latest turn's
   readings, 16 hex chars) changes when a turn is logged or corrected, so a new state is a new entry. A note edit does not change the revision,
   because a note changes nothing the advisor ranks on. contract_version keeps an old
   payload from being served under a changed shape.
6. **Provider degradation is the designed path, not the failure.** No key, a
   401/403/429, a 5xx after retries, a connection timeout, an exhausted budget, an invalid
   choice, or an abstention all degrade to the deterministic answer, the decision and explanation_source fields stating which. The 12-second request budget is the
   outer limit; the explanation step is skipped when it cannot finish inside it.
7. **Evidence is retrieved from the local corpus, with provenance.** Citations name a
   path, heading, line and server set from documents already in the repository. retrieval failure degrades to an empty evidence list, never a fabricated citation; the explanation must not contain a number or claim the engine's rows do not
   hold.
8. **Canonical source data and supporting documents are separated.** The advisor reads the
   run's rows and the repository's markdown corpus. It writes to neither, and adds no entry to config('uma.sources'), the fetch engine's host allowlist.
9. **No runtime dependency on agent tooling.** The layer calls one decision endpoint and
   one explanation endpoint by configuration and invokes no agent harness; no part of the
   deterministic experience reaches for it.
10. **The cockpit contract is preserved.** The cockpit's advisor prop remains exactly
    decision and finale. The AI layer appears only on its own JSON route, and a 404 when
    the layer is off is intentional: the feature is not enabled and this career has no
    recommendation are different statements.

## Consequences

- The regression suites never reach the network; Http::fake covers every provider call
  and the corpus index builds into a per-test scratch file.
- A response field that states a provider state (decision.provider, decision.status,
  explanation_source) is part of the contract a frontend may branch on; the
  machine-readable record of that contract is advisor-api-contract.json, which this
  revision corrected to agree with the producer.
- Accepting this ADR changes nothing in the tree; it removes the ADR-owed note from
  config/advisor_ai.php and gives the boundary its governing record.

## Ledger

- 2026-11-23 — drafted as proposed by the Hermes backend integration pass, with the
  contract-metadata corrections (H-2) and the 503 regression test (H-3) landing in the
  same working tree.

- 2026-11-25 — owner acceptance recorded: ADR-0038 accepted with all ten architectural boundaries confirmed. `enabled => false` preserved; deterministic engine remains the decision-safety floor. No external provider activation. Cockpit contract preserved.
