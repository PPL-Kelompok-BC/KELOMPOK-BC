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

Syarat: sudah mengikuti langkah 1 sampai 4 di README.md (sekali saja per komputer).
#>
param(
    [ValidateSet('', 'Jonathan', 'Fakhrezi', 'Najmi', 'Alexander', 'Hafidz', 'Seto')]
    [string]$Anggota = '',
    [string]$Berkas = '',
    [string]$Filter = '',
    [int]$Jeda = 700,
    [switch]$TanpaTampil
)

$ErrorActionPreference = 'Stop'
$akar = Split-Path -Parent $MyInvocation.MyCommand.Path
$app = Join-Path (Split-Path -Parent $akar) 'ecochallenge'

# Pindah ke folder aplikasi hanya selama skrip berjalan; posisi terminal dikembalikan di akhir.
Push-Location $app

if (-not (Test-Path '.env.dusk.local')) {
    Pop-Location
    Write-Host 'Belum ada ecochallenge\.env.dusk.local. Salin dari automated-testing\.env.dusk.local.example (lihat README langkah 3).' -ForegroundColor Red
    exit 1
}

# Pengaturan tampilan lewat variabel lingkungan (dibaca oleh tes).
$env:DUSK_JEDA = "$Jeda"
if ($TanpaTampil) { $env:DUSK_HEADLESS_DISABLED = 'false' } else { $env:DUSK_HEADLESS_DISABLED = 'true' }

# Tentukan sasaran: satu berkas, satu anggota, atau semuanya.
$argumen = @('artisan', 'dusk')
if ($Berkas -ne '') {
    $nama = $Berkas -replace '\.php$', ''
    $ketemu = Get-ChildItem (Join-Path $akar 'tests') -Recurse -Filter "$nama.php" | Select-Object -First 1
    if (-not $ketemu) {
        Write-Host "Berkas tes '$nama' tidak ditemukan di automated-testing\tests." -ForegroundColor Red
        exit 1
    }
    $argumen += '../automated-testing/tests/' + ($ketemu.FullName.Substring((Join-Path $akar 'tests').Length + 1) -replace '\\', '/')
} elseif ($Anggota -ne '') {
    $argumen += "--testsuite=$Anggota"
} else {
    $argumen += '--testsuite=Fitur'
}
if ($Filter -ne '') { $argumen += "--filter=$Filter" }

# Nyalakan server aplikasi di port 8000 bila belum ada.
$server = $null
$hidup = $false
try { $hidup = (Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -WarningAction SilentlyContinue).TcpTestSucceeded } catch {}
if (-not $hidup) {
    Write-Host 'Menyalakan server di http://127.0.0.1:8000 ...' -ForegroundColor Cyan
    $server = Start-Process php -ArgumentList 'artisan', 'serve', '--port=8000', '--no-reload' -PassThru -WindowStyle Hidden
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep 1
        try { if ((Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -WarningAction SilentlyContinue).TcpTestSucceeded) { break } } catch {}
    }
}

try {
    # Peringatan PHP di stderr (mis. modul exif dimuat dua kali) bukan kegagalan tes.
    $ErrorActionPreference = 'Continue'
    & php @argumen
    $kode = $LASTEXITCODE
}
finally {
    if ($server) { Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue }
    Pop-Location
}

exit $kode
