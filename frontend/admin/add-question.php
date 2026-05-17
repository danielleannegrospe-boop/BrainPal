<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';
$csrf = generateCSRF();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET LESSON
========================= */
$selectedLessonID = $_GET['lessonID'] ?? null;

$selectedLesson = null;
$selectedSubject = null;

if ($selectedLessonID) {

    $stmt = $conn->prepare("
        SELECT l.lessonID, l.lessonTitle, l.subjectID, s.subjectName
        FROM lessons l
        LEFT JOIN subjects s ON l.subjectID = s.subjectID
        WHERE l.lessonID = ?
    ");

    $stmt->bind_param("i", $selectedLessonID);
    $stmt->execute();
    $selectedLesson = $stmt->get_result()->fetch_assoc();

    if ($selectedLesson) {
        $selectedSubject = $selectedLesson['subjectName'];
    }
}

/* =========================
   DELETE QUESTION (SECURED)
========================= */
if (isset($_POST['deleteQuestion'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $id = (int) $_POST['delete_id'];

    $stmt = $conn->prepare("
        UPDATE questions
        SET date_deleted = NOW()
        WHERE questionID = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: add-question.php?lessonID=$selectedLessonID");
    exit();
}

/* =========================
   ADD QUESTIONS (SECURED)
========================= */
if (isset($_POST['addQuestion'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    $lessonID = $_POST['lessonID'];
    $questionType = $_POST['questionType'];
    $difficulty = $_POST['difficulty'];
    $points = $_POST['points'];

    $questionTexts = $_POST['questionText'];
    $correctAnswers = $_POST['correctAnswer'];

    $choiceA = $_POST['choiceA'];
    $choiceB = $_POST['choiceB'];
    $choiceC = $_POST['choiceC'];
    $choiceD = $_POST['choiceD'];

    $stmt = $conn->prepare("
        INSERT INTO questions
        (lessonID, questionText, questionType, difficulty, correctAnswer, points, choiceA, choiceB, choiceC, choiceD)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($questionTexts); $i++) {

        $q = trim($questionTexts[$i]);
        if (empty($q)) continue;

        $stmt->bind_param(
            "issssissss",
            $lessonID,
            $q,
            $questionType,
            $difficulty,
            $correctAnswers[$i],
            $points,
            $choiceA[$i],
            $choiceB[$i],
            $choiceC[$i],
            $choiceD[$i]
        );

        $stmt->execute();
    }

    header("Location: add-question.php?lessonID=$lessonID");
    exit();
}

/* =========================
   GET QUESTIONS
========================= */
$questions = [];

if ($selectedLessonID) {

    $stmt = $conn->prepare("
        SELECT *
        FROM questions
        WHERE lessonID = ?
        AND date_deleted IS NULL
        ORDER BY questionID DESC
    ");

    $stmt->bind_param("i", $selectedLessonID);
    $stmt->execute();

    $questions = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Questions</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6fb;
    color: #333;
}

/* HEADER STYLE */
.header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    color: white;
    padding: 18px 25px;
}

.container {
    padding: 25px;
}

/* CARD BOX */
.box {
    background: #fff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

/* SUBJECT INFO */
.subject-info {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
}

/* FORM ELEMENTS */
label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
    font-size: 13px;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 10px;
    outline: none;
}

textarea {
    resize: none;
}

/* QUESTION BOX */
.question-box {
    border: 1px solid #eee;
    background: #fafafa;
    padding: 15px;
    border-radius: 12px;
    margin-top: 10px;
}

/* BUTTON */
button {
    margin-top: 15px;
    padding: 10px 14px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    background: #4f46e5;
    color: white;
    transition: 0.2s;
}

button:hover {
    background: #3730a3;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

th {
    background: #4f46e5;
    color: white;
    padding: 12px;
    text-align: left;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

/* ACTION BUTTONS */
.btn {
    padding: 6px 10px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    display: inline-block;
}

.edit { background: #007bff; }
.delete { background: #dc3545; border: none; cursor: pointer; }

.edit:hover { opacity: 0.85; }
.delete:hover { opacity: 0.85; }

/* CHOICES */
.choices {
    display: none;
    margin-top: 10px;
}

</style>

</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Add Questions</h2>

    <a href="../admin/lessons.php"
       style="
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
       ">
        ⬅ Back to Lessons
    </a>
</div>
<div class="container">

<!-- SUBJECT INFO -->
<div class="box subject-info">
    <b>Subject:</b> <?= htmlspecialchars($selectedSubject ?? 'No Subject') ?><br>
    <b>Lesson:</b> <?= htmlspecialchars($selectedLesson['lessonTitle'] ?? 'No Lesson') ?>
</div>

<!-- FORM -->
<div class="box">

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="lessonID" value="<?= $selectedLessonID ?>">

<label>Question Type</label>
<select name="questionType" id="questionType" onchange="toggleChoices()">
    <option value="multiple_choice">Multiple Choice</option>
    <option value="identification">Identification</option>
    <option value="enumeration">Enumeration</option>
</select>

<label>Difficulty</label>
<select name="difficulty">
    <option value="easy">Easy</option>
    <option value="medium">Medium</option>
    <option value="hard">Hard</option>
</select>

<label>Points</label>
<input type="number" name="points" value="1">

<?php for ($i=1;$i<=15;$i++) { ?>

<div class="question-box">

    <textarea name="questionText[]" placeholder="Question"></textarea>

    <div class="choices">
        <input name="choiceA[]" placeholder="Choice A">
        <input name="choiceB[]" placeholder="Choice B">
        <input name="choiceC[]" placeholder="Choice C">
        <input name="choiceD[]" placeholder="Choice D">
    </div>

    <input name="correctAnswer[]" placeholder="Correct Answer">

</div>

<?php } ?>

<button type="submit" name="addQuestion">Add Questions</button>

</form>

</div>

<!-- LIST -->
<div class="box">

<h3>Questions List</h3>

<table>
<tr>
    <th>ID</th>
    <th>Question</th>
    <th>Type</th>
    <th>Answer</th>
    <th>Actions</th>
</tr>

<?php while($row = $questions->fetch_assoc()) { ?>

<tr>
    <td><?= $row['questionID'] ?></td>
    <td><?= htmlspecialchars($row['questionText']) ?></td>
    <td><?= $row['questionType'] ?></td>
    <td><?= htmlspecialchars($row['correctAnswer']) ?></td>

    <td>
        <a class="btn edit"
           href="edit-question.php?id=<?= $row['questionID'] ?>">
           Edit
        </a>

        <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $row['questionID'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

            <button type="submit"
                name="deleteQuestion"
                class="btn delete"
                onclick="return confirm('Delete question?')">
                Delete
            </button>
        </form>
    </td>
</tr>

<?php } ?>

</table>

</div>

</div>

<script>
function toggleChoices(){
    let type = document.getElementById("questionType").value;
    let boxes = document.querySelectorAll(".choices");

    boxes.forEach(b => {
        b.style.display = (type === "multiple_choice") ? "block" : "none";
    });
}
toggleChoices();
</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

</body>
</html>