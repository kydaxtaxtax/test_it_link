#!/usr/bin/env php
<?php
/**
 * Router script для PHP built-in server.
 * Позволяет слушать на 0.0.0.0:8080 для Docker сети.
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);
$decodedUri = $uri;

$decodedUri = ltrim($decodedUri, '/');

if ($decodedUri !== '' && file_exists(__DIR__ . '/web/' . $decodedUri)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/web/index.php';

require __DIR__ . '/web/index.php';