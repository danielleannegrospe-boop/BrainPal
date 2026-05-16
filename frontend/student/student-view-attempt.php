<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}


if (!isset($_GET['id'])) {
    header("Location: student-quiz-records.php");
    exit();
}

$attemptID = (int) $_GET['id'];
$userID = $_SESSION['userID'];

/* =========================
   GET ATTEMPT INFO
========================= */
$stmt = $conn->prepare("
    SELECT 
        qa.attemptID,
        qa.score,
        qa.totalQuestions,
        qa.submittedAt,

        l.lessonTitle,
        s.subjectName

    FROM quiz_attempts qa

    LEFT JOIN lessons l 
        ON qa.lessonID = l.lessonID

    LEFT JOIN subjects s 
        ON l.subjectID = s.subjectID

    WHERE qa.attemptID = ?
    AND qa.studentID = ?
");

$stmt->bind_param("ii", $attemptID, $userID);
$stmt->execute();

$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    echo "Quiz attempt not found.";
    exit();
}

/* =========================
   GET ANSWERS
========================= */
$stmt2 = $conn->prepare("
    SELECT 
        aa.studentAnswer,
        aa.isCorrect,

        q.questionText,
        q.correctAnswer,
        q.points

    FROM attempt_answers aa

    LEFT JOIN questions q
        ON aa.questionID = q.questionID

    WHERE aa.attemptID = ?
");

$stmt2->bind_param("i", $attemptID);
$stmt2->execute();

$answers = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Attempt Details</title>

    <style>

        body{
            font-family: Arial;
            margin: 20px;
        }

        .box{
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .question{
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .correct{
            border-left: 6px solid green;
        }

        .wrong{
            border-left: 6px solid red;
        }

        .title{
            font-weight: bold;
            margin-bottom: 10px;
        }

    </style>

</head>

<body>

<h2>Quiz Attempt Details</h2>

<a href="student-quiz-records.php">← Back to Records</a>

<div class="box">

    <p>
        <b>Subject:</b>
        <?= htmlspecialchars($attempt['subjectName']) ?>
    </p>

    <p>
        <b>Lesson:</b>
        <?= htmlspecialchars($attempt['lessonTitle']) ?>
    </p>

    <p>
        <b>Score:</b>
        <?= $attempt['score'] ?> / <?= $attempt['totalQuestions'] ?>
    </p>

    <p>
        <b>Date Taken:</b>
        <?= $attempt['submittedAt'] ?>
    </p>

</div>

<h3>Answers Breakdown</h3>

<?php while ($row = $answers->fetch_assoc()) { ?>

<div class="question <?= ($row['isCorrect']) ? 'correct' : 'wrong' ?>">

    <div class="title">
        <?= htmlspecialchars($row['questionText']) ?>
    </div>

    <p>
        <b>Your Answer:</b>
        <?= htmlspecialchars($row['studentAnswer']) ?>
    </p>

    <p>
        <b>Correct Answer:</b>
        <?= htmlspecialchars($row['correctAnswer']) ?>
    </p>

    <p>
        <b>Points:</b>
        <?= $row['points'] ?>
    </p>

    <p>
        <?= ($row['isCorrect']) ? '✔ Correct' : '✘ Wrong' ?>
    </p>

</div>

<?php } ?>

</body>
</html>