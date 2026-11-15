param(
    [Parameter(Mandatory = $true)][string]$BuildDir
)

$envPath = Join-Path $BuildDir ".env"
if (-not (Test-Path $envPath)) { Write-Error "No .env at $envPath"; exit 1 }

$pairs = Get-Content $envPath | Where-Object { $_ -notmatch '^\s*#' -and $_ -match '=' } | ForEach-Object {
    $k, $v = $_ -split '=', 2
    [PSCustomObject]@{ Key = $k.Trim(); Value = $v.Trim() }
}

function Get-Env($k) { ($pairs | Where-Object { $_.Key -eq $k }).Value }

$checks = @(
    @{ N = 'APP_ENV=production'; Ok = (Get-Env 'APP_ENV') -eq 'production' }
    @{ N = 'APP_DEBUG=false'; Ok = (Get-Env 'APP_DEBUG') -eq 'false' }
    @{ N = 'APP_KEY present'; Ok = [bool](Get-Env 'APP_KEY') }
    @{ N = 'APP_KEY base64: prefixed'; Ok = (Get-Env 'APP_KEY') -like 'base64:*' }
    @{ N = 'DB_CONNECTION=sqlite'; Ok = (Get-Env 'DB_CONNECTION') -eq 'sqlite' }
    @{ N = 'No dev secrets'; Ok = -not ($pairs | Where-Object { $_.Key -match 'AWS_|STRIPE_|MAIL_PASSWORD|PUSHER_' }) }
)

$failed = 0
foreach ($c in $checks) {
    if ($c.Ok) { Write-Host "[PASS] $($c.N)" -ForegroundColor Green }
    else { Write-Host "[FAIL] $($c.N)" -ForegroundColor Red; $failed++ }
}

"---"
"Checked: $envPath"
exit $failed
