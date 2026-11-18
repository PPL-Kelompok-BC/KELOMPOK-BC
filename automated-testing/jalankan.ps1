<#
.SYNOPSIS
  Menjalankan tes otomatis Dusk (browser tampil, klik dan ketik otomatis).

.EXAMPLE
  .\jalankan.ps1                                   # SEMUA tes
  .\jalankan.ps1 -Anggota Seto                     # semua tes milik satu anggota (nama = Jira)
  .\jalankan.ps1 -Berkas Jonathan_PBI04_LoginTest  # satu berkas
  .\jalankan.ps1 -Berkas Jonathan_PBI04_LoginTest -Filter test_admin_masuk   # satu tes saja
  .\jalankan.ps1 -Anggota Alexander -Jeda 1500     # lebih pelan agar mudah ditonton
  .\jalankan.ps1 -TanpaTampil                      # tanpa jendela browser (headless)

Anggota: Jonathan, Fakhrezi, Najmi, Alexander, Hafidz, Seto

Skrip ini berdiri sendiri: ia menyalakan server tes KHUSUS di port 8001 dengan basis data
ecochallenge\database\dusk.sqlite, lalu mematikannya lagi. Server pengembanganmu (port 8000),
basis data MySQL/SQLite aslimu, dan isi .env tidak disentuh. Tidak perlu membuat .env.dusk.local.
#>
param(
    [ValidateSet('', 'Jonathan', 'Fakhrezi', 'Najmi', 'Alexander', 'Hafidz', 'Seto')]
    [string]$Anggota = '',
    [string]$Berkas = '',
    [string]$Filter = '',
    [int]$Jeda = 700,
    [int]$Port = 8001,
    [switch]$TanpaTampil
)

$ErrorActionPreference = 'Stop'
$akar = Split-Path -Parent $MyInvocation.MyCommand.Path
$app = Join-Path (Split-Path -Parent $akar) 'ecochallenge'

if (-not (Test-Path (Join-Path $app 'artisan'))) {
    Write-Host "Folder aplikasi tidak ditemukan: $app" -ForegroundColor Red
    exit 1
}
if (-not (Test-Path (Join-Path $app 'phpunit.dusk.xml'))) {
    Write-Host 'phpunit.dusk.xml tidak ada. Pastikan kamu berada di branch yang berisi folder automated-testing (git pull).' -ForegroundColor Red
    exit 1
}

Push-Location $app

# Basis data khusus tes (dikosongkan tiap tes oleh Dusk).
$dbTes = Join-Path $app 'database\dusk.sqlite'
if (-not (Test-Path $dbTes)) { New-Item -ItemType File -Force $dbTes | Out-Null }

# Variabel lingkungan untuk proses tes DAN server tes. Nilai ini mengalahkan isi .env /
# .env.dusk.local, jadi tes selalu memakai SQLite dan port yang benar di laptop mana pun.
$nilai = @{
    'APP_URL'                = "http://127.0.0.1:$Port"
    'DB_CONNECTION'          = 'sqlite'
    'DB_DATABASE'            = $dbTes
    'SESSION_DRIVER'         = 'file'
    'CACHE_STORE'            = 'file'
    'QUEUE_CONNECTION'       = 'sync'
    'DUSK_JEDA'              = "$Jeda"
    'DUSK_HEADLESS_DISABLED' = $(if ($TanpaTampil) { 'false' } else { 'true' })
    'DUSK_START_MAXIMIZED'   = 'true'
}
$lama = @{}
foreach ($k in $nilai.Keys) {
    $lama[$k] = [Environment]::GetEnvironmentVariable($k, 'Process')
    [Environment]::SetEnvironmentVariable($k, $nilai[$k], 'Process')
}

# Konfigurasi yang ter-cache membuat .env dan variabel di atas diabaikan.
$ErrorActionPreference = 'Continue'
php artisan config:clear 2>$null | Out-Null

# Tentukan sasaran: satu berkas, satu anggota, atau semuanya.
$argumen = @('artisan', 'dusk')
if ($Berkas -ne '') {
    $nama = $Berkas -replace '\.php$', ''
    $ketemu = Get-ChildItem (Join-Path $akar 'tests') -Recurse -Filter "$nama.php" | Select-Object -First 1
    if (-not $ketemu) {
        $ketemu = Get-ChildItem (Join-Path $app 'tests\Browser') -Recurse -Filter "$nama.php" | Select-Object -First 1
    }
    if (-not $ketemu) {
        Write-Host "Berkas tes '$nama' tidak ditemukan." -ForegroundColor Red
        Pop-Location
        exit 1
    }
    $argumen += (Resolve-Path -Relative $ketemu.FullName) -replace '\\', '/'
} elseif ($Anggota -ne '') {
    $argumen += "--testsuite=$Anggota"
} else {
    $argumen += '--testsuite=Fitur'
}
if ($Filter -ne '') { $argumen += "--filter=$Filter" }

# Server tes khusus (php -S langsung, agar variabel lingkungan di atas ikut terbawa).
# Router sendiri (automated-testing\router.php) yang menunjuk ke ecochallenge\public dengan path tetap.
$router = Join-Path $akar 'router.php'
$server = $null
$kode = 1
try {
    $terpakai = $false
    try { $terpakai = (Test-NetConnection -ComputerName 127.0.0.1 -Port $Port -WarningAction SilentlyContinue).TcpTestSucceeded } catch {}
    if ($terpakai) {
        Write-Host "Port $Port sudah dipakai program lain. Tutup programnya atau jalankan dengan -Port 8002." -ForegroundColor Red
        exit 1
    }

    Write-Host "Menyalakan server tes di http://127.0.0.1:$Port (basis data: database\dusk.sqlite) ..." -ForegroundColor Cyan
    # Router Laravel mencari index.php di folder kerja, jadi server dijalankan DARI folder public
    # dengan path lengkap (aman untuk path yang mengandung spasi, mis. "E:\DATA C\...").
    $publik = Join-Path $app 'public'
    # display_errors=0: di PHP 8.4+ peringatan "PDO::MYSQL_ATTR_SSL_CA is deprecated" dari
    # config/database.php bisa tercetak ke halaman sebelum header, sehingga cookie sesi gagal
    # terkirim dan login di tes tidak pernah berhasil. Peringatan tetap dicatat Laravel ke log.
    $server = Start-Process php -ArgumentList '-d', 'display_errors=0', '-d', 'display_startup_errors=0', '-S', "127.0.0.1:$Port", '-t', "`"$publik`"", "`"$router`"" -WorkingDirectory $publik -PassThru -WindowStyle Hidden
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep 1
        try { if ((Test-NetConnection -ComputerName 127.0.0.1 -Port $Port -WarningAction SilentlyContinue).TcpTestSucceeded) { break } } catch {}
    }

    & php @argumen
    $kode = $LASTEXITCODE
}
finally {
    if ($server) { Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue }
    foreach ($k in $lama.Keys) { [Environment]::SetEnvironmentVariable($k, $lama[$k], 'Process') }
    Pop-Location
}

exit $kode
