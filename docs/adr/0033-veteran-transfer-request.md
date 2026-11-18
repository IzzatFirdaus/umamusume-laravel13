# ADR-0033: A Veteran transfer is recorded as a request row that outlives the Veteran

<!-- ADR-CONTRACT
  decision: accepted (owner delegation 2026-11-18: "proceed with every owner gated tasks" — the written instruction to this session, recorded as the ruling source)
  code: built — migrations 2026_11_14_023609_create_transfer_requests_table, 2026_11_15_131115_fix_transfer_requests_table (both with down()); TransferRequest, OfferTransferRequest, CompleteTransferRequest, WithdrawTransferRequest; TransferRequestController; tests/Feature/TransferRequestTest.php, tests/browser/veterans-transfer-requests.spec.ts
  superseded-by: —
  amended-by: —
  open: the reward is recorded and never paid out — no wallet or ledger exists in this build, so the blob is an audit fact and no surface may present it as a balance
-->

Status: **accepted** (owner delegation 2026-11-18 — "proceed with every owner gated tasks"; the original draft note is preserved in the contract above).
until the owner records it as Accepted.**

## Context

A Trainer may offer a banked Veteran for transfer and receives a reward for it. Completing the transfer removes
the Veteran from the library, permanently, and there is no un-do. The action arrives on the library row and on the
Veteran detail, so it sits on the two surfaces a Trainer already browses.

Three facts made a plain column on `veterans` the wrong shape:

1. **The Veteran ceases to exist.** A fact about a transfer cannot live on the row the transfer deletes.
2. **The transfer has its own lifecycle.** An offer can sit unclaimed, be withdrawn, or be completed, and a
   Veteran may be offered again after a withdrawal. That is a history, not a state.
3. **The reward is a record, not a payment.** No wallet exists. Storing the reward as if it were credited would
   state a balance the tool cannot hold.

## Decision

**One `transfer_requests` row per offer, with the Veteran link nullable.**

- **`status` carries the lifecycle**: `offered`, `completed`, `withdrawn`, with the two timestamps stamped by the
  model's own guard rather than by whichever controller posted the update.
- **`veteran_id` is nullable with `nullOnDelete`.** Completion deletes the Veteran; the row survives with its link
  dropped, which is what makes it an audit record rather than a child row.
- **`veteran_label` is stamped before the delete.** With the link nulled there is nothing left to resolve a name
  from, so completion writes the same label every Veteran surface already prints. Without it the board can only
  fall back to a placeholder, which loses the one thing a finished-transfer record needs to say.
- **One offered request per Veteran, enforced by a partial unique index.** `withdrawn` and `completed` rows
  accumulate freely, because they are the trail.
- **The reward is stored, never applied.** `{star_pieces?, monies?, support_points?}` is recorded for audit and no
  code path credits it. A surface that presents it as a total would be inventing a ledger.

## What this does not license

- **No wallet, ledger, balance or spend tracking.** This ADR records what a transfer stated; it does not open a
  currency system, and `PRD.md` has no requirement for one.
- **No derivation of library membership from transfer rows.** A completed transfer deletes the Veteran; the
  library never learns to filter by `transfer_requests`.
- **No second Veteran write path.** `ADR-0022`'s tag twin and the library reads are untouched.

## Links

- `ADR-0022` (the Veteran library and its tag twin), `ADR-0010` (the Legacy payload the library sits beside),
  `PRD.md` FR-G, US-15, §6.3
- `TransferRequest::STATUS_*`; `TransferRequest::booted()` (timestamp guard)
- `app/Actions/{Offer,Complete,Withdraw}TransferRequest.php`; `TransferRequestController`
- `routes/web.php` (`veterans.transfers`, `veterans.transfers.store`, `transfers.complete`, `transfers.withdraw`)
- `tests/Feature/TransferRequestTest.php`, `tests/browser/veterans-transfer-requests.spec.ts`

## Ledger

- 2026-11-18 · **drafted** — the feature shipped at `6bbca89b` as a recovered peer draft, with a nine-item repair
  list in its commit body and no decision record. The two-shape migration history is preserved in
  `2026_11_15_131115`'s own docblock and is not restated here.
