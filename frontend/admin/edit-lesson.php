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
</head>
<body>

<h2>Edit Lesson</h2>

<?php if (isset($error)) : ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST">

    <!-- CSRF TOKEN -->
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <label>Lesson Title</label>
    <input type="text" name="lessonTitle"
           value="<?= htmlspecialchars($lesson['lessonTitle']) ?>"
           required>

    <label>Description</label>
    <textarea name="description" required><?= htmlspecialchars($lesson['lessonDescription']) ?></textarea>

    <button type="submit" name="updateLesson">
        Update Lesson
    </button>

</form>

</body>
</html>