<?php

$host = "casestudy";
$user = "root";
$password = "";
$database = "brainpal-quiz";
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>