<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: lessons.php");
    exit();
}

/* =========================
   GET LESSON
========================= */
$stmt = $conn->prepare("
    SELECT * FROM lessons
    WHERE lessonID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) {
    die("Lesson not found.");
}

/* =========================
   UPDATE LESSON (CSRF SECURED)
========================= */
if (isset($_POST['updateLesson'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $title = trim($_POST['lessonTitle'] ?? '');
    $desc  = trim($_POST['description'] ?? '');

    if (!empty($title)) {

        $stmt = $conn->prepare("
            UPDATE lessons
            SET lessonTitle = ?, lessonDescription = ?
            WHERE lessonID = ?
        ");

        $stmt->bind_param("ssi", $title, $desc, $id);
        $stmt->execute();

        header("Location: lessons.php");
        exit();

    } else {
        $error = "Title is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Lesson</title>

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
            max-width: 500px;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 15px;
            text-align: center;
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
    <h2>Edit Lesson</h2>
</div>

<div class="container">

<div class="box">

    <h2>Update Lesson ✏️</h2>

    <?php if (isset($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <label>Lesson Title</label>
        <input type="text"
               name="lessonTitle"
               value="<?= htmlspecialchars($lesson['lessonTitle']) ?>"
               required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($lesson['lessonDescription']) ?></textarea>

        <button type="submit" name="updateLesson">
            Update Lesson
        </button>

    </form>

    <a class="back" href="lessons.php">← Back to Lessons</a>

</div>

</div>

</body>
</html>