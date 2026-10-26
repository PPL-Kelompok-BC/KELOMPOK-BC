param([Parameter(Mandatory=$true)][string]$ProjectPath)
$ErrorActionPreference = 'Stop'
$Target = (Resolve-Path -LiteralPath $ProjectPath).Path
if (-not (Test-Path (Join-Path $Target 'artisan'))) { throw 'Pilih folder Laravel yang berisi file artisan.' }
$RouteFile = Join-Path $Target 'routes\web.php'
if (-not (Test-Path $RouteFile)) { throw 'routes/web.php tidak ditemukan.' }
$RouteContent = Get-Content -LiteralPath $RouteFile -Raw
if (-not $RouteContent.Contains('eco-gui.php')) { throw 'Proyek ini belum memakai route EcoChallenge. Ikuti integrasi manual agar route lama tidak tertimpa.' }
$Backup = Join-Path $Target ('eco-gui-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $Backup | Out-Null
$Items = @('resources\views\eco\app.blade.php','resources\css\app.css','resources\js\app.js','resources\js\demo-state.js','resources\js\social-state.js','public\build','public\eco-media')
foreach ($Relative in $Items) {
    $Source = Join-Path (Join-Path $PSScriptRoot 'overlay') $Relative
    $Destination = Join-Path $Target $Relative
    if (Test-Path -LiteralPath $Destination) {
        $BackupPath = Join-Path $Backup $Relative
        New-Item -ItemType Directory -Force -Path (Split-Path $BackupPath -Parent) | Out-Null
        Copy-Item -LiteralPath $Destination -Destination $BackupPath -Recurse -Force
    }
    if (Test-Path -LiteralPath $Source -PathType Container) {
        New-Item -ItemType Directory -Force -Path $Destination | Out-Null
        Get-ChildItem -LiteralPath $Source -Force | ForEach-Object { Copy-Item -LiteralPath $_.FullName -Destination $Destination -Recurse -Force }
    } else {
        New-Item -ItemType Directory -Force -Path (Split-Path $Destination -Parent) | Out-Null
        Copy-Item -LiteralPath $Source -Destination $Destination -Force
    }
}
$HotFile = Join-Path $Target 'public\hot'
if (Test-Path -LiteralPath $HotFile) { Move-Item -LiteralPath $HotFile -Destination (Join-Path $Backup 'hot') }
Write-Host 'GUI Social Edition tersalin. File sebelumnya dicadangkan di:' -ForegroundColor Green
Write-Host $Backup
Write-Host 'Build CSS/JS sudah disertakan, tidak perlu npm untuk mencoba tampilan.'
Write-Host "Buka terminal di $Target, lalu jalankan:"
Write-Host 'php artisan view:clear'
Write-Host 'php artisan serve'
Write-Host 'Buka http://127.0.0.1:8000/#feed'
