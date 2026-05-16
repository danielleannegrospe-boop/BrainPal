<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/pusher.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

/* =========================
   🔐 AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   🔥 CSRF + FLOW PROTECTION
========================= */
if (!isset($_POST['lessonID'], $_POST['questionID'], $_POST['answer'])) {
    header("Location: take-quiz.php");
    exit();
}

// CSRF VALIDATION
if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    die("CSRF validation failed");
}

$lessonID = (int) $_POST['lessonID'];
$questionIDs = $_POST['questionID'];
$answers = $_POST['answer'];

/* =========================
   INIT SCORE
========================= */
$score = 0;
$total = count($questionIDs);

/* =========================
   GET QUESTIONS DATA
========================= */
$stmt = $conn->prepare("
    SELECT questionID, correctAnswer, points, questionType
    FROM questions
    WHERE questionID = ?
");

$questionsMap = [];

foreach ($questionIDs as $qid) {

    $qid = (int) $qid;

    $stmt->bind_param("i", $qid);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $questionsMap[$qid] = $row;
    }
}

$stmt->close();

/* =========================
   CALCULATE SCORE
========================= */
foreach ($questionIDs as $qid) {

    $qid = (int) $qid;

    $userAnswer = trim($answers[$qid] ?? '');
    $correctAnswer = trim($questionsMap[$qid]['correctAnswer'] ?? '');
    $points = (int) ($questionsMap[$qid]['points'] ?? 1);
    $type = $questionsMap[$qid]['questionType'];

    $userLower = strtolower($userAnswer);
    $correctLower = strtolower($correctAnswer);

    if ($type === 'multiple_choice') {

        if ($userLower === $correctLower) {
            $score += $points;
        }

    } elseif ($type === 'enumeration') {

        $userArray = array_map('trim', explode(',', $userLower));
        $correctArray = array_map('trim', explode(',', $correctLower));

        sort($userArray);
        sort($correctArray);

        if ($userArray == $correctArray) {
            $score += $points;
        }

    } else {

        if ($userLower === $correctLower) {
            $score += $points;
        }
    }
}

/* =========================
   SAVE ATTEMPT
========================= */
$stmt = $conn->prepare("
    INSERT INTO quiz_attempts
    (studentID, lessonID, totalQuestions, score)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("iiii", $userID, $lessonID, $total, $score);
$stmt->execute();

$attemptID = $conn->insert_id;

/* =========================
   SAVE ANSWERS
========================= */
$stmt2 = $conn->prepare("
    INSERT INTO attempt_answers
    (attemptID, questionID, studentAnswer, isCorrect)
    VALUES (?, ?, ?, ?)
");

foreach ($questionIDs as $qid) {

    $qid = (int) $qid;

    $userAnswer = trim($answers[$qid] ?? '');
    $correctAnswer = trim($questionsMap[$qid]['correctAnswer'] ?? '');
    $type = $questionsMap[$qid]['questionType'];

    $userLower = strtolower($userAnswer);
    $correctLower = strtolower($correctAnswer);

    $isCorrect = 0;

    if ($type === 'multiple_choice') {
        $isCorrect = ($userLower === $correctLower) ? 1 : 0;
    }
    elseif ($type === 'enumeration') {

        $userArray = array_map('trim', explode(',', $userLower));
        $correctArray = array_map('trim', explode(',', $correctLower));

        sort($userArray);
        sort($correctArray);

        $isCorrect = ($userArray == $correctArray) ? 1 : 0;

    } else {
        $isCorrect = ($userLower === $correctLower) ? 1 : 0;
    }

    $stmt2->bind_param(
        "iisi",
        $attemptID,
        $qid,
        $userAnswer,
        $isCorrect
    );

    $stmt2->execute();
}

$stmt2->close();

/* =========================
   SESSION FLAGS
========================= */
$_SESSION['quizCompleted'] = true;
$_SESSION['lastAttemptID'] = $attemptID;

/* =========================
   REALTIME QUIZ SUBMISSION
========================= */
$pusher->trigger(
    'quiz-channel',
    'quiz-submitted',
    [
        'studentID' => $userID,
        'attemptID' => $attemptID,
        'score' => $score,
        'total' => $total,
        'percentage' => ($total > 0)
            ? round(($score / $total) * 100, 2)
            : 0,
        'submittedAt' => date('Y-m-d H:i:s')
    ]
);

/* =========================
   REDIRECT
========================= */
header("Location: quiz-result.php?attemptID=$attemptID");
exit();
?>