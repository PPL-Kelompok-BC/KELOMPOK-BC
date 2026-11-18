<?php

/*
 * Router untuk server tes (php -S) yang dinyalakan jalankan.ps1.
 *
 * Sama seperti router bawaan Laravel (vendor/.../Foundation/resources/server.php), tetapi folder
 * public ditentukan dari lokasi berkas ini, bukan dari folder kerja proses PHP. Router bawaan
 * memakai getcwd(), dan di sebagian laptop folder kerjanya bukan "public" sehingga muncul error
 * "Failed opening required '...ecochallenge/index.php'".
 */

$publicPath = realpath(__DIR__.'/../ecochallenge/public');

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

// Berkas statis (CSS, JS, gambar) dilayani langsung oleh server bawaan PHP.
if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $publicPath.DIRECTORY_SEPARATOR.'index.php';

chdir($publicPath);

require_once $publicPath.DIRECTORY_SEPARATOR.'index.php';
