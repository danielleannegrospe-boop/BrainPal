<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET QUESTION DATA
========================= */
if (!isset($_GET['id'])) {
    header("Location: add-question.php");
    exit();
}

$questionID = $_GET['id'];

$stmt = $conn->prepare("
    SELECT * FROM questions 
    WHERE questionID = ? AND date_deleted IS NULL
");
$stmt->bind_param("i", $questionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Question not found.";
    exit();
}

$data = $result->fetch_assoc();

/* =========================
   UPDATE QUESTION (CSRF SECURED)
========================= */
if (isset($_POST['updateQuestion'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $lessonID = $_POST['lessonID'];
    $questionText = trim($_POST['questionText']);
    $questionType = $_POST['questionType'];
    $difficulty = $_POST['difficulty'];
    $correctAnswer = trim($_POST['correctAnswer']);
    $points = $_POST['points'] ?? 1;

    $sql = "
        UPDATE questions 
        SET lessonID = ?, questionText = ?, questionType = ?, difficulty = ?, correctAnswer = ?, points = ?
        WHERE questionID = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssii",
        $lessonID,
        $questionText,
        $questionType,
        $difficulty,
        $correctAnswer,
        $points,
        $questionID
    );

    if ($stmt->execute()) {
        header("Location: add-question.php");
        exit();
    } else {
        $error = "Failed to update question.";
    }
}

/* =========================
   GET LESSONS
========================= */
$lessons = $conn->query("
    SELECT lessonID, lessonTitle
    FROM lessons
    WHERE date_deleted IS NULL
    ORDER BY lessonTitle ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Question</title>

    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Edit Question</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="box">

    <form method="POST">

        <!-- CSRF TOKEN -->
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <label>Lesson</label>
        <select name="lessonID" required>
            <?php while ($row = $lessons->fetch_assoc()) { ?>
                <option value="<?= $row['lessonID']; ?>"
                    <?= ($row['lessonID'] == $data['lessonID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['lessonTitle']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Question</label>
        <textarea name="questionText" required><?= htmlspecialchars($data['questionText']); ?></textarea>

        <label>Question Type</label>
        <select name="questionType">
            <option value="multiple_choice" <?= ($data['questionType']=="multiple_choice") ? "selected" : ""; ?>>
                Multiple Choice
            </option>
            <option value="identification" <?= ($data['questionType']=="identification") ? "selected" : ""; ?>>
                Identification
            </option>
            <option value="enumeration" <?= ($data['questionType']=="enumeration") ? "selected" : ""; ?>>
                Enumeration
            </option>
        </select>

        <label>Difficulty</label>
        <select name="difficulty">
            <option value="easy" <?= ($data['difficulty']=="easy") ? "selected" : ""; ?>>Easy</option>
            <option value="medium" <?= ($data['difficulty']=="medium") ? "selected" : ""; ?>>Medium</option>
            <option value="hard" <?= ($data['difficulty']=="hard") ? "selected" : ""; ?>>Hard</option>
        </select>

        <label>Points</label>
        <input type="number" name="points" value="<?= $data['points']; ?>" min="1">

        <label>Correct Answer</label>
        <input type="text" name="correctAnswer" value="<?= htmlspecialchars($data['correctAnswer']); ?>" required>

        <button type="submit" name="updateQuestion">
            Update Question
        </button>

    </form>

</div>

</body>
</html>