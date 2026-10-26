param([string]$ProjectName = 'ecochallenge')
$ErrorActionPreference = 'Stop'
# Run this from the extracted package. It only creates a NEW Laravel project.
if ($ProjectName -notmatch '^[a-zA-Z0-9_-]+$') { throw 'Nama folder hanya boleh huruf, angka, tanda minus, dan underscore.' }
$Target = Join-Path $PSScriptRoot $ProjectName
if (Test-Path $Target) { throw "Folder $Target sudah ada. Gunakan nama baru agar pekerjaan lama tidak tertimpa." }
foreach ($CommandName in @('php', 'composer', 'node', 'npm')) {
    if (-not (Get-Command $CommandName -ErrorAction SilentlyContinue)) { throw "$CommandName belum ditemukan di PATH. Baca MULAI-DI-SINI.md." }
}
& php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);'
if ($LASTEXITCODE -ne 0) { throw 'Diperlukan PHP 8.2 atau lebih baru.' }
$NodeVersion = [version](& node -p 'process.versions.node')
$NodeOkay = ($NodeVersion.Major -eq 20 -and $NodeVersion.Minor -ge 19) -or ($NodeVersion.Major -eq 22 -and $NodeVersion.Minor -ge 12) -or ($NodeVersion.Major -gt 22)
if (-not $NodeOkay) { throw 'Gunakan Node.js 22.12+ (atau Node 20.19+).' }
Write-Host 'Membuat proyek Laravel 12 baru...' -ForegroundColor Green
& composer create-project 'laravel/laravel:^12.0' $Target --no-interaction
if ($LASTEXITCODE -ne 0) { throw 'Composer gagal. Lihat pesan error di atas. Folder parsial tidak dihapus otomatis.' }
Get-ChildItem -LiteralPath (Join-Path $PSScriptRoot 'overlay') -Force | ForEach-Object {
    Copy-Item -LiteralPath $_.FullName -Destination $Target -Recurse -Force
}
$RouteFile = Join-Path $Target 'routes\web.php'
Add-Content -LiteralPath $RouteFile -Value "`nrequire __DIR__.'/eco-gui.php';" -Encoding UTF8
$EnvPath = Join-Path $Target '.env'
$EnvContent = Get-Content -LiteralPath $EnvPath -Raw
$EnvContent = $EnvContent -replace '(?m)^APP_NAME=.*$', 'APP_NAME=EcoChallenge'
$EnvContent = $EnvContent -replace '(?m)^SESSION_DRIVER=.*$', 'SESSION_DRIVER=file'
$EnvContent = $EnvContent -replace '(?m)^CACHE_STORE=.*$', 'CACHE_STORE=file'
$EnvContent = $EnvContent -replace '(?m)^QUEUE_CONNECTION=.*$', 'QUEUE_CONNECTION=sync'
[System.IO.File]::WriteAllText($EnvPath, $EnvContent, [System.Text.UTF8Encoding]::new($false))
Push-Location $Target
try {
    & npm.cmd ci --no-audit --no-fund
    if ($LASTEXITCODE -ne 0) { throw 'Instalasi npm gagal. Jalankan npm ci di folder proyek setelah memperbaiki error.' }
    & npm.cmd run build
    if ($LASTEXITCODE -ne 0) { throw 'Build frontend gagal. Lihat error di atas.' }
    & php artisan optimize:clear
    if ($LASTEXITCODE -ne 0) { throw 'Laravel belum berhasil dijalankan. Lihat error di atas.' }
} finally { Pop-Location }
Write-Host "`nSetup selesai. Jalankan:" -ForegroundColor Green
Write-Host "cd $Target"
Write-Host 'php artisan serve'
Write-Host 'Lalu buka http://127.0.0.1:8000'
Write-Host 'Untuk mengedit GUI, jalankan npm run dev pada terminal kedua.'
