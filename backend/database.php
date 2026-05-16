<?php

$env = parse_ini_file(__DIR__ . '/../.env');

$host = $env['DB_HOST'];
$user = $env['DB_USER'];
$password = $env['DB_PASS'];
$database = $env['DB_NAME'];
$port = $env['DB_PORT'];

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}