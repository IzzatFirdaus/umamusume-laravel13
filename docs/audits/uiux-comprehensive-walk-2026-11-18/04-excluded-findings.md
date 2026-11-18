# 04 — Excluded findings and limitations

| Exclusion | Kind | Detail / owner |
|---|---|---|
| Visual-craft review | Method / tooling | `vision_analyze` returned HTTP 429 on four attempts, including one retry after the indicated delay. No density, hierarchy or "does it read as raised" judgement was made. Owner: commission a visual pass with a working instrument or a human reviewer |
| Real-user usability testing | Process | Not performed. No claim of usability testing with real users is made |
| 404 / 419 / 500 error views | Coverage | No browser route; not observed |
| Loading and error states on all 58 screens | Coverage | Only status/alert states reached during the walk were observed; async loading and forced errors were not triggered per screen |
| Routes beyond the 70-visit BFS cap | Coverage | Crawl stopped at its cap; career routes were covered through the created run instead. A few catalogue detail pages and wizard variants were not visited |
| Peer-owned dirty files | Process | Shared worktree carries peer edits; none were staged, written or committed by this pass |
| Throwaway run #1 | Data | Created in the session-private database, not the dev file; it will disappear when scratch is cleaned. No production data was changed |
| C-05, C-06, C-08, C-09 from the static pass | Not re-measured | Title-only reasons in SparkSummary/RacePlanner and locale-formatted dates in TransferRequests were not re-measured in this pass; recorded in `06-static-candidates.md`, not filed |
| Subagent research and static-candidate sheets | Provenance | Dispatched as read-only inputs; any claim from them must be re-measured before it becomes a finding. They are not used above |

The three dated audit findings in `AUD-20261118-001` remain resolved in source; this pass did not re-open them.
