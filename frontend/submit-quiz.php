<?php
session_start();
require_once '../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if (!isset($_POST['lessonID'], $_POST['questionID'])) {
    header("Location: take-quiz.php");
    exit();
}

$lessonID = (int) $_POST['lessonID'];
$questionIDs = $_POST['questionID'];
$answers = $_POST['answer'] ?? [];

$score = 0;
$total = count($questionIDs);

/* =========================
   1. GET QUESTIONS DATA
========================= */
$stmt = $conn->prepare("
    SELECT questionID, correctAnswer, points
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
   2. CALCULATE SCORE FIRST
========================= */
for ($i = 0; $i < $total; $i++) {

    $qid = (int) $questionIDs[$i];
    $userAnswer = trim($answers[$i] ?? '');

    $correctAnswer = $questionsMap[$qid]['correctAnswer'] ?? '';
    $points = $questionsMap[$qid]['points'] ?? 1;

    if (strcasecmp($userAnswer, $correctAnswer) == 0) {
        $score += $points;
    }
}

/* =========================
   3. SAVE QUIZ ATTEMPT
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
   4. SAVE EACH ANSWER
========================= */
$stmt2 = $conn->prepare("
    INSERT INTO attempt_answers
    (attemptID, questionID, studentAnswer, isCorrect)
    VALUES (?, ?, ?, ?)
");

for ($i = 0; $i < $total; $i++) {

    $qid = (int) $questionIDs[$i];
    $userAnswer = trim($answers[$i] ?? '');

    $correctAnswer = $questionsMap[$qid]['correctAnswer'] ?? '';

    $isCorrect = (strcasecmp($userAnswer, $correctAnswer) == 0) ? 1 : 0;

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
   5. FINAL REDIRECT
========================= */
header("Location: quiz-result.php?attemptID=$attemptID");
exit();
?>