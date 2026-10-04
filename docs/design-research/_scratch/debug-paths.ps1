$source = Get-Item -LiteralPath 'D:\Projects\umamusume-laravel13\docs\design-research\_scratch'
$files = Get-ChildItem -LiteralPath $source.FullName -Recurse -File | Where-Object { $_.FullName -notlike '*REORGANIZATION_PLAN.md' -and $_.FullName -notlike '*reorganize-design-scratch.ps1' }
$files | ForEach-Object {
    $rel = $_.FullName.Substring($source.FullName.Length + 1)
    Write-Host "Relative: $rel"
}