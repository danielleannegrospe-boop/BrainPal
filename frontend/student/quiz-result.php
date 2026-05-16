<?php
session_start();
require_once '../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   GET ATTEMPT ID
========================= */
if (!isset($_GET['attemptID'])) {
    echo "No attempt found.";
    exit();
}

$attemptID = $_GET['attemptID'];

/* =========================
   GET ATTEMPT INFO
========================= */
$stmt = $conn->prepare("
    SELECT * FROM quiz_attempts
    WHERE attemptID = ?
");
$stmt->bind_param("i", $attemptID);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();

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
   COMPUTE SCORE DATA
========================= */
$total = $attempt['totalQuestions'];
$score = $attempt['score'];

$percentage = ($total > 0) ? ($score / $total) * 100 : 0;
$passed = ($percentage >= 75) ? "PASSED" : "FAILED";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        .correct { color: green; }
        .wrong { color: red; }
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
     REVIEW ANSWERS
========================= -->
<div class="box">

    <h3>Answer Review</h3>

    <?php while ($row = $answers->fetch_assoc()) { ?>

        <div style="margin-bottom:15px; padding:10px; border:1px solid #ddd;">

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

</body>
</html>