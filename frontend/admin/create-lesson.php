<?php

session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   CREATE LESSON (CSRF SECURED)
========================= */
if (isset($_POST['createLesson'])) {

    // CSRF VALIDATION
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $subjectID = $_POST['subjectID'] ?? null;
    $title = trim($_POST['lessonTitle'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($subjectID && $title) {

        $sql = "
            INSERT INTO lessons (
                subjectID,
                lessonTitle,
                lessonDescription,
                createdBy
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $subjectID, $title, $description, $userID);

        if ($stmt->execute()) {
            $success = "Lesson created successfully!";
        } else {
            $error = "Failed to create lesson.";
        }

    } else {
        $error = "Please fill required fields.";
    }
}

/* =========================
   FETCH LESSONS
========================= */
$sql = "
    SELECT 
        l.lessonID,
        l.lessonTitle,
        l.lessonDescription,
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
        body {
            font-family: Arial;
            margin: 20px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
        }

        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
        }
    </style>
</head>

<body>

<h2>Create Lesson</h2>

<?php if (isset($success)) : ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<?php if (isset($error)) : ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<div class="box">

    <form method="POST">

        <!-- CSRF TOKEN -->
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <!-- SUBJECT -->
        <label>Subject</label>

        <select name="subjectID" id="subjectSelect" required onchange="showSubjectDesc(this)">
            <option value="">-- Choose Subject --</option>

            <?php
            $subjects = $conn->query("
                SELECT subjectID, subjectName, description
                FROM subjects
                WHERE date_deleted IS NULL
            ");

            $subjectData = [];

            while ($sub = $subjects->fetch_assoc()) {

                $subjectData[$sub['subjectID']] = $sub['description'] ?? '';
            ?>
                <option value="<?= $sub['subjectID'] ?>">
                    <?= htmlspecialchars($sub['subjectName']) ?>
                </option>
            <?php } ?>
        </select>

        <!-- SUBJECT DESCRIPTION -->
        <div id="subjectDesc"
             style="margin-top:10px; padding:10px; background:#f5f5f5; display:none;">
        </div>

        <!-- LESSON TITLE -->
        <label>Lesson Title</label>
        <input type="text" name="lessonTitle" required>

        <!-- DESCRIPTION -->
        <label>Lesson Description</label>
        <textarea name="description"></textarea>

        <button type="submit" name="createLesson">
            Create Lesson
        </button>

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

        <td><?= $row['lessonID'] ?></td>

        <td><?= htmlspecialchars($row['subjectName']) ?></td>

        <td><?= htmlspecialchars($row['lessonTitle']) ?></td>

        <td><?= htmlspecialchars($row['lessonDescription']) ?></td>

        <td><?= htmlspecialchars($row['fullName']) ?></td>

        <td><?= $row['date_created'] ?></td>

        <td class="actions">

            <a href="edit-lesson.php?id=<?= $row['lessonID'] ?>">
                Edit
            </a>

            <a href="add-question.php?lessonID=<?= $row['lessonID'] ?>">
                Add Question
            </a>

            <a href="delete-lesson.php?id=<?= $row['lessonID'] ?>"
               onclick="return confirm('Delete this lesson?')">
                Delete
            </a>

        </td>

    </tr>

    <?php } ?>

</table>

<script>

const subjectDescriptions = <?= json_encode($subjectData) ?>;

function showSubjectDesc(select) {

    const descBox = document.getElementById('subjectDesc');

    const subjectID = select.value;

    if (subjectDescriptions[subjectID]) {

        descBox.style.display = 'block';

        descBox.innerHTML =
            "<strong>Description:</strong><br>" +
            subjectDescriptions[subjectID];

    } else {

        descBox.style.display = 'none';
        descBox.innerHTML = '';
    }
}

</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

</body>
</html>