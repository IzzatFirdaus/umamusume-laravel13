param(
    [Parameter(Mandatory = $true)][int]$Port,
    [Parameter(Mandatory = $true)][string]$NativeSecret,
    [string]$HeaderName = 'X-NativePHP-Secret'
)

function Invoke-Probe([string]$Uri, [hashtable]$Headers) {
    try {
        $r = Invoke-WebRequest -Uri $Uri -Headers $Headers -UseBasicParsing -TimeoutSec 5
        return $r.StatusCode
    } catch [System.Net.WebException] {
        if ($_.Exception.Response) { return [int]$_.Exception.Response.StatusCode }
        return -1
    }
}

$base = "http://127.0.0.1:$Port"
$a = Invoke-Probe "$base/" @{}
$b = Invoke-Probe "$base/" @{ $HeaderName = 'wrong' }
$c = Invoke-Probe "$base/" @{ $HeaderName = $NativeSecret }

"no-secret    -> $a (expect 403)"
"wrong-secret -> $b (expect 403)"
"valid        -> $c (expect 200)"

if ($a -eq 403 -and $b -eq 403 -and $c -eq 200) { exit 0 } else { exit 1 }
