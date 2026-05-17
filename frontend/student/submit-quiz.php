<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/pusher.php';
require_once '../../backend/csrf.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    die("CSRF validation failed");
}

$userID = (int) $_SESSION['userID'];
$lessonID = (int) $_POST['lessonID'];
$answers = $_POST['answers'] ?? [];

$conn->begin_transaction();

try {

    /* =========================
       GET QUESTIONS
    ========================= */
    $q = $conn->prepare("
        SELECT questionID, correctAnswer
        FROM questions
        WHERE lessonID = ? AND date_deleted IS NULL
    ");
    $q->bind_param("i", $lessonID);
    $q->execute();
    $res = $q->get_result();

    $questions = [];
    while ($row = $res->fetch_assoc()) {
        $questions[$row['questionID']] = $row['correctAnswer'];
    }

    $totalQuestions = count($questions);
    $score = 0;

    /* =========================
       CREATE ATTEMPT
    ========================= */
    $stmt = $conn->prepare("
        INSERT INTO quiz_attempts (studentID, lessonID, totalQuestions, score)
        VALUES (?, ?, ?, 0)
    ");
    $stmt->bind_param("iii", $userID, $lessonID, $totalQuestions);
    $stmt->execute();

    $attemptID = $stmt->insert_id;

    /* =========================
       SAVE ANSWERS
    ========================= */
    $insertAns = $conn->prepare("
        INSERT INTO attempt_answers (attemptID, questionID, studentAnswer, isCorrect)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($questions as $qid => $correct) {

        $studentAnswer = trim($answers[$qid] ?? '');

        $isCorrect = (strcasecmp($studentAnswer, trim($correct)) === 0) ? 1 : 0;

        if ($isCorrect) $score++;

        $insertAns->bind_param("iisi", $attemptID, $qid, $studentAnswer, $isCorrect);
        $insertAns->execute();
    }

   /* =========================
   UPDATE SCORE
========================= */
$update = $conn->prepare("
    UPDATE quiz_attempts
    SET score = ?
    WHERE attemptID = ?
");
$update->bind_param("ii", $score, $attemptID);
$update->execute();

/* =========================
   CALCULATION FOR UI + PUSHER
========================= */
$percent = ($totalQuestions > 0)
    ? round(($score / $totalQuestions) * 100, 2)
    : 0;

$status = ($percent >= 75) ? "PASSED" : "FAILED";
$color = ($percent >= 75) ? "#16a34a" : "#dc2626";

$conn->commit();

/* =========================
   PUSHER EVENT (REAL-TIME)
========================= */
$data = [
    'attemptID' => $attemptID,
    'studentID' => $userID,
    'lessonID' => $lessonID,
    'score' => $score,
    'total' => $totalQuestions,
    'percent' => $percent,
    'status' => $status
];

$pusher->trigger('quiz-channel', 'quiz-submitted', $data);

} catch (Exception $e) {
    $conn->rollback();
    die("Error submitting quiz: " . $e->getMessage());
}

/* =========================
   CALCULATION FOR UI
========================= */
$percent = ($totalQuestions > 0)
    ? round(($score / $totalQuestions) * 100, 2)
    : 0;

$status = ($percent >= 75) ? "PASSED" : "FAILED";
$color = ($percent >= 75) ? "#16a34a" : "#dc2626";
?>

<!DOCTYPE html>
<html>
<head>
<title>Quiz Submitted</title>

<style>
body{
    margin:0;
    font-family: Arial;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    color:white;
}

.card{
    background:white;
    color:#333;
    padding:30px;
    border-radius:16px;
    width:400px;
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

h1{
    margin-bottom:10px;
}

.score{
    font-size:40px;
    font-weight:bold;
    margin:10px 0;
    color:<?= $color ?>;
}

.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:8px;
    color:white;
    background:<?= $color ?>;
    font-weight:bold;
    margin-bottom:15px;
}

.btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 15px;
    background:#4f46e5;
    color:white;
    text-decoration:none;
    border-radius:8px;
}

.btn:hover{
    background:#3730a3;
}

.small{
    color:gray;
    font-size:14px;
    margin-top:10px;
}
</style>
</head>

<body>

<div class="card">

    <h1>Quiz Submitted 🎉</h1>

    <div class="score"><?= $score ?> / <?= $totalQuestions ?></div>

    <div class="badge"><?= $status ?></div>

    <div style="margin-top:10px;">
        <?= $percent ?>%
    </div>

    <a class="btn" href="student-view-attempt.php?attemptID=<?= $attemptID ?>">
        View Full Result
    </a>

    <div class="small">
        Redirecting to dashboard in 5 seconds...
    </div>

</div>

<script>
setTimeout(() => {
    window.location.href =
        "student-view-attempt.php?attemptID=<?= $attemptID ?>";
}, 5000);
</script>

</body>
</html>