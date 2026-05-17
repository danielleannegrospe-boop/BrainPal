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
   DELETE LESSON
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF VALIDATION
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $lessonID = (int) ($_POST['lessonID'] ?? 0);

    if ($lessonID > 0) {

        $stmt = $conn->prepare("
            UPDATE lessons
            SET date_deleted = NOW()
            WHERE lessonID = ?
        ");

        $stmt->bind_param("i", $lessonID);

        if ($stmt->execute()) {
            header("Location: lessons.php");
            exit();
        } else {
            die("Failed to delete lesson.");
        }

    } else {
        die("Invalid lesson ID.");
    }

} else {
    header("Location: lessons.php");
    exit();
}
?>