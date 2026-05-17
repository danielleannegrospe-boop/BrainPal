<?php
session_start();
require_once '../../backend/database.php';

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET ATTEMPT ID (SAFE)
========================= */
$attemptID = isset($_GET['attemptID']) ? (int) $_GET['attemptID'] : 0;

if ($attemptID <= 0) {
    header("Location: student-dashboard.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   GET ATTEMPT (SECURE)
========================= */
$stmt = $conn->prepare("
    SELECT attemptID, studentID, totalQuestions, score
    FROM quiz_attempts
    WHERE attemptID = ? AND studentID = ?
");
$stmt->bind_param("ii", $attemptID, $userID);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

if (!$attempt) {
    header("Location: student-dashboard.php");
    exit();
}

/* =========================
   GET ANSWERS
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

/* =========================
   SAFE CALCULATION
========================= */
$total = (int) ($attempt['totalQuestions'] ?? 0);
$score = (int) ($attempt['score'] ?? 0);

$percentage = ($total > 0) ? round(($score / $total) * 100, 2) : 0;
$passed = ($percentage >= 75);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial;
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
            max-width: 900px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .stat {
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .stat h3 {
            font-size: 22px;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            margin-top: 5px;
        }

        .pass { background: #16a34a; }
        .fail { background: #dc2626; }

        .question {
            border-left: 6px solid #ddd;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #fafafa;
        }

        .correct { border-left-color: #16a34a; }
        .wrong { border-left-color: #dc2626; }

        .meta {
            margin-top: 5px;
            font-size: 14px;
            color: #444;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 14px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .btn:hover {
            background: #3730a3;
        }

        .empty {
            padding: 15px;
            color: gray;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>Quiz Result</h2>
</div>

<div class="container">

<!-- SUMMARY -->
<div class="card">
    <h3>Score Summary</h3>

    <div class="grid">

        <div class="stat">
            <h3><?= $score ?></h3>
            <small>Score</small>
        </div>

        <div class="stat">
            <h3><?= $total ?></h3>
            <small>Total Questions</small>
        </div>

        <div class="stat">
            <h3><?= $percentage ?>%</h3>
            <small>Percentage</small>
        </div>

        <div class="stat">
            <h3><?= $passed ? "PASSED" : "FAILED" ?></h3>
            <span class="badge <?= $passed ? 'pass' : 'fail' ?>">
                Status
            </span>
        </div>

    </div>
</div>

<!-- ANSWERS -->
<div class="card">
    <h3>Answer Review</h3>

    <?php if ($answers->num_rows > 0): ?>

        <?php while ($row = $answers->fetch_assoc()): ?>

            <div class="question <?= $row['isCorrect'] ? 'correct' : 'wrong' ?>">

                <div><b>Question:</b> <?= htmlspecialchars($row['questionText'] ?? '') ?></div>

                <div class="meta">
                    <b>Your Answer:</b> <?= htmlspecialchars($row['studentAnswer'] ?? '') ?>
                </div>

                <div class="meta">
                    <b>Correct Answer:</b> <?= htmlspecialchars($row['correctAnswer'] ?? '') ?>
                </div>

                <div class="meta">
                    <b>Points:</b> <?= $row['points'] ?? 0 ?>
                </div>

                <div class="meta">
                    <?= $row['isCorrect'] ? "✔ Correct" : "✘ Wrong" ?>
                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            No answers found for this attempt.
        </div>

    <?php endif; ?>

</div>

<a class="btn" href="student-dashboard.php">
    Back to Dashboard
</a>

</div>

</body>
</html>