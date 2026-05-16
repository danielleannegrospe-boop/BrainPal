<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   CSRF INIT
========================= */
$csrf = generateCSRF();

/* =========================
   SET QUIZ SESSION ONLY WHEN STARTING
========================= */
if (isset($_GET['start'])) {
    $_SESSION['canTakeQuiz'] = true;
    $_SESSION['quizLessonID'] = null;
}

/* =========================
   VARIABLES
========================= */
$userID = $_SESSION['userID'];

$selectedSubject = $_GET['subject'] ?? null;
$selectedLesson = $_GET['lessonID'] ?? null;
$selectedDifficulty = $_GET['difficulty'] ?? null;
$selectedType = $_GET['questionType'] ?? null;

/* =========================
   SUBJECTS
========================= */
$subjects = $conn->query("
    SELECT subjectID, subjectName, description
    FROM subjects
    WHERE date_deleted IS NULL
    ORDER BY subjectName ASC
");

/* =========================
   LESSONS
========================= */
$lessons = null;

if (!empty($selectedSubject)) {
    $stmt = $conn->prepare("
        SELECT lessonID, lessonTitle
        FROM lessons
        WHERE subjectID = ?
        AND date_deleted IS NULL
        ORDER BY lessonTitle ASC
    ");
    $stmt->bind_param("i", $selectedSubject);
    $stmt->execute();
    $lessons = $stmt->get_result();
}

/* =========================
   QUESTIONS
========================= */
$questions = null;

if (!empty($selectedLesson) && !empty($selectedDifficulty) && !empty($selectedType)) {

    $stmt = $conn->prepare("
        SELECT questionID, questionText, questionType,
               choiceA, choiceB, choiceC, choiceD
        FROM questions
        WHERE lessonID = ?
        AND difficulty = ?
        AND questionType = ?
        AND date_deleted IS NULL
        ORDER BY RAND()
        LIMIT 15
    ");

    $stmt->bind_param("iss", $selectedLesson, $selectedDifficulty, $selectedType);
    $stmt->execute();
    $questions = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Take Quiz</title>

<style>
body { font-family: Arial; margin: 20px; background:#f5f5f5; }
.box { background:white; padding:20px; border-radius:10px; margin-bottom:20px; }
.question-box { border:1px solid #ccc; padding:15px; margin-bottom:15px; border-radius:8px; }
.choice { margin-bottom:8px; }
button { padding:10px; background:#007bff; color:white; border:none; border-radius:5px; }
</style>
</head>

<body>

<h2>Take Quiz</h2>

<!-- FILTER FORM (GET) -->
<form method="GET">
<div class="box">

<label>Subject</label>
<select name="subject" onchange="this.form.submit()" required>
<option value="">-- Choose Subject --</option>

<?php while ($s = $subjects->fetch_assoc()) { ?>
<option value="<?= $s['subjectID'] ?>"
<?= ($selectedSubject == $s['subjectID']) ? 'selected' : '' ?>>
<?= htmlspecialchars($s['subjectName']) ?>
</option>
<?php } ?>

</select>

<label>Lesson</label>
<select name="lessonID" required>
<option value="">-- Choose Lesson --</option>

<?php if ($lessons) { ?>
<?php while ($l = $lessons->fetch_assoc()) { ?>
<option value="<?= $l['lessonID'] ?>"
<?= ($selectedLesson == $l['lessonID']) ? 'selected' : '' ?>>
<?= htmlspecialchars($l['lessonTitle']) ?>
</option>
<?php } ?>
<?php } ?>

</select>

<label>Difficulty</label>
<select name="difficulty" required>
<option value="">-- Choose --</option>
<option value="easy">Easy</option>
<option value="medium">Medium</option>
<option value="hard">Hard</option>
</select>

<label>Type</label>
<select name="questionType" required>
<option value="">-- Choose --</option>
<option value="multiple_choice">Multiple Choice</option>
<option value="identification">Identification</option>
<option value="enumeration">Enumeration</option>
</select>

<button type="submit">Start Quiz</button>

</div>
</form>

<!-- QUIZ FORM (POST) -->
<?php if ($questions && $questions->num_rows > 0): ?>

<form method="POST" action="submit-quiz.php">

<!-- CSRF MUST BE INSIDE POST FORM -->
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="lessonID" value="<?= $selectedLesson ?>">

<div class="box">

<?php while ($q = $questions->fetch_assoc()): ?>

<div class="question-box">

<p><b><?= htmlspecialchars($q['questionText']) ?></b></p>

<input type="hidden" name="questionID[]" value="<?= $q['questionID'] ?>">

<?php if ($q['questionType'] === 'multiple_choice'): ?>

<div class="choice">
<label>
<input type="radio" name="answer[<?= $q['questionID'] ?>]" value="A" required>
A. <?= htmlspecialchars($q['choiceA']) ?>
</label>
</div>

<div class="choice">
<label>
<input type="radio" name="answer[<?= $q['questionID'] ?>]" value="B">
B. <?= htmlspecialchars($q['choiceB']) ?>
</label>
</div>

<div class="choice">
<label>
<input type="radio" name="answer[<?= $q['questionID'] ?>]" value="C">
C. <?= htmlspecialchars($q['choiceC']) ?>
</label>
</div>

<div class="choice">
<label>
<input type="radio" name="answer[<?= $q['questionID'] ?>]" value="D">
D. <?= htmlspecialchars($q['choiceD']) ?>
</label>
</div>

<?php else: ?>

<input type="text" name="answer[<?= $q['questionID'] ?>]" required>

<?php endif; ?>

</div>

<?php endwhile; ?>

<button type="submit">Submit Quiz</button>

</div>

</form>

<?php endif; ?>

</body>
</html>