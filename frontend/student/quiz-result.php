<?php
session_start();
require_once '../../backend/database.php';

/* =========================
   🔐 AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   🔥 RESULT FLOW PROTECTION (IMPORTANT FIX)
   ONLY ALLOW ACCESS IF QUIZ WAS JUST SUBMITTED
========================= */
if (!isset($_SESSION['quizCompleted']) || $_SESSION['quizCompleted'] !== true) {
    header("Location: student-dashboard.php");
    exit();
}

/* =========================
   GET ATTEMPT ID (FROM SESSION, NOT URL SAFE ACCESS)
========================= */
if (!isset($_GET['attemptID'])) {
    header("Location: student-dashboard.php");
    exit();
}

$attemptID = $_GET['attemptID'];
$userID = $_SESSION['userID'];

/* =========================
   🔐 SECURITY CHECK (OWNERSHIP)
========================= */
$stmt = $conn->prepare("
    SELECT * FROM quiz_attempts
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
   GET ANSWERS + QUESTIONS
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
   SCORE COMPUTATION
========================= */
$total = $attempt['totalQuestions'];
$score = $attempt['score'];

$percentage = ($total > 0) ? ($score / $total) * 100 : 0;
$passed = ($percentage >= 75) ? "PASSED" : "FAILED";

/* =========================
   🔥 ONCE VIEWED, LOCK AGAIN
========================= */
unset($_SESSION['quizCompleted']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>

    <style>
        body { font-family: Arial; margin: 20px; background:#f5f5f5; }

        .box {
            background:white;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .correct { color: green; font-weight: bold; }
        .wrong { color: red; font-weight: bold; }

        a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background:#007bff;
            color:white;
            text-decoration:none;
            border-radius:5px;
        }

        a:hover {
            background:#0056b3;
        }
    </style>
</head>

<body>

<h2>Quiz Result</h2>

<!-- =========================
     SUMMARY
========================= -->
<div class="box">

    <h3>Score Summary</h3>

    <p><b>Score:</b> <?php echo $score; ?> / <?php echo $total; ?></p>

    <p><b>Percentage:</b> <?php echo round($percentage, 2); ?>%</p>

    <p>
        <b>Status:</b>
        <span style="color:<?php echo ($passed == 'PASSED') ? 'green' : 'red'; ?>">
            <?php echo $passed; ?>
        </span>
    </p>

</div>

<!-- =========================
     ANSWER REVIEW
========================= -->
<div class="box">

    <h3>Answer Review</h3>

    <?php while ($row = $answers->fetch_assoc()) { ?>

        <div style="margin-bottom:15px; padding:10px; border:1px solid #eee; border-radius:8px;">

            <p><b>Question:</b> <?php echo htmlspecialchars($row['questionText']); ?></p>

            <p>
                <b>Your Answer:</b>
                <span class="<?php echo $row['isCorrect'] ? 'correct' : 'wrong'; ?>">
                    <?php echo htmlspecialchars($row['studentAnswer']); ?>
                </span>
            </p>

            <p><b>Correct Answer:</b> <?php echo htmlspecialchars($row['correctAnswer']); ?></p>

            <p><b>Points:</b> <?php echo $row['points']; ?></p>

        </div>

    <?php } ?>

</div>

<a href="take-quiz.php">Take Another Quiz</a>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</body>
</html>