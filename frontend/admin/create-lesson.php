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

        /* HEADER */
        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 18px 25px;
        }

        .container {
            padding: 25px;
        }

        h2, h3 {
            margin-bottom: 15px;
        }

        /* CARD */
        .box {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            font-size: 13px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
        }

        textarea {
            resize: none;
        }

        button {
            margin-top: 15px;
            padding: 10px 14px;
            border: none;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #3730a3;
        }

        /* SUBJECT DESC */
        #subjectDesc {
            margin-top: 10px;
            padding: 12px;
            background: #f8fafc;
            border-left: 4px solid #4f46e5;
            border-radius: 10px;
            display: none;
            font-size: 14px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        th {
            background: #4f46e5;
            color: white;
            padding: 12px;
            font-size: 13px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f9f9ff;
        }

        /* ACTIONS */
        .actions a {
            text-decoration: none;
            margin-right: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .actions a:nth-child(1) { color: #2563eb; }
        .actions a:nth-child(2) { color: #16a34a; }
        .actions a:nth-child(3) { color: #dc2626; }

        .actions a:hover {
            opacity: 0.7;
        }

    </style>
</head>

<body>

<div class="header">
    <h2>Create Lesson</h2>
</div>

<div class="container">

<?php if (isset($success)) : ?>
    <p style="color:green; margin-bottom:10px;"><?= $success ?></p>
<?php endif; ?>

<?php if (isset($error)) : ?>
    <p style="color:red; margin-bottom:10px;"><?= $error ?></p>
<?php endif; ?>

<!-- FORM CARD -->
<div class="box">

    <form method="POST">

        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

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

        <div id="subjectDesc"></div>

        <label>Lesson Title</label>
        <input type="text" name="lessonTitle" required>

        <label>Lesson Description</label>
        <textarea name="description" rows="4"></textarea>

        <button type="submit" name="createLesson">
            Create Lesson
        </button>

    </form>

</div>

<!-- TABLE -->
<h3>Active Lessons</h3>

<table>

<tr>
    <th>ID</th>
    <th>Subject</th>
    <th>Title</th>
    <th>Description</th>
    <th>Created By</th>
    <th>Date</th>
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
        <a href="edit-lesson.php?id=<?= $row['lessonID'] ?>">Edit</a>
        <a href="add-question.php?lessonID=<?= $row['lessonID'] ?>">Questions</a>
        <a href="delete-lesson.php?id=<?= $row['lessonID'] ?>">Delete</a>
    </td>
</tr>

<?php } ?>

</table>

</div>

<script>
const subjectDescriptions = <?= json_encode($subjectData) ?>;

function showSubjectDesc(select) {
    const descBox = document.getElementById('subjectDesc');
    const id = select.value;

    if (subjectDescriptions[id]) {
        descBox.style.display = 'block';
        descBox.innerHTML = "<b>Description:</b><br>" + subjectDescriptions[id];
    } else {
        descBox.style.display = 'none';
        descBox.innerHTML = '';
    }
}
</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

</body>
</html>