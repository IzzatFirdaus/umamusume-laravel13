# P0 Canonical Database Incident — Preservation & Offline Forensic Procedure

Status: **OPEN**. Owner approval required before any step marked `[OWNER-GATED]`.
Incident: automation `dd93502b-59d7-496d-a982-8019f6bb86d4` reportedly inserted/deleted a
`users` row in the canonical development database on 2026-11-26.

## 1. What the log evidence does and does not establish

Established (read-only `storage/logs/laravel.log` forensics, Fullstack msg#19; Backend msg#21 concurs):

- `12:16:17–12:20:46` — `AgentSessionGuard` **REFUSED** 4 canonical connections
  (`DatabaseSafetyViolation` @ `AgentSessionGuard.php:114`), session `78dbd599`. The Laravel DB
  path was blocked.
- `12:18:01` — `RfcComplianceException` on an email to `test_a@example.com` (Laravel Mail path, failed).
- `12:21:23` — `Log` facade `TEST MESSAGE` INFO (log write, not a DB mutation).

**NOT established:** the actual successful mutation mechanism. The Laravel/Eloquent path was
refused, so if a `users` row was inserted/deleted it was **not** through the guarded Laravel
connection. Raw `PDO` / external `sqlite3` / direct file access sit **outside** the application
guard by design (source-level boundary, not a defect). Backend found no raw-PDO INSERT/DELETE path
in app code; `BackupDatabase::to()` uses raw PDO but `VACUUM INTO` is read-only on the source.

**Do not** classify raw SQLite/PDO as the confirmed mutation path without direct evidence. The
automation's "cleanup complete / 0 records" claim is **unverified** (backups predate the incident;
live WAL is forbidden to probe without owner authorization).

## 2. Preservation (read-only, no live DB mutation)

Already present, do not overwrite:

- `database/database.sqlite` (live, 5.0M) + `-wal` (3.3M) + `-shm`.
- `database/database.sqlite.backup-20261125-215656` (+ `-shm`, `-wal` 0B) — **pre-incident**.
- `database/database.sqlite.bak-before-rosy-reseed` (4.3M) — pre-reseed.

`[OWNER-GATED]` Offline snapshot for forensics (preserves WAL state consistently, no live write):

1. Copy the **whole set** `database.sqlite` + `-wal` + `-shm` to an evidence dir outside the repo
   (e.g. `%USERPROFILE%\TrainerDesk-Incident-Evidence\2026-11-26\`) using a byte copy while the app
   is **stopped** (no `php artisan serve`, no scheduler). Copying a live WAL set while running risks
   a torn snapshot.
2. Record SHA-256 of each copied file; store in `EVIDENCE-MANIFEST.txt`.
3. Do **not** open the live DB, do **not** run `VACUUM`, `migrate`, or any write against canonical.

## 3. Offline forensic analysis (on the copied evidence only)

`[OWNER-GATED]` Run against the **copy**, never the live file:

1. `sqlite3 <copy>.sqlite "SELECT id,email,created_at,updated_at FROM users;"` — confirm whether a
   `test_a@example.com` row exists or existed.
2. Check `sqlite_sequence` / `dbstat` / freelist for deleted-row residue if the row is absent.
3. Diff row counts vs the pre-incident backup copy.
4. Correlate timestamps with the log window (12:16–12:22).

## 4. Hardening (out of application scope — owner decision)

Application guards cannot contain raw OS-level access. Real isolation needs an owner-created
low-privilege Windows account + ACL on `database/`, or running the automation under a separate
identity. **Owner-gated; not applied.**

## 5. Automation

Do **not** resume or recreate automation `dd93502b-...`. Evidence preserved at
`.workbuddy-ai/memory/automations/dd93502b-.../memory.md`.

## 6. Exit criteria for OPEN

Owner-authorized offline snapshot taken + hashed; forensic copy confirms presence/absence of the
reported row; mechanism classified from direct evidence (not inference). Until then the incident
stays OPEN and no live canonical access occurs.