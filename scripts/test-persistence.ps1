param(
    [Parameter(Mandatory = $true)][string]$Exe,
    [Parameter(Mandatory = $true)][string]$SqlitePath,
    [Parameter(Mandatory = $true)][string]$Table,
    [int]$PreinstallSeconds = 20
)

function Row-Count {
    if (-not (Test-Path $SqlitePath)) { return -1 }
    php -r "try { `$db = new PDO('sqlite:'.getenv('SQLITE_PATH')); `$n = `$db->query('SELECT COUNT(*) FROM '.getenv('TABLE'))->fetchColumn(); echo `$n; } catch (Exception `$e) { echo -1; }"
}

function Launch-And-Wait {
    Start-Process $Exe
    Start-Sleep -Seconds $PreinstallSeconds
    Get-Process | Where-Object { $_.Path -eq $Exe } | Stop-Process -Force
    Start-Sleep -Seconds 2
}

$env:SQLITE_PATH = $SqlitePath
$env:TABLE = $Table

$before = Row-Count
Launch-And-Wait
$afterFirst = Row-Count
Launch-And-Wait
$afterSecond = Row-Count

"before=$before  after1=$afterFirst  after2=$afterSecond"

"[UNTESTED] upgrade: run a newer .exe over this install and re-read the row count"
"[UNTESTED] uninstall: run the uninstaller and check whether user data survives"

if ($afterFirst -gt $before -and $afterSecond -ge $afterFirst) { exit 0 } else { exit 1 }
