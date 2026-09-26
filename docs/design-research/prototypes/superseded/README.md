# Superseded prototypes

These four files are kept for the record and are **not** the current design. They sit in a
subfolder so `gate.py`, which sweeps `prototypes/*.html`, stops treating them as live artifacts.

They were retired on 2026-09-27 when the scenario-aware work converged on one layout.

| File | Superseded by | Why |
|---|---|---|
| `screen-a-dashboard.html` | `../screen-a-scenario-v10.html` | Single-scenario URA dashboard. No scenario switching, so it cannot demonstrate §10n conformance. Its stat band header is a solid `#106F9F` with white text, which is not the tint system in `DESIGN.md` §3.6. |
| `screen-b-guided-v1-inline-preview.html` | `../screen-a-scenario-v10.html` | One of two guided-input layout variations. The variation was dropped when the layout converged; the guided input now lives in the same spine as the dashboard. Its band header carries no tint tokens at all. |
| `screen-b-guided-v2-side-preview.html` | `../screen-a-scenario-v10.html` | The other variation. Its preview column sits beside the choices, and it shipped a real defect where the preview rendered **above** them. Dropped for the reasons in `DESIGN.md` §11. |
| `screen-e-scenario-switch.html` | `../screen-a-scenario-v10.html` | The first scenario-switching build. v10 absorbs it and adds the stat band grade derivation, the trainee side rail, and the per-column band rules. |

Reading these for art direction is fine. Reading them for labels, tokens or layout is not: use
`../screen-a-scenario-v10.html`, which is the file the gate checks.
