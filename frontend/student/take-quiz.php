<?php
session_start();

require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$csrf = generateCSRF();
$userID = $_SESSION['userID'];

$selectedSubject = isset($_GET['subject']) ? (int)$_GET['subject'] : null;

$selectedLesson = isset($_GET['lessonID']) && $_GET['lessonID'] !== ''
    ? (int)$_GET['lessonID']
    : null;

$selectedDifficulty = $_GET['difficulty'] ?? null;
$selectedType = $_GET['questionType'] ?? null;

$selectedDifficulty = $selectedDifficulty !== '' ? $selectedDifficulty : null;
$selectedType = $selectedType !== '' ? $selectedType : null;

/* SUBJECTS */
$subjects = $conn->query("
    SELECT subjectID, subjectName
    FROM subjects
    WHERE date_deleted IS NULL
    ORDER BY subjectName ASC
");

/* LESSONS */
$lessons = null;

if ($selectedSubject) {
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

/* QUESTIONS */
$questions = null;

if ($selectedLesson && $selectedDifficulty && $selectedType) {

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
body{font-family:Arial;margin:0;background:#f4f6f9;}
.header{background:#007bff;color:white;padding:18px;}
.container{padding:20px;max-width:900px;margin:auto;}
.box{background:white;padding:20px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);}
select,input,button{width:100%;padding:10px;margin:8px 0 12px;border-radius:6px;border:1px solid #ccc;}
button{background:#007bff;color:white;border:none;cursor:pointer;}
button:hover{background:#0056b3;}
.question-box{border:1px solid #ddd;padding:15px;margin-bottom:15px;border-radius:10px;background:#fafafa;}
</style>

</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Take Quiz</h2>

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

<form method="GET">
<div class="box">

<label>Subject</label>
<select name="subject" onchange="this.form.submit()" required>
<option value="">-- Choose Subject --</option>
<?php while($s = $subjects->fetch_assoc()): ?>
<option value="<?= $s['subjectID'] ?>" <?= ($selectedSubject == $s['subjectID'])?'selected':'' ?>>
<?= htmlspecialchars($s['subjectName']) ?>
</option>
<?php endwhile; ?>
</select>

<label>Lesson</label>
<select name="lessonID" onchange="this.form.submit()" required>
<option value="">-- Choose Lesson --</option>
<?php if ($lessons): ?>
<?php while($l = $lessons->fetch_assoc()): ?>
<option value="<?= $l['lessonID'] ?>" <?= ($selectedLesson == $l['lessonID'])?'selected':'' ?>>
<?= htmlspecialchars($l['lessonTitle']) ?>
</option>
<?php endwhile; ?>
<?php endif; ?>
</select>

<label>Difficulty</label>
<select name="difficulty" onchange="this.form.submit()" required>
<option value="">-- Choose --</option>
<option value="easy" <?= ($selectedDifficulty=='easy')?'selected':'' ?>>Easy</option>
<option value="medium" <?= ($selectedDifficulty=='medium')?'selected':'' ?>>Medium</option>
<option value="hard" <?= ($selectedDifficulty=='hard')?'selected':'' ?>>Hard</option>
</select>

<label>Question Type</label>
<select name="questionType" onchange="this.form.submit()" required>
<option value="">-- Choose --</option>
<option value="multiple_choice" <?= ($selectedType=='multiple_choice')?'selected':'' ?>>Multiple Choice</option>
<option value="identification" <?= ($selectedType=='identification')?'selected':'' ?>>Identification</option>
<option value="enumeration" <?= ($selectedType=='enumeration')?'selected':'' ?>>Enumeration</option>
</select>

</div>
</form>

<?php if ($questions && $questions->num_rows > 0): ?>

<form method="POST" action="submit-quiz.php">

<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="lessonID" value="<?= $selectedLesson ?>">

<div class="box">

<?php while($q = $questions->fetch_assoc()): ?>

<div class="question-box">

<p><b><?= htmlspecialchars($q['questionText']) ?></b></p>

<input type="hidden" name="questionID[]" value="<?= $q['questionID'] ?>">

<?php if ($q['questionType'] === 'multiple_choice'): ?>

<label><input type="radio" name="answer[<?= $q['questionID'] ?>]" value="A" required> <?= $q['choiceA'] ?></label><br>
<label><input type="radio" name="answer[<?= $q['questionID'] ?>]" value="B"> <?= $q['choiceB'] ?></label><br>
<label><input type="radio" name="answer[<?= $q['questionID'] ?>]" value="C"> <?= $q['choiceC'] ?></label><br>
<label><input type="radio" name="answer[<?= $q['questionID'] ?>]" value="D"> <?= $q['choiceD'] ?></label>

<?php else: ?>

<input type="text" name="answer[<?= $q['questionID'] ?>]" required>

<?php endif; ?>

</div>

<?php endwhile; ?>

<button type="submit">Submit Quiz</button>

</div>
</form>

<?php elseif ($selectedLesson): ?>
<p>No questions found.</p>
<?php endif; ?>

</div>

</body>
</html>