<?php
session_start();
require_once '../../backend/database.php';

// CHECK LOGIN
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================
   GET QUESTION DATA
========================= */
if (!isset($_GET['id'])) {
    header("Location: add-question.php");
    exit();
}

$questionID = $_GET['id'];

$stmt = $conn->prepare("
    SELECT * FROM questions WHERE questionID = ? AND date_deleted IS NULL
");
$stmt->bind_param("i", $questionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Question not found.";
    exit();
}

$data = $result->fetch_assoc();

/* =========================
   UPDATE QUESTION
========================= */
if (isset($_POST['updateQuestion'])) {

    $lessonID = $_POST['lessonID'];
    $questionText = $_POST['questionText'];
    $questionType = $_POST['questionType'];
    $difficulty = $_POST['difficulty'];
    $correctAnswer = $_POST['correctAnswer'];
    $points = $_POST['points'] ?? 1;

    $sql = "
        UPDATE questions 
        SET lessonID = ?, questionText = ?, questionType = ?, difficulty = ?, correctAnswer = ?, points = ?
        WHERE questionID = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssii", $lessonID, $questionText, $questionType, $difficulty, $correctAnswer, $points, $questionID);

    if ($stmt->execute()) {
        header("Location: add-question.php");
        exit();
    } else {
        $error = "Failed to update question.";
    }
}

/* =========================
   GET LESSONS
========================= */
$lessons = $conn->query("
    SELECT lessonID, lessonTitle
    FROM lessons
    WHERE date_deleted IS NULL
    ORDER BY lessonTitle ASC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Question</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Edit Question</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="box">
    <form method="POST">

        <label>Lesson</label>
        <select name="lessonID" required>
            <?php while ($row = $lessons->fetch_assoc()) { ?>
                <option value="<?php echo $row['lessonID']; ?>"
                    <?php if ($row['lessonID'] == $data['lessonID']) echo 'selected'; ?>>
                    <?php echo $row['lessonTitle']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Question</label>
        <textarea name="questionText" required><?php echo $data['questionText']; ?></textarea>

        <label>Question Type</label>
        <select name="questionType">
            <option value="multiple_choice" <?php if($data['questionType']=="multiple_choice") echo "selected"; ?>>Multiple Choice</option>
            <option value="identification" <?php if($data['questionType']=="identification") echo "selected"; ?>>Identification</option>
            <option value="enumeration" <?php if($data['questionType']=="enumeration") echo "selected"; ?>>Enumeration</option>
        </select>

        <label>Difficulty</label>
        <select name="difficulty">
            <option value="easy" <?php if($data['difficulty']=="easy") echo "selected"; ?>>Easy</option>
            <option value="medium" <?php if($data['difficulty']=="medium") echo "selected"; ?>>Medium</option>
            <option value="hard" <?php if($data['difficulty']=="hard") echo "selected"; ?>>Hard</option>
        </select>

        <label>Points</label>
        <input type="number" name="points" value="<?php echo $data['points']; ?>" min="1">

        <label>Correct Answer</label>
        <input type="text" name="correctAnswer" value="<?php echo $data['correctAnswer']; ?>" required>

        <button type="submit" name="updateQuestion">Update Question</button>
    </form>
</div>

</body>
</html>