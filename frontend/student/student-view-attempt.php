<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$userID = (int) $_SESSION['userID'];
$attemptID = (int) $_GET['attemptID'];

/* =========================
   GET ATTEMPT
========================= */
$a = $conn->prepare("
    SELECT * FROM quiz_attempts
    WHERE attemptID = ? AND studentID = ?
");
$a->bind_param("ii", $attemptID, $userID);
$a->execute();
$attempt = $a->get_result()->fetch_assoc();

if (!$attempt) {
    exit("Invalid attempt");
}

/* =========================
   GET ANSWERS
========================= */
$q = $conn->prepare("
    SELECT 
        aa.studentAnswer,
        aa.isCorrect,
        q.questionText,
        q.correctAnswer,
        q.points
    FROM attempt_answers aa
    LEFT JOIN questions q ON aa.questionID = q.questionID
    WHERE aa.attemptID = ?
");
$q->bind_param("i", $attemptID);
$q->execute();
$answers = $q->get_result();

/* =========================
   CALCULATIONS
========================= */
$total = (int)$attempt['totalQuestions'];
$score = (int)$attempt['score'];
$percent = ($total > 0) ? round(($score / $total) * 100, 2) : 0;
$status = ($percent >= 75) ? "PASSED" : "FAILED";
$color = ($percent >= 75) ? "#16a34a" : "#dc2626";
?>

<!DOCTYPE html>
<html>
<head>
<title>Quiz Review</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial;
}

body{
    background:#f4f6fb;
}

/* HEADER */
.header{
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    color:white;
    padding:18px 25px;
}

/* CONTAINER */
.container{
    max-width:900px;
    margin:auto;
    padding:25px;
}

/* SUMMARY CARD */
.card{
    background:white;
    padding:18px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    margin-bottom:20px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap:10px;
    margin-top:10px;
}

/* STAT */
.stat{
    background:#f8fafc;
    padding:15px;
    border-radius:12px;
    text-align:center;
}

.stat h3{
    font-size:22px;
}

/* BADGE */
.badge{
    display:inline-block;
    margin-top:8px;
    padding:5px 10px;
    border-radius:8px;
    color:white;
    font-size:12px;
    background:<?= $color ?>;
}

/* QUESTION CARD */
.question{
    background:white;
    padding:15px;
    border-radius:12px;
    margin-bottom:12px;
    box-shadow:0 6px 15px rgba(0,0,0,0.05);
    border-left:6px solid transparent;
}

.correct{
    border-left-color:#16a34a;
}

.wrong{
    border-left-color:#dc2626;
}

/* TEXT */
.meta{
    margin-top:5px;
    font-size:14px;
    color:#444;
}

.title{
    font-weight:bold;
    margin-bottom:6px;
}

/* BUTTON */
.btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 14px;
    background:#4f46e5;
    color:white;
    text-decoration:none;
    border-radius:8px;
}

.btn:hover{
    background:#3730a3;
}

</style>
</head>

<body>

<div class="header">
    <h2>Quiz Review</h2>
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
            <small>Total</small>
        </div>

        <div class="stat">
            <h3><?= $percent ?>%</h3>
            <small>Percentage</small>
        </div>

        <div class="stat">
            <h3><?= $status ?></h3>
            <span class="badge"><?= $status ?></span>
        </div>

    </div>

</div>

<!-- ANSWERS -->
<div class="card">
    <h3>Answer Breakdown</h3>
</div>

<?php while ($row = $answers->fetch_assoc()): ?>

    <div class="question <?= $row['isCorrect'] ? 'correct' : 'wrong' ?>">

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

        <div class="meta">
            <?= $row['isCorrect'] ? "✔ Correct" : "✘ Wrong" ?>
        </div>

    </div>

<?php endwhile; ?>

<a class="btn" href="student-dashboard.php">
    Back to Dashboard
</a>

</div>

</body>
</html>