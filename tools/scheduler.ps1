#requires -Version 5.1
<#
.SYNOPSIS
    Runs the TrainerDesk Laravel scheduler unattended on Windows.

.DESCRIPTION
    Starts `php artisan schedule:work` with duplicate-instance prevention,
    logging, and graceful shutdown handling. Intended for Windows Task Scheduler
    or manual invocation outside an IDE/coding-agent session.

    The scheduler is the only actor that runs while the TrainerDesk app is
    closed (Independent Training session expiry, synthetic health heartbeat).
    This script keeps it alive independently of Hermes, DSH, MCode, or any
    other IDE.

    PID file contract:
      The PID file (`storage/scheduler.pid`) stores "<PID>|<StartTime>".
      StartTime is recorded to detect PID reuse: if a new process happens to
      reuse a stale PID, its StartTime will not match and the file is treated
      as stale and reclaimed. Only the process that wrote the file may remove
      it (ownership-safe cleanup).

.PARAMETER PhpPath
    Path to the PHP executable. Defaults to `php` on PATH.

.PARAMETER ArtisanPath
    Path to the artisan file. Defaults to `<script-dir>/../artisan`.

.PARAMETER LogPath
    Path to the scheduler log file. Defaults to
    `<script-dir>/../storage/logs/scheduler.log`.

.PARAMETER PidPath
    Path to the PID file used for duplicate prevention. Defaults to
    `<script-dir>/../storage/scheduler.pid`.

.EXAMPLE
    .\tools\scheduler.ps1
    # Runs the scheduler in the foreground; Windows Task Scheduler can invoke
    # this with -NoExit -File to keep it alive.
#>
param(
    [string]$PhpPath = 'php',
    [string]$ArtisanPath = '',
    [string]$LogPath = '',
    [string]$PidPath = ''
)

$ErrorActionPreference = 'Stop'
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$repoRoot = Resolve-Path (Join-Path $scriptDir '..')

if (-not $ArtisanPath) { $ArtisanPath = Join-Path $repoRoot 'artisan' }
if (-not $LogPath)   { $LogPath   = Join-Path $repoRoot 'storage/logs/scheduler.log' }
if (-not $PidPath)   { $PidPath   = Join-Path $repoRoot 'storage/scheduler.pid' }

# Ensure log directory exists.
$logDir = Split-Path -Parent $LogPath
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }

function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    "$timestamp  $Message" | Tee-Object -FilePath $LogPath -Append
}

# Parse a PID file entry of the form "<PID>|<StartTime>".
# Returns $null when the file is absent or malformed.
function Read-PidFile {
    param([string]$Path)
    if (-not (Test-Path $Path)) { return $null }
    $content = Get-Content $Path -Raw -ErrorAction SilentlyContinue
    if ($content -match '^(\d+)\|(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$') {
        return @{ Pid = [int]$matches[1]; StartTime = $matches[2] }
    }
    return $null
}

# Returns true when the process with $pid is alive and its StartTime matches
# $expectedStartTime. Matching StartTime guards against PID reuse: a stale PID
# file whose PID was reassigned to a different process will not match.
function Test-SchedulerAlive {
    param([int]$Pid, [string]$ExpectedStartTime)
    try {
        $process = Get-Process -Id $Pid -ErrorAction Stop
        $actualStart = $process.StartTime.ToString('yyyy-MM-dd HH:mm:ss')
        return $actualStart -eq $ExpectedStartTime
    } catch {
        # Process no longer exists (or inaccessible) → not our live scheduler.
        return $false
    }
}

# Removes the PID file only when it still records our own PID (ownership-safe
# cleanup). Never removes a file written by a different scheduler instance.
function Remove-OurPidFile {
    param([int]$MyPid)
    if (-not (Test-Path $PidPath)) { return }
    $entry = Read-PidFile $PidPath
    if ($entry -and $entry.Pid -eq $MyPid) {
        Remove-Item $PidPath -Force -ErrorAction SilentlyContinue
    }
}

# Duplicate-instance prevention via PID file with PID-reuse detection.
$existing = Read-PidFile $PidPath
if ($existing -and (Test-SchedulerAlive $existing.Pid $existing.StartTime)) {
    Write-Log "Scheduler already running as PID $($existing.Pid). Exiting."
    exit 0
}

# Stale or absent PID file: reclaim it. This is ownership-safe because we only
# remove entries that are no longer a live scheduler process we started.
if ($existing) {
    Write-Log "Reclaiming stale PID file (recorded PID $($existing.Pid) not live or StartTime mismatched)."
    Remove-Item $PidPath -Force -ErrorAction SilentlyContinue
}

# Write our PID and start time so a later instance can detect reuse.
$myStart = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
"$PID|$myStart" | Set-Content $PidPath

# Cleanup on terminating error: remove our PID file only.
trap {
    Write-Log "Scheduler exiting due to terminating error: $_"
    Remove-OurPidFile $PID
    exit 1
}

# Graceful shutdown: remove our PID file when the host signals exit.
$exitHandler = {
    Write-Log "Scheduler shutting down gracefully (PID $PID)."
    Remove-OurPidFile $PID
}
$null = Register-EngineEvent -SourceIdentifier PowerShell.Exiting -Action $exitHandler

Write-Log "TrainerDesk scheduler starting (PID $PID)."
Write-Log "PHP: $PhpPath"
Write-Log "Artisan: $ArtisanPath"
Write-Log "Log: $LogPath"

# Run schedule:work in the foreground. The process blocks until interrupted;
# Windows Task Scheduler or the user stops it with Ctrl-C. When the wrapper
# exits, the stdout pipe to Tee-Object closes, which terminates the PHP child
# (schedule:work) via a broken pipe on its next write.
try {
    & $PhpPath $ArtisanPath schedule:work 2>&1 | Tee-Object -FilePath $LogPath -Append
    $exitCode = $LASTEXITCODE
    Write-Log "Scheduler exited with code $exitCode."
    exit $exitCode
} catch {
    Write-Log "Scheduler failed: $_"
    exit 1
} finally {
    Remove-OurPidFile $PID
}
