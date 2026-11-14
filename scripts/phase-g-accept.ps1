$results = @()

$exe = Get-ChildItem nativephp\electron\dist\*.exe -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1
$results += @{ N = 'G.5 installer exists'; Ok = [bool]$exe }

if ($exe) {
    $hash = Get-FileHash -Algorithm SHA256 $exe.FullName
    "Artifact: $($exe.FullName)"
    "Size:     $('{0:N0}' -f $exe.Length) bytes"
    "Modified: $($exe.LastWriteTime)"
    "SHA-256:  $($hash.Hash)"

    $bytes = [System.IO.File]::ReadAllBytes($exe.FullName)[0..1]
    $header = [System.Text.Encoding]::ASCII.GetString($bytes)
    $results += @{ N = 'G.5 size > 40 MB'; Ok = ($exe.Length -gt 40MB) }
    $results += @{ N = 'G.5 MZ header'; Ok = ($header -eq 'MZ') }
    $results += @{ N = 'G.5 Nullsoft string present'; Ok = ([System.IO.File]::ReadAllBytes($exe.FullName).Length -gt 0 -and (Select-String -Path $exe.FullName -Pattern 'Nullsoft' -Encoding ascii -List -Quiet)) }
}

$appDir = "nativephp\electron\dist\win-unpacked\resources\app"
$results += @{ N = 'G.6 packaged .env checks'; Ok = ((Test-Path $appDir) -and ((powershell -NoProfile -File scripts\verify-packaged-env.ps1 -BuildDir $appDir) -eq 0)) }
$results += @{ N = 'G.7 gate tests logged'; Ok = [bool](Get-ChildItem logs\gate-test-*.log -ErrorAction SilentlyContinue) }
$results += @{ N = 'G.8 user-data paths traced'; Ok = [bool](Get-ChildItem logs\trace-*.log -ErrorAction SilentlyContinue) }
$results += @{ N = 'G.9 persistence logged'; Ok = [bool](Get-ChildItem logs\persistence-*.log -ErrorAction SilentlyContinue) }

$failed = @($results | Where-Object { -not $_.Ok })
foreach ($r in $results) { "[{0}] {1}" -f $(if ($r.Ok) { 'PASS' } else { 'FAIL' }), $r.N }

"---"
"Failed: $($failed.Count)"
exit $failed.Count
