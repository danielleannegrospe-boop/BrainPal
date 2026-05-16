<?php

require_once __DIR__ . '/../vendor/autoload.php';

/* =========================
   ENV FILE PATH
========================= */
$envFile = __DIR__ . '/../.env';

/* =========================
   CHECK ENV FILE
========================= */
if (!file_exists($envFile)) {
    die(".env file not found");
}

/* =========================
   LOAD ENV VARIABLES
========================= */
$env = [];

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {

    if (strpos(trim($line), '#') === 0) {
        continue;
    }

    list($key, $value) = explode('=', $line, 2);

    $env[trim($key)] = trim($value);
}

/* =========================
   PUSHER CONFIG
========================= */
$options = [
    'cluster' => $env['PUSHER_APP_CLUSTER'],
    'useTLS' => true
];

/* =========================
   CREATE PUSHER INSTANCE
========================= */
$pusher = new Pusher\Pusher(
    $env['PUSHER_APP_KEY'],
    $env['PUSHER_APP_SECRET'],
    $env['PUSHER_APP_ID'],
    $options
);