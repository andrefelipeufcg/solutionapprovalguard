<?php

if (!defined('GLPI_ROOT')) {
    $inc = __DIR__ . '/../../../inc/includes.php';
    if (!file_exists($inc)) {
        $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php';
    }
    if (!file_exists($inc)) {
        $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php';
    }
    if (!file_exists($inc)) {
        http_response_code(503);
        exit;
    }
    include $inc;
}

Session::checkRight('config', UPDATE);

// Serve the fixed documentation image without exposing arbitrary file paths.
$image = __DIR__ . '/../docs/screenshots/screenshot3.png';
if (!is_file($image)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/png');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($image);
exit;