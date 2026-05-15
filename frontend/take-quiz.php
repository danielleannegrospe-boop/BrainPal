<?php
session_start();
require_once '../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   SELECTED VALUES
========================= */
$selectedSubject = $_GET['subject'] ?? null;
$selectedLesson = $_GET['lessonID'] ?? null;
$selectedDifficulty = $_GET['difficulty'] ?? null;
$selectedType = $_GET['questionType'] ?? null;

/* =========================
   GET SUBJECTS (WITH DESCRIPTION FIX)
========================= */
$subjects = $conn->query("
    SELECT subjectID, subjectName, description
    FROM subjects
    WHERE date_deleted IS NULL
");

/* =========================
   GET LESSONS BASED ON SUBJECT
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
   GET QUESTIONS
========================= */
$questions = null;

if ($selectedLesson && $selectedDifficulty && $selectedType) {

    $stmt = $conn->prepare("
        SELECT questionID, questionText
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
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-top: 15px; }
        select, input { width: 100%; padding: 8px; margin-top: 5px; }
        button { padding: 10px 15px; margin-top: 10px; }
        small { color: #666; }
    </style>
</head>
<body>

<h2>Take Quiz</h2>

<!-- =========================
     FORM
========================= -->
<form method="GET">
<div class="box">

    <!-- SUBJECT -->
    <label>Subject</label>
    <select name="subject" onchange="this.form.submit()" required>
        <option value="">-- Choose Subject --</option>

        <?php while ($s = $subjects->fetch_assoc()) { ?>
            <option value="<?= $s['subjectID'] ?>"
                <?= ($selectedSubject == $s['subjectID']) ? 'selected' : '' ?>>

                <?= $s['subjectName'] ?>
                <?php if (!empty($s['description'])): ?>
                    - <?= $s['description'] ?>
                <?php endif; ?>

            </option>
        <?php } ?>

    </select>

    <!-- LESSON -->
    <label>Lesson</label>
    <select name="lessonID" required>
        <option value="">-- Choose Lesson --</option>

        <?php if ($lessons): ?>
            <?php while ($l = $lessons->fetch_assoc()) { ?>
                <option value="<?= $l['lessonID'] ?>"
                    <?= ($selectedLesson == $l['lessonID']) ? 'selected' : '' ?>>
                    <?= $l['lessonTitle'] ?>
                </option>
            <?php } ?>
        <?php endif; ?>

    </select>

    <!-- DIFFICULTY -->
    <label>Difficulty</label>
    <select name="difficulty" required>
        <option value="">-- Choose Difficulty --</option>
        <option value="easy" <?= ($selectedDifficulty=="easy")?'selected':'' ?>>Easy</option>
        <option value="medium" <?= ($selectedDifficulty=="medium")?'selected':'' ?>>Medium</option>
        <option value="hard" <?= ($selectedDifficulty=="hard")?'selected':'' ?>>Hard</option>
    </select>

    <!-- TYPE -->
    <label>Question Type</label>
    <select name="questionType" required>
        <option value="">-- Choose Type --</option>
        <option value="multiple_choice" <?= ($selectedType=="multiple_choice")?'selected':'' ?>>Multiple Choice</option>
        <option value="identification" <?= ($selectedType=="identification")?'selected':'' ?>>Identification</option>
        <option value="enumeration" <?= ($selectedType=="enumeration")?'selected':'' ?>>Enumeration</option>
    </select>

    <button type="submit">Start Quiz</button>

</div>
</form>

<!-- =========================
     QUESTIONS
========================= -->
<?php if ($questions && $selectedLesson && $selectedDifficulty && $selectedType): ?>

<form method="POST" action="submit-quiz.php">

    <input type="hidden" name="subject" value="<?= $selectedSubject ?>">
    <input type="hidden" name="lessonID" value="<?= $selectedLesson ?>">
    <input type="hidden" name="difficulty" value="<?= $selectedDifficulty ?>">
    <input type="hidden" name="questionType" value="<?= $selectedType ?>">

    <div class="box">

        <?php while ($q = $questions->fetch_assoc()) { ?>

            <div style="margin-bottom:15px; padding:10px; border:1px solid #ddd;">
                <p><b><?= htmlspecialchars($q['questionText']) ?></b></p>

                <input type="hidden" name="questionID[]" value="<?= $q['questionID'] ?>">
                <input type="text" name="answer[]" placeholder="Your answer" required>
            </div>

        <?php } ?>

        <button type="submit">Submit Quiz</button>

    </div>

</form>

<?php endif; ?>

</body>
</html>