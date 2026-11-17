<?php

/*
 * Bootstrap PHPUnit/Dusk untuk folder automated-testing.
 *
 * File tes ada di luar folder aplikasi (ecochallenge/), jadi autoloader Composer tidak tahu
 * namespace Tests\Browser\Fitur. Berkas ini memuat autoloader aplikasi lalu mendaftarkan
 * pemetaan tambahan: Tests\Browser\Fitur\* => automated-testing/tests/*.php
 */

require __DIR__.'/../ecochallenge/vendor/autoload.php';

spl_autoload_register(function (string $kelas): void {
    $awalan = 'Tests\\Browser\\Fitur\\';

    if (! str_starts_with($kelas, $awalan)) {
        return;
    }

    $berkas = __DIR__.'/tests/'.str_replace('\\', '/', substr($kelas, strlen($awalan))).'.php';

    if (is_file($berkas)) {
        require_once $berkas;
    }
});
