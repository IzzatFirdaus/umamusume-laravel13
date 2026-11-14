param([string]$AppName = "Trainer Desk")

$root = Join-Path $env:APPDATA $AppName
$paths = @(
    @{ L = 'userData'; P = $root }
    @{ L = 'database'; P = (Join-Path $root 'database\database.sqlite') }
    @{ L = 'storage'; P = (Join-Path $root 'storage') }
    @{ L = 'cache'; P = (Join-Path $root 'bootstrap\cache') }
    @{ L = 'logs'; P = (Join-Path $root 'storage\logs') }
    @{ L = 'backups'; P = (Join-Path $root 'backups') }
)

foreach ($p in $paths) {
    "{0,-10} {1}  [{2}]" -f $p.L, $p.P, $(if (Test-Path $p.P) { 'exists' } else { 'missing' })
}

$db = Join-Path $root 'database\database.sqlite'
if (Test-Path $db) {
    "database.sqlite size: {0:N0} bytes" -f (Get-Item $db).Length
}
