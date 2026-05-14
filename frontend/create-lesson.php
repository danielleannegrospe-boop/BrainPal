<?php
session_start();
require_once '../backend/database.php';

// CHECK LOGIN
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   CREATE LESSON
========================= */
if (isset($_POST['createLesson'])) {

    $subjectID = $_POST['subjectID'];
    $title = $_POST['lessonTitle'];
    $description = $_POST['description'];

    $sql = "INSERT INTO lessons (subjectID, lessonTitle, description, createdBy)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $subjectID, $title, $description, $userID);

    if ($stmt->execute()) {
        $success = "Lesson created successfully!";
    } else {
        $error = "Failed to create lesson.";
    }
}

/* =========================
   FETCH ACTIVE LESSONS ONLY
========================= */
$sql = "
    SELECT 
        l.lessonID,
        l.lessonTitle,
        l.description,
        l.date_created,
        s.subjectName,
        CONCAT(u.firstName, ' ', u.lastName) AS fullName
    FROM lessons l
    LEFT JOIN users u ON l.createdBy = u.userID
    LEFT JOIN subjects s ON l.subjectID = s.subjectID
    WHERE l.date_deleted IS NULL
    ORDER BY l.date_created DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Lesson</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        input, textarea, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 15px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
    </style>
</head>
<body>

<h2>Create Lesson</h2>

<?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<div class="box">
    <form method="POST">

        <label>Subject</label>
        <select name="subjectID" required>
            <option value="1">ITWS03: Web System Technologies</option>
            <option value="2">ITWS04: Web Vulnerabilities</option>
            <option value="3">ITWS05: Mobile Application Development</option>
        </select>

        <label>Lesson Title</label>
        <input type="text" name="lessonTitle" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <button type="submit" name="createLesson">Create Lesson</button>
    </form>
</div>

<h3>Active Lessons</h3>

<table>
    <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Title</th>
        <th>Description</th>
        <th>Created By</th>
        <th>Date Created</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['lessonID']; ?></td>
        <td><?php echo $row['subjectName']; ?></td>
        <td><?php echo $row['lessonTitle']; ?></td>
        <td><?php echo $row['description']; ?></td>
        <td><?php echo $row['fullName']; ?></td>
        <td><?php echo $row['date_created']; ?></td>
        <td>
            <a href="edit-lesson.php?id=<?php echo $row['lessonID']; ?>">Edit</a> |
            <a href="delete-lesson.php?id=<?php echo $row['lessonID']; ?>"
               onclick="return confirm('Delete this lesson?')">
               Delete
            </a>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>