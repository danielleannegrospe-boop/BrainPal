<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6fb;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 18px 25px;
        }

        .container {
            padding: 25px;
        }

        /* INFO CARD */
        .box {
            background: white;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        h2, h3 {
            margin-bottom: 10px;
        }

        /* QUESTION CARD */
        .question {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            border-left: 6px solid transparent;
        }

        .correct {
            border-left-color: #16a34a;
        }

        .wrong {
            border-left-color: #dc2626;
        }

        .title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .meta {
            margin-top: 4px;
            font-size: 14px;
            color: #444;
        }

        .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 6px;
            color: white;
        }

        .ok { background: #16a34a; }
        .bad { background: #dc2626; }

    </style>
</head>

<body>

<div class="header">
    <h2>Quiz Attempt Details</h2>
</div>

<div class="container">

<!-- ATTEMPT INFO -->
<div class="box">

    <p><b>Student:</b> <?= htmlspecialchars($attempt['firstName'] . ' ' . $attempt['lastName']) ?></p>
    <p><b>Subject:</b> <?= htmlspecialchars($attempt['subjectName']) ?></p>
    <p><b>Lesson:</b> <?= htmlspecialchars($attempt['lessonTitle']) ?></p>
    <p><b>Score:</b> <?= $attempt['score'] ?> / <?= $attempt['totalQuestions'] ?></p>
    <p><b>Date Taken:</b> <?= $attempt['submittedAt'] ?></p>

</div>

<h3>Answers Breakdown</h3>

<!-- QUESTIONS -->
<?php while ($row = $answers->fetch_assoc()) { ?>

    <div class="question <?= ($row['isCorrect']) ? 'correct' : 'wrong' ?>">

        <div class="title">
            <?= htmlspecialchars($row['questionText']) ?>
        </div>

        <div class="meta">
            <b>Your Answer:</b> <?= htmlspecialchars($row['studentAnswer']) ?>
        </div>

        <div class="meta">
            <b>Correct Answer:</b> <?= htmlspecialchars($row['correctAnswer']) ?>
        </div>

        <div class="meta">
            <b>Points:</b> <?= $row['points'] ?>
        </div>

        <div class="badge <?= ($row['isCorrect']) ? 'ok' : 'bad' ?>">
            <?= ($row['isCorrect']) ? 'Correct' : 'Wrong' ?>
        </div>

    </div>

<?php } ?>

</div>

</body>
</html>