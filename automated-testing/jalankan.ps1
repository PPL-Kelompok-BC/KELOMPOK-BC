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
  .\jalankan.ps1 -Berkas LeaderboardBrowserTest -Filter test_pbi19 -MySQL -SimpanData
                                                   # pakai MySQL XAMPP (basis data ecochallenge_dusk)
                                                   # dan data tes TIDAK dihapus, bisa dilihat di phpMyAdmin

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
    [switch]$TanpaTampil,
    [switch]$MySQL,                       # pakai MySQL (XAMPP) alih-alih SQLite
    [switch]$SimpanData,                  # data tes terakhir tidak dihapus (untuk diperiksa)
    [string]$NamaDb = 'ecochallenge_dusk',
    [int]$PortDb = 0,                     # 0 = deteksi otomatis (3306, lalu 3307)
    [string]$UserDb = 'root',
    [string]$SandiDb = ''
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

# Basis data khusus tes (dikosongkan di awal tiap tes oleh Dusk).
$dbTes = Join-Path $app 'database\dusk.sqlite'
if (-not $MySQL -and -not (Test-Path $dbTes)) { New-Item -ItemType File -Force $dbTes | Out-Null }

if ($MySQL) {
    # XAMPP biasanya memakai 3306, tetapi bisa 3307 bila 3306 sudah dipakai program lain.
    $kandidat = $(if ($PortDb -gt 0) { @($PortDb) } else { @(3306, 3307) })
    $PortDb = 0
    foreach ($pt in $kandidat) {
        $buka = $false
        try { $buka = (Test-NetConnection -ComputerName 127.0.0.1 -Port $pt -WarningAction SilentlyContinue).TcpTestSucceeded } catch {}
        if ($buka) { $PortDb = $pt; break }
    }
    if ($PortDb -eq 0) {
        Write-Host 'MySQL belum menyala. Buka XAMPP Control Panel lalu Start MySQL, kemudian ulangi.' -ForegroundColor Red
        Pop-Location
        exit 1
    }
    # Buat basis data khusus tes bila belum ada (basis data aplikasi aslimu tidak disentuh).
    $buat = "try { `$p = new PDO('mysql:host=127.0.0.1;port=$PortDb', '$UserDb', '$SandiDb'); `$p->exec('CREATE DATABASE IF NOT EXISTS ``$NamaDb`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo 'ok'; } catch (Throwable `$e) { echo `$e->getMessage(); exit(1); }"
    # Peringatan PHP di stderr (mis. "exif already loaded") jangan dianggap error PowerShell.
    $ErrorActionPreference = 'Continue'
    $hasil = php -r $buat 2>$null
    $ErrorActionPreference = 'Stop'
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Gagal membuat basis data MySQL '$NamaDb': $hasil" -ForegroundColor Red
        Pop-Location
        exit 1
    }
    Write-Host "Basis data MySQL: $NamaDb di port $PortDb (lihat di http://localhost/phpmyadmin)" -ForegroundColor Cyan
}

# Variabel lingkungan untuk proses tes DAN server tes. Nilai ini mengalahkan isi .env /
# .env.dusk.local, jadi tes selalu memakai SQLite dan port yang benar di laptop mana pun.
$nilai = @{
    'APP_ENV'                = 'local'   # rute bantu Dusk (/_dusk/login, /_dusk/logout) hanya aktif di luar production
    'APP_URL'                = "http://127.0.0.1:$Port"
    'DB_CONNECTION'          = $(if ($MySQL) { 'mysql' } else { 'sqlite' })
    'DB_HOST'                = '127.0.0.1'
    'DB_PORT'                = $(if ($MySQL) { "$PortDb" } else { '3306' })
    'DB_DATABASE'            = $(if ($MySQL) { $NamaDb } else { $dbTes })
    'DB_USERNAME'            = $UserDb
    'DB_PASSWORD'            = $SandiDb
    'DUSK_SIMPAN_DATA'       = $(if ($SimpanData) { 'true' } else { 'false' })
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
# PHPUnit dijalankan LANGSUNG dengan phpunit.dusk.xml, bukan lewat "php artisan dusk". Perintah itu
# menukar .env dengan .env.dusk.local lalu memuatnya ulang secara mutable, sehingga variabel di
# atas tertimpa (tes tersesat ke MySQL atau http://localhost bila laptop punya .env.dusk.local lama).
$argumen = @('vendor/phpunit/phpunit/phpunit', '--configuration', 'phpunit.dusk.xml', '--testdox', '--colors=always')
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

    $labelDb = $(if ($MySQL) { "MySQL $NamaDb" } else { 'database\dusk.sqlite' })
    Write-Host "Menyalakan server tes di http://127.0.0.1:$Port (basis data: $labelDb) ..." -ForegroundColor Cyan
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
    if ($SimpanData) {
        Write-Host "Data tes terakhir disimpan di $labelDb. Tabel yang menarik: users (total_points), claims (status, points_awarded, co2e_awarded_gram), streaks." -ForegroundColor Cyan
    }
}
finally {
    if ($server) { Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue }
    foreach ($k in $lama.Keys) { [Environment]::SetEnvironmentVariable($k, $lama[$k], 'Process') }
    Pop-Location
}

exit $kode
