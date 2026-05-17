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
            max-width: 550px;
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

        input, textarea, select {
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
            min-height: 100px;
        }

        input:focus, textarea:focus, select:focus {
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
    <h2>Edit Question</h2>
</div>

<div class="container">

<div class="box">

    <h2>Update Question ✏️</h2>

    <?php if (isset($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <!-- CSRF -->
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

    <a class="back" href="add-question.php?lessonID=<?= $data['lessonID'] ?>">
        ← Back to Questions
    </a>

</div>

</div>

</body>
</html>