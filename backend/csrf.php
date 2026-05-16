<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   GENERATE CSRF TOKEN
========================= */
function generateCSRF() {

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/* =========================
   VALIDATE CSRF TOKEN
========================= */
function validateCSRF($token) {

    if (
        !isset($_SESSION['csrf_token']) ||
        empty($token)
    ) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}