<#
.SYNOPSIS
    Reorganizes the docs/design-research/_scratch directory based on design research categories.
    Includes a dry-run mode to preview changes before applying.

.DESCRIPTION
    This script categorizes files into logical folders for design research:
    - analysis/color, analysis/components, analysis/scenarios, analysis/tokens
    - references/web, references/sheets
    - scripts/patch, scripts/probe, scripts/analysis, scripts/utils
    - logs/

.PARAMETER DryRun
    If set, only shows what would be moved without making changes.

.PARAMETER SourcePath
    Path to the _scratch directory (default: current directory).

.PARAMETER ShowDetails
    Show detailed output for each file operation.

.EXAMPLE
    .\reorganize-design-scratch.ps1 -DryRun -ShowDetails

.EXAMPLE
    .\reorganize-design-scratch.ps1  # Actually performs the reorganization
#>

[CmdletBinding()]
param(
    [Parameter()]
    [switch]$DryRun,

    [Parameter()]
    [string]$SourcePath = "D:\Projects\umamusume-laravel13\docs\design-research\_scratch",

    [Parameter()]
    [switch]$ShowDetails
)

$ErrorActionPreference = 'Stop'

# --- Configuration: File pattern to destination folder mapping ---
$Rules = @(
    # Analysis - Color
    @{ Pattern = 'accents.json';        Destination = 'analysis/color' }
    @{ Pattern = 'accents.py';          Destination = 'analysis/color' }
    @{ Pattern = 'clusters.json';       Destination = 'analysis/color' }
    @{ Pattern = 'cluster.py';          Destination = 'analysis/color' }
    @{ Pattern = 'colorprobes.json';    Destination = 'analysis/color' }
    @{ Pattern = 'colorprobes2.json';   Destination = 'analysis/color' }
    @{ Pattern = 'cs_bigclusters_1of1.png'; Destination = 'analysis/color' }
    @{ Pattern = 'cs_wide_1of2.png';    Destination = 'analysis/color' }
    @{ Pattern = 'cs_wide_2of2.png';    Destination = 'analysis/color' }

    # Analysis - Components (UI components)
    @{ Pattern = 'statband_*.png';      Destination = 'analysis/components' }
    @{ Pattern = 'rank_band_*.png';     Destination = 'analysis/components' }
    @{ Pattern = 'sparks_head_*.png';   Destination = 'analysis/components' }
    @{ Pattern = 'affinity_*.png';      Destination = 'analysis/components' }
    @{ Pattern = 'legacyslots_*.png';   Destination = 'analysis/components' }
    @{ Pattern = 'legacy_crops/*.png';  Destination = 'analysis/components' }

    # Analysis - Scenarios
    @{ Pattern = 'scenario_chips.png';  Destination = 'analysis/scenarios' }
    @{ Pattern = 'signatures.json';     Destination = 'analysis/scenarios' }

    # Analysis - Tokens
    @{ Pattern = 'tokens.json';         Destination = 'analysis/tokens' }
    @{ Pattern = 'tokens.py';           Destination = 'analysis/tokens' }

    # References - Web UI screenshots
    @{ Pattern = 'web/en-*.png';        Destination = 'references/web' }
    @{ Pattern = 'web/jp-*.png';        Destination = 'references/web' }
    @{ Pattern = 'web/global-*.png';    Destination = 'references/web' }
    @{ Pattern = 'web/*';               Destination = 'references/web' }

    # References - Sheets (19 sheets)
    @{ Pattern = 'sheet_*of19.png';     Destination = 'references/sheets' }

    # Scripts - Patch/fix scripts
    @{ Pattern = 'patch_*.py';          Destination = 'scripts/patch' }

    # Scripts - Probe/exploration
    @{ Pattern = 'probe.py';            Destination = 'scripts/probe' }
    @{ Pattern = 'probe2.py';           Destination = 'scripts/probe' }
    @{ Pattern = 'scenario_probe.py';   Destination = 'scripts/probe' }
    @{ Pattern = 'sheet.py';            Destination = 'scripts/probe' }

    # Scripts - Core analysis
    @{ Pattern = 'cluster.py';          Destination = 'scripts/analysis' }
    @{ Pattern = 'triage.py';           Destination = 'scripts/analysis' }

    # Scripts - Utilities
    @{ Pattern = 'fix_*.py';            Destination = 'scripts/utils' }

    # Logs
    @{ Pattern = 'triage.err';          Destination = 'logs' }
)

# --- Helper functions ---

function Get-Destination {
    param([string]$RelativePath)
    foreach ($rule in $Rules) {
        $pattern = $rule.Pattern
        $dest = $rule.Destination

        # Convert glob to regex
        $regex = '^' + [regex]::Escape($pattern).Replace('\*', '.*').Replace('\?', '.') + '$'
        if ($RelativePath -match $regex) {
            return $dest
        }
    }
    return $null
}

# --- Main logic ---

$source = Get-Item -LiteralPath $SourcePath
if (-not $source.Exists) {
    Write-Error "Source path does not exist: $SourcePath"
    exit 1
}

Write-Host "Scanning: $($source.FullName)" -ForegroundColor Cyan
if ($DryRun) {
    Write-Host "=== DRY RUN MODE - No changes will be made ===" -ForegroundColor Yellow
}

# Collect all files (including in subdirectories)
$allFiles = Get-ChildItem -LiteralPath $source.FullName -Recurse -File |
    Where-Object { $_.FullName -notlike "*\REORGANIZATION_PLAN.md" -and $_.FullName -notlike "*\reorganize-design-scratch.ps1" -and $_.FullName -notlike "*\debug-paths.ps1" } |
    ForEach-Object {
        $rel = $_.FullName.Substring($source.FullName.Length + 1).Replace('\', '/')
        $dest = Get-Destination -RelativePath $rel
        if ($dest) {
            [pscustomobject]@{
                Source      = $_.FullName
                Relative    = $rel
                Destination = $dest
                Size        = $_.Length
            }
        }
    }

if (-not $allFiles) {
    Write-Host "No files matched reorganization rules." -ForegroundColor Green
    exit 0
}

# Group by destination
$groups = $allFiles | Group-Object Destination

Write-Host "`n=== Reorganization Plan ===" -ForegroundColor Cyan
foreach ($group in $groups) {
    $count = $group.Count
    $totalSize = ($group.Group | Measure-Object Size -Sum).Sum
    $sizeMB = [math]::Round($totalSize / 1MB, 2)
    Write-Host "`n$($group.Name)  ($count files, ${sizeMB} MB)" -ForegroundColor Green
    if ($ShowDetails) {
        $group.Group | Sort-Object Relative | ForEach-Object {
            $kb = [math]::Round($_.Size / 1KB, 1)
            Write-Host "  $_ ($kb KB)" -ForegroundColor Gray
        }
    }
}

if ($DryRun) {
    Write-Host "`n=== DRY RUN COMPLETE ===" -ForegroundColor Yellow
    Write-Host "Run without -DryRun to apply changes." -ForegroundColor Yellow
    exit 0
}

# --- Apply changes ---
Write-Host "`n=== Applying Changes ===" -ForegroundColor Cyan

$moved = 0
$errors = 0

foreach ($file in $allFiles) {
    $destDir = Join-Path $source.FullName $file.Destination
    $destFile = Join-Path $destDir (Split-Path $file.Relative -Leaf)

    # Create destination directory
    if (-not (Test-Path -LiteralPath $destDir)) {
        try {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
            if ($ShowDetails) { Write-Host "Created: $destDir" -ForegroundColor DarkGray }
        }
        catch {
            $errMsg = $Error[0].ToString()
            Write-Error ("Failed to create directory {0}: {1}" -f $destDir, $errMsg)
            $errors++
            continue
        }
    }

    # Handle conflicts
    if (Test-Path -LiteralPath $destFile) {
        $base = [System.IO.Path]::GetFileNameWithoutExtension($destFile)
        $ext = [System.IO.Path]::GetExtension($destFile)
        $counter = 1
        $newName = $destFile
        while (Test-Path -LiteralPath $newName) {
            $newName = Join-Path $destDir ("{0}_{1}{2}" -f $base, $counter, $ext)
            $counter++
        }
        $destFile = $newName
        if ($ShowDetails) { Write-Host "Conflict resolved: $([System.IO.Path]::GetFileName($destFile))" -ForegroundColor Yellow }
    }

    try {
        Move-Item -LiteralPath $file.Source -Destination $destFile -Force
        $moved++
        if ($ShowDetails) { Write-Host "Moved: $($file.Relative) -> $($file.Destination)/" -ForegroundColor Gray }
    }
    catch {
        $errMsg = $Error[0].ToString()
        Write-Error ("Failed to move {0}: {1}" -f $file.Relative, $errMsg)
        $errors++
    }
}

# Clean up empty legacy directories
$legacyDirs = @("legacy_crops", "web")
foreach ($d in $legacyDirs) {
    $dirPath = Join-Path $source.FullName $d
    if (Test-Path -LiteralPath $dirPath) {
        try {
            $items = Get-ChildItem -LiteralPath $dirPath -Force
            if (-not $items) {
                Remove-Item -LiteralPath $dirPath -Force
                if ($ShowDetails) { Write-Host "Removed empty directory: $d" -ForegroundColor DarkGray }
            }
        }
        catch { }
    }
}

Write-Host "`n=== Summary ===" -ForegroundColor Cyan
Write-Host "Files moved: $moved" -ForegroundColor Green
if ($errors -gt 0) {
    Write-Host "Errors: $errors" -ForegroundColor Red
}
Write-Host "Done." -ForegroundColor Green