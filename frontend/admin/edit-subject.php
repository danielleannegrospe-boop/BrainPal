<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET ID
========================= */
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: subjects.php");
    exit();
}

/* =========================
   FETCH SUBJECT
========================= */
$stmt = $conn->prepare("
    SELECT subjectID, subjectName, description
    FROM subjects
    WHERE subjectID = ? AND date_deleted IS NULL
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    die("Subject not found.");
}

/* =========================
   UPDATE SUBJECT (CSRF SECURED)
========================= */
if (isset($_POST['updateSubject'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $subjectName = trim($_POST['subjectName']);
    $description = trim($_POST['description']);

    if (!empty($subjectName)) {

        $stmt = $conn->prepare("
            UPDATE subjects
            SET subjectName = ?, description = ?
            WHERE subjectID = ?
        ");

        $stmt->bind_param("ssi", $subjectName, $description, $id);
        $stmt->execute();

        header("Location: subjects.php");
        exit();

    } else {
        $error = "Subject name is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Subject</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6fb;
            color: #333;
        }

        /* HEADER */
        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 18px 25px;
        }

        .container {
            padding: 25px;
            display: flex;
            justify-content: center;
        }

        /* CARD */
        .box {
            background: white;
            width: 100%;
            max-width: 520px;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            font-size: 13px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            transition: 0.2s;
        }

        textarea {
            resize: none;
            min-height: 120px;
        }

        input:focus, textarea:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 5px rgba(79,70,229,0.2);
        }

        button {
            width: 100%;
            margin-top: 18px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #3730a3;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
            color: #4f46e5;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

    </style>
</head>

<body>

<div class="header">
    <h2>Edit Subject</h2>
</div>

<div class="container">

<div class="box">

    <h2>Update Subject 📘</h2>

    <?php if (isset($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- CSRF TOKEN -->
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <label>Subject Name</label>
        <input type="text"
               name="subjectName"
               value="<?= htmlspecialchars($subject['subjectName']) ?>"
               required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($subject['description']) ?></textarea>

        <button type="submit" name="updateSubject">
            Update Subject
        </button>

    </form>

    <a class="back" href="subjects.php">
        ← Back to Subjects
    </a>

</div>

</div>

</body>
</html>