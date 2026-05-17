<?php
session_start();

require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

/* =========================
   AUTH GUARD
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   DELETE SUBJECT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF VALIDATION
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $subjectID = (int) ($_POST['subjectID'] ?? 0);

    if ($subjectID > 0) {

        $stmt = $conn->prepare("
            UPDATE subjects
            SET date_deleted = NOW()
            WHERE subjectID = ?
        ");

        $stmt->bind_param("i", $subjectID);

        if ($stmt->execute()) {
            header("Location: subjects.php");
            exit();
        } else {
            die("Failed to delete subject.");
        }

    } else {
        die("Invalid subject ID.");
    }

} else {
    header("Location: subjects.php");
    exit();
}
?>