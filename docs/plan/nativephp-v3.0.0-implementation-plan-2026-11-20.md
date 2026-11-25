# Trainer Desk v3.0.0 — NativePHP Implementation Plan

<!-- PLAN-CONTRACT
plan: nativephp-v3.0.0-implementation-plan-2026-11-20
status: drafted
slices: N0-N5
superseded-by: —
amended-by: —
open: Planning only; no code changes; Windows packaging not runnable in planning environment
-->

**Date:** 2026-11-20
**Scope:** NativePHP desktop packaging and packaged-app acceptance for v3.0.0 (Windows x64).
**Status:** planning only; no code changes in this pass.
**Repository state:** verified in this session against the local Trainer Desk checkout.

## 1. Objective

Ship Trainer Desk v3.0.0 as a NativePHP-packaged Windows installer whose packaged app:
- boots its embedded PHP server,
- serves the Inertia/Vue SPA,
- opens its desktop window,
- enforces the NativePHP request-secret security gate,
- passes the packaged-app Playwright acceptance spec,
- and publishes the installer and updater metadata through an authorized GitHub release.

The application's v3.0.0 release number is separate from the NativePHP dependency version (`nativephp/desktop` 2.3, `nativephp/php-bin` 1.2).

## 2. Verified current state

- Dependency: `nativephp/desktop` 2.3, `nativephp/php-bin` 1.2 (`composer.json:17-18`).
- Provider: `config/nativephp.php` names `App\Providers\NativeAppServiceProvider` (`config/nativephp.php`, provider key).
- `NativeAppServiceProvider` implements `Native\Desktop\Contracts\ProvidesPhpIni`, opens `Window`, returns empty php.ini overrides (`app/Providers/NativeAppServiceProvider.php`).
- Secret middleware: `EnforceNativePHPSecret` is appended to the `web` group and reads `config('nativephp.secret')`; `_php_native` cookie is exempted from cookie encryption (`bootstrap/app.php`).
- Build guard: `composer run pre-native-build` runs `tools/check-electron-env.php` (`composer.json:62-64`).
- Workflows: `.github/workflows/nativephp-windows.yml`, `.github/workflows/nativephp-windows-acceptance.yml`, `.github/workflows/release.yml`.
- Acceptance spec: `tests/browser/nativephp-packaged.spec.ts` tests 403/403/200/200 secret gate against `NATIVEPHP_EXE_PATH`.
- Packaged config: `playwright.nativephp.config.ts` runs only `nativephp-packaged.spec.ts`.

## 3. Applicable project requirements

- Accepted ADRs are binding; proposed decisions are not.
- The roadmap controls implementation ordering; plans connect to roadmap slices through `PLAN-CONTRACT`.
- Proposal paths must be verified against the tracked tree before use.
- New documentation follows the established ownership map; no new root docs or invented directories.
- No new dependencies without approval; `composer audit` and `npm audit --omit=dev` must be clean of reachable critical/high before tagging.

## 4. Environment and tools (verified in this session)

| Capability | Observed result |
|---|---|
| File reading/editing | Available against the Trainer Desk checkout |
| Terminal execution | Available |
| Git CLI | Available; repository checkout present |
| PHP | 8.4 available in the planning container; workflows pin PHP 8.5 |
| Node.js | 22 available in the planning container; workflows pin Node 24 |
| Composer | Available in repo context; planning container may lack it |
| Windows packaging | Not runnable in this Linux planning session |
| Web research | Available |
| GitHub access | Repository is accessible locally; CI publication remains owner-gated |

## 5. Technical research findings

NativePHP 2.x remains the appropriate implementation path. Official requirements include PHP 8.3+, Laravel 11+, Node 22+, and Windows 10 or later. The documented `NativeAppServiceProvider`, `ProvidesPhpIni`, and `Window::open()` pattern is compatible with the Trainer Desk architecture.

The security gate still needs runtime validation. NativePHP documents a dynamic authentication key between its web servers and protection against regular browser access, but that does not independently prove the application's custom `X-NativePHP-Secret` header and `_php_native` cookie behavior. Both implementations must be traced and the intended protections verified at runtime.

Updater metadata needs a format check. The existing release workflow generates `latest.json`, while standard electron-builder Windows NSIS updating commonly uses `latest.yml`. The repository's custom metadata generation must be established from the pinned build/release configuration before finalizing release acceptance. Do not assume either format is correct without checking the installed NativePHP/Electron builder configuration.

Release publication must test the actual published artifact. NativePHP's documented GitHub publishing workflow may involve a draft release and `native:publish`; this repository uses custom GitHub Actions publication. The custom workflow must be checked against that path to prevent duplicate publication or release of a build different from the one accepted.

These findings do not justify changing dependencies now. They identify verification gates for the existing pinned implementation.

## 6. Approach comparison

| Approach | Compatibility and cost | Decision |
|---|---|---|
| A. Preserve NativePHP 2.3 and existing CI | Best fit with documented Laravel/Vue architecture. No new dependencies; primarily verification work | Recommended |
| B. Upgrade NativePHP within 2.x first | Requires Composer compatibility review, lockfile changes, Windows rebuild and full regression testing | Defer unless a concrete blocker is found |
| C. Replace NativePHP with custom Electron or Tauri packaging | Substantial architectural changes, additional dependencies and new security, lifecycle and release work | Outside v3.0.0 scope |

Approach A minimizes implementation risk and preserves prior integration work. The first response to a failed test should be to identify whether it is an environment, packaging, application, or dependency defect—not to initiate a framework migration.

## 7. Dependency-ordered execution plan

### G0 — Repository identity and governance (blocking prerequisite)

Components: Git working tree, `AGENTS.md`, constraints, roadmap, governance masters, accepted ADRs.

Actions:
- Confirm repository root, branch, HEAD commit, dirty state, governing instructions, and NativePHP roadmap slice.
- Verify exact tracked paths named in this plan.
- Reconcile the v3.0.0 NativePHP task with the authoritative roadmap before implementation.

Acceptance: implementation scope is consistent with binding repository instructions and has an identified source commit and roadmap authority.

### N0 — Environment and dependency preflight

Components: `composer.json`, `composer.lock`, `package.json`, lockfiles, `.env.example`, `tools/check-electron-env.php`.

Actions:
- Confirm PHP/Laravel/Node compatibility and pinned NativePHP, Electron, and Playwright versions.
- Inspect release environment requirements.
- Ensure `ELECTRON_RUN_AS_NODE` is unset for build execution.
- Run `composer validate` and dependency inspection where Composer is available.

Tests: `composer validate`, dependency inspection, `composer run pre-native-build`.

Acceptance: dependency graph is valid; build guard exits successfully; environment requirements are documented without embedding secrets.

### N1 — Native application boot and UI

Components: `app/Providers/NativeAppServiceProvider.php`, `config/nativephp.php`, `bootstrap/providers.php`, Laravel/Inertia/Vue entry points, Vite assets.

Actions:
- Verify provider registration only in the appropriate lifecycle.
- Verify window opens.
- Verify embedded PHP server starts.
- Verify production UI loads without a Vite development server.
- Verify ordinary Laravel web requests do not accidentally initialize a native window.

Tests: `native:config` and `native:php-ini` diagnostics where supported by the installed package, followed by a real packaged-app launch.

Acceptance: native window is visible, expected SPA content renders, assets load, and ordinary web requests do not open a desktop window.

### N2 — Request-secret security

Components: `app/Http/Middleware/EnforceNativePHPSecret.php`, `bootstrap/app.php`, `tests/browser/nativephp-packaged.spec.ts`.

Actions:
- Trace runtime secret generation and injection.
- Trace middleware ordering, header validation, and cookie handling.
- Ensure a missing or invalid secret cannot silently disable protection.
- Ensure `_php_native` cookie handling remains compatible with `EncryptCookies`.

Tests and expected results:

| Request | Expected |
|---|---|
| No authentication | HTTP 403 |
| Incorrect secret header | HTTP 403 |
| Correct secret header | HTTP 200 |
| Valid NativePHP cookie | HTTP 200 |

Acceptance: all four existing assertions pass against the installed executable. Add tests for absent secret or forged cookie only if repository inspection identifies a genuine coverage gap.

### N3 — Windows artifact production

Components: `.github/workflows/nativephp-windows.yml`, build hooks, bundling configuration, Windows installer output.

Actions:
- Verify Windows runner, asset-building sequence, NativePHP packaging invocation, and artifact upload.
- Run authorized candidate build only after N0-N2 prerequisites.
- Confirm installer `.exe` is produced and uploaded.

Acceptance: build workflow succeeds and produces an installable Windows x64 executable with unambiguous artifact identity.

### N4 — Installed-application acceptance

Components: `.github/workflows/nativephp-windows-acceptance.yml`, `playwright.nativephp.config.ts`, packaged-app spec.

Actions:
- Retrieve the N3 artifact and confirm its identity.
- Install it on the Windows runner.
- Resolve `NATIVEPHP_EXE_PATH`.
- Execute packaged tests.
- Check startup and UI readiness in addition to request authorization.
- Retain logs or traces useful for diagnosis.

Command:

```bash
npx playwright test --config=playwright.nativephp.config.ts
```

Acceptance: existing secret-gate assertions pass; embedded PHP startup and desktop-window behavior are evidenced.

### N5 — Release and updater verification (owner approval required)

Components: `.github/workflows/release.yml`, NativePHP updater configuration, GitHub Release artifacts, checksum generation.

Actions:
- Verify whether publication uses NativePHP's built-in publisher or the custom workflow.
- Confirm updater metadata format, version consistency, and the release artifact's relationship to the accepted build.
- Ensure the artifact actually published has passed packaged-app acceptance, either by reusing the verified artifact or retesting the final release build.
- Confirm `latest.json` (or the actual supported updater metadata format) matches the installed app's expected updater behavior.

Acceptance: release `v3.0.0` contains the intended installer, required updater metadata, and checksum; the updater can discover that release through its configured provider. No tag or release is created in this planning pass.

## 8. Verification matrix

| Test category | Required evidence | Execution environment |
|---|---|---|
| Governance | Instructions, roadmap alignment, Git status, tracked files | Repository checkout |
| Dependency integrity | Composer validation and lockfile inspection | Repository checkout |
| Build environment | Electron environment guard succeeds | Build runner |
| Native runtime | PHP server starts; window opens | Windows |
| Frontend | Production Inertia/Vue assets render | Windows packaged app |
| Request security | 403 / 403 / 200 / 200 assertions | Packaged Playwright |
| Distribution | Windows installer exists and installs | Windows CI |
| Updater | Metadata matches artifact and provider | Release validation |
| Traceability | Accepted build and published build correspond | GitHub Actions |

Playwright's Electron automation APIs (`electron.launch()`, `firstWindow()`) are a viable supplementary technique for window-level checks, but whether they work with the exact packaged executable and Electron configuration must be measured rather than assumed.

## 9. Outstanding risks and decisions

| Priority | Issue | Required resolution |
|---|---|---|
| P0 | Repository/governance mismatch risk | Reconcile task with authoritative roadmap before implementation |
| P0 | Runtime secret injection unverified | Inspect exact NativePHP package implementation and custom middleware |
| P0 | Acceptance-to-release artifact mismatch | Establish artifact identity and enforce a release gate |
| P1 | `latest.json` versus standard Windows `latest.yml` | Confirm actual updater implementation and metadata format |
| P1 | NativePHP package-version differences | Check pinned version against documentation and release notes |
| P1 | Fixed port range 8100-9000 | Validate endpoint discovery, timeout, and cleanup behavior |
| P1 | `native:config` cached-config startup failures | Treat as troubleshooting lead; verify in packaged app if encountered |
| P2 | Windows code signing and updater behavior | Establish intended distribution/signing policy before publication |
| P2 | Port range and CI timing | Validate cold-start behavior and timeout handling |

## 13. Workflow orchestration findings (2026-11-20 research)

Research identified an orchestration defect in the three workflow files: a version tag starts two separate Windows builds, release publication does not depend on packaged-app acceptance, and release excludes standard Windows updater metadata.

Corrective direction:
- Establish one deterministic Windows build and one artifact identity.
- Make packaged acceptance consume the exact build artifact and matching commit.
- Make release publication depend on successful acceptance.
- Publish standard updater assets (including the Windows feed generated by the pinned Electron Builder configuration) in addition to any custom metadata.
- Preserve subprocess output and installer hash for diagnosis.
- Run repository gates before packaging, not as an unrelated post-build job.

Implemented in this pass:
- `nativephp-windows.yml`: repository gates now run before the Windows build; `.env` is normalized to production settings; artifact upload includes `latest.yml`; `ELECTRON_RUN_AS_NODE` is cleared at job scope.
- `nativephp-windows-acceptance.yml`: accepts `workflow_call`; checks out the triggering commit; uses a run-scoped concurrency key; allows reusable invocation; timeout increased.
- `release.yml`: now orchestrates the existing build and acceptance workflows and publishes only after both succeed, using the accepted build artifact.
- `tools/check-electron-env.php`: treats an empty `ELECTRON_RUN_AS_NODE` value as unset.

These are workflow-design corrections, not evidence of a runtime failure. Actual GitHub run logs are still required to identify the earliest failing command in any failed run.

## 10. Completion criteria

1. `composer run pre-native-build` succeeds.
2. Native provider resolves and window opens in a packaged app.
3. Secret gate assertions pass (403/403/200/200).
4. Windows installer builds and installs.
5. Packaged Playwright spec passes.
6. Release artifact identity matches the accepted build.
7. Updater metadata is valid for the configured provider.
8. Tag and release occur only with explicit owner authorization.

## 11. Recommendation

The existing NativePHP integration is the preferred path for v3.0.0. The work appears primarily verification- and release-oriented, conditional on the supplied plan accurately representing the current repository. This session has verified the local repository state and the NativePHP integration files named in the plan.

The next authorized implementation session should start with **G0 → N0 → N1 → N2** before building anything. Windows packaging follows only after runtime and security checks are satisfied. Publication remains separate and explicitly owner-gated.

No source code, dependencies, integration settings, workflows, Git references, or release artifacts were changed by this planning pass. No project-specific tests were represented as passed.

## 12. Authoritative references

- NativePHP: installation and compatibility, security, publishing.
- Playwright Electron API.
- electron-builder auto-update.
- GitHub Actions security guidance.
- NativePHP upstream issue #24 (troubleshooting lead for cached Laravel configuration/startup failures).
