<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$userID = (int) $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT 
        qa.attemptID,
        qa.score,
        qa.totalQuestions,
        qa.submittedAt,
        l.lessonTitle,
        s.subjectName
    FROM quiz_attempts qa
    LEFT JOIN lessons l ON qa.lessonID = l.lessonID
    LEFT JOIN subjects s ON l.subjectID = s.subjectID
    WHERE qa.studentID = ?
    ORDER BY qa.submittedAt DESC
");

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   SAFE PERCENT
========================= */
function percent($score, $total) {
    if (!$total) return 0;
    return round(($score / $total) * 100, 2);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Quiz Attempts</title>

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
    padding:25px;
    max-width:1000px;
    margin:auto;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap:15px;
}

/* CARD */
.card{
    background:white;
    padding:15px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    border-left:6px solid #4f46e5;
    transition:0.2s;
}

.card:hover{
    transform: translateY(-3px);
}

/* TEXT */
.title{
    font-weight:bold;
    font-size:16px;
    margin-bottom:6px;
}

.meta{
    font-size:14px;
    color:#555;
    margin-bottom:5px;
}

/* SCORE */
.score{
    font-weight:bold;
}

.good{
    color:#16a34a;
}

.bad{
    color:#dc2626;
}

/* BUTTON */
.btn{
    display:inline-block;
    margin-top:10px;
    padding:7px 12px;
    background:#4f46e5;
    color:white;
    text-decoration:none;
    border-radius:8px;
    font-size:13px;
}

.btn:hover{
    background:#3730a3;
}

/* EMPTY */
.empty{
    text-align:center;
    padding:30px;
    color:gray;
    background:white;
    border-radius:12px;
}
</style>

</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>My Quiz Attempts</h2>

    <a href="../student/student-dashboard.php" 
       style="
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
       ">
        ⬅ Go to Dashboard
    </a>
</div>

<div class="container">

<div class="grid">

<?php if ($result->num_rows > 0): ?>

    <?php while ($row = $result->fetch_assoc()): 

        $percent = percent($row['score'], $row['totalQuestions']);
    ?>

    <div class="card">

        <div class="title">
            <?= htmlspecialchars($row['subjectName']) ?>
        </div>

        <div class="meta">
            <?= htmlspecialchars($row['lessonTitle']) ?>
        </div>

        <div class="meta score <?= ($percent >= 75 ? 'good' : 'bad') ?>">
            Score: <?= $row['score'] ?> / <?= $row['totalQuestions'] ?>
        </div>

        <div class="meta">
            <?= $percent ?>%
        </div>

        <div class="meta">
            <?= $row['submittedAt'] ?>
        </div>

        <a class="btn"
           href="student-view-attempt.php?attemptID=<?= $row['attemptID'] ?>">
            View Details
        </a>

    </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="empty">
       NO QUIZ ATTEMPTS FOUND
    </div>

<?php endif; ?>

</div>

</div>

</body>
</html>