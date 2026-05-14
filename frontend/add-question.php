<?php
session_start();
require_once '../backend/database.php';

// CHECK LOGIN
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================
   DELETE QUESTION (SOFT DELETE)
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("UPDATE questions SET date_deleted = NOW() WHERE questionID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: add-question.php");
    exit();
}

/* =========================
   ADD QUESTION
========================= */
if (isset($_POST['addQuestion'])) {

    $lessonID = $_POST['lessonID'];
    $questionText = $_POST['questionText'];
    $questionType = $_POST['questionType'];
    $difficulty = $_POST['difficulty'];
    $correctAnswer = $_POST['correctAnswer'];
    $points = $_POST['points'] ?? 1;

    $sql = "INSERT INTO questions 
            (lessonID, questionText, questionType, difficulty, correctAnswer, points)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $lessonID, $questionText, $questionType, $difficulty, $correctAnswer, $points);

    if ($stmt->execute()) {
        header("Location: add-question.php"); // prevent duplicate insert
        exit();
    } else {
        $error = "Failed to add question.";
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

/* =========================
   GET QUESTIONS
========================= */
$questions = $conn->query("
    SELECT q.*, l.lessonTitle
    FROM questions q
    LEFT JOIN lessons l ON q.lessonID = l.lessonID
    WHERE q.date_deleted IS NULL
    ORDER BY q.questionID DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 15px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        a { margin-right: 8px; text-decoration: none; }
    </style>
</head>
<body>

<h2>Add Question</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="box">
    <form method="POST">

        <label>Select Lesson</label>
        <select name="lessonID" required>
            <option value="">-- Choose Lesson --</option>
            <?php while ($row = $lessons->fetch_assoc()) { ?>
                <option value="<?php echo $row['lessonID']; ?>">
                    <?php echo $row['lessonTitle']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Question</label>
        <textarea name="questionText" required></textarea>

        <label>Question Type</label>
        <select name="questionType" required>
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
        <input type="number" name="points" value="1" min="1" required>

        <label>Correct Answer</label>
        <input type="text" name="correctAnswer" required>

        <button type="submit" name="addQuestion">Add Question</button>
    </form>
</div>

<h3>All Questions</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Lesson</th>
        <th>Question</th>
        <th>Type</th>
        <th>Difficulty</th>
        <th>Points</th>
        <th>Correct Answer</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $questions->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['questionID']; ?></td>
        <td><?php echo $row['lessonTitle']; ?></td>
        <td><?php echo $row['questionText']; ?></td>
        <td><?php echo $row['questionType']; ?></td>
        <td><?php echo $row['difficulty']; ?></td>
        <td><?php echo $row['points']; ?></td>
        <td><?php echo $row['correctAnswer']; ?></td>
        <td>
            <a href="edit-question.php?id=<?php echo $row['questionID']; ?>">Edit</a>
            <a href="add-question.php?delete=<?php echo $row['questionID']; ?>"
               onclick="return confirm('Delete this question?')">
               Delete
            </a>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>