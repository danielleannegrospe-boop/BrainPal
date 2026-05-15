<?php
session_start();
require_once '../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: quiz-records.php");
    exit();
}

$attemptID = (int) $_GET['id'];

/* =========================
   GET ATTEMPT INFO
========================= */
$stmt = $conn->prepare("
    SELECT 
        qa.attemptID,
        qa.score,
        qa.totalQuestions,
        qa.submittedAt,

        u.firstName,
        u.lastName,

        l.lessonTitle,
        s.subjectName

    FROM quiz_attempts qa
    LEFT JOIN users u ON qa.studentID = u.userID
    LEFT JOIN lessons l ON qa.lessonID = l.lessonID
    LEFT JOIN subjects s ON l.subjectID = s.subjectID

    WHERE qa.attemptID = ?
");

$stmt->bind_param("i", $attemptID);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    echo "Attempt not found.";
    exit();
}

/* =========================
   GET ANSWERS BREAKDOWN
========================= */
$stmt2 = $conn->prepare("
    SELECT 
        aa.questionID,
        aa.studentAnswer,
        aa.isCorrect,

        q.questionText,
        q.correctAnswer,
        q.points

    FROM attempt_answers aa
    LEFT JOIN questions q ON aa.questionID = q.questionID

    WHERE aa.attemptID = ?
");

$stmt2->bind_param("i", $attemptID);
$stmt2->execute();
$answers = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Details</title>

    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .box {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .question {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .correct {
            border-left: 6px solid green;
        }

        .wrong {
            border-left: 6px solid red;
        }

        .title {
            font-weight: bold;
        }

        .meta {
            margin-bottom: 5px;
            color: #555;
        }
    </style>
</head>

<body>

<h2>Quiz Attempt Details</h2>

<!-- ATTEMPT INFO -->
<div class="box">
    <p><b>Student:</b> <?= $attempt['firstName'] . ' ' . $attempt['lastName'] ?></p>
    <p><b>Subject:</b> <?= $attempt['subjectName'] ?></p>
    <p><b>Lesson:</b> <?= $attempt['lessonTitle'] ?></p>
    <p><b>Score:</b> <?= $attempt['score'] ?> / <?= $attempt['totalQuestions'] ?></p>
    <p><b>Date Taken:</b> <?= $attempt['submittedAt'] ?></p>
</div>

<h3>Answers Breakdown</h3>

<!-- QUESTIONS -->
<?php while ($row = $answers->fetch_assoc()) { ?>

    <div class="question <?= ($row['isCorrect']) ? 'correct' : 'wrong' ?>">

        <div class="title">
            Q: <?= $row['questionText'] ?>
        </div>

        <div class="meta">
            <b>Student Answer:</b> <?= $row['studentAnswer'] ?>
        </div>

        <div class="meta">
            <b>Correct Answer:</b> <?= $row['correctAnswer'] ?>
        </div>

        <div class="meta">
            <b>Points:</b> <?= $row['points'] ?>
        </div>

        <div class="meta">
            <?= ($row['isCorrect']) ? "✔ Correct" : "✘ Wrong" ?>
        </div>

    </div>

<?php } ?>

</body>
</html>