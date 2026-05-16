<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   DELETE LESSON (SOFT DELETE)
========================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("
        UPDATE lessons
        SET date_deleted = NOW()
        WHERE lessonID = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: lessons.php");
    exit();
}

/* =========================
   FILTER BY SUBJECT
========================= */
$subjectFilter = $_GET['subjectID'] ?? '';

/* =========================
   GET SUBJECTS
========================= */
$subjects = $conn->query("
    SELECT subjectID, subjectName
    FROM subjects
    WHERE date_deleted IS NULL
    ORDER BY subjectName ASC
");

/* =========================
   GET LESSONS
========================= */
$sql = "
    SELECT 
        l.lessonID,
        l.lessonTitle,
        l.lessonDescription,
        l.date_created,
        s.subjectName
    FROM lessons l
    LEFT JOIN subjects s 
        ON l.subjectID = s.subjectID
    WHERE l.date_deleted IS NULL
";

if (!empty($subjectFilter)) {
    $sql .= " AND l.subjectID = ?";
}

$sql .= " ORDER BY l.date_created DESC";

$stmt = $conn->prepare($sql);

if (!empty($subjectFilter)) {
    $stmt->bind_param("i", $subjectFilter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lessons Management</title>

    <style>

        body {
            font-family: Arial;
            margin: 20px;
        }

        .top-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        select,
        button {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .btn {
            padding: 5px 8px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            font-size: 14px;
        }

        .edit {
            background: #007bff;
        }

        .delete {
            background: #dc3545;
        }

        .add {
            background: #28a745;
        }

    </style>
</head>

<body>

<h2>Lessons Management</h2>

<!-- FILTER -->
<form method="GET" class="top-bar">

    <select name="subjectID">

        <option value="">
            -- Filter by Subject --
        </option>

        <?php while ($sub = $subjects->fetch_assoc()) { ?>

            <option
                value="<?= $sub['subjectID'] ?>"
                <?= ($subjectFilter == $sub['subjectID']) ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($sub['subjectName']) ?>
            </option>

        <?php } ?>

    </select>

    <button type="submit">
        Filter
    </button>

    <a class="btn add" href="create-lesson.php">
        + Add Lesson
    </a>

</form>

<!-- TABLE -->
<table>

    <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Lesson Title</th>
        <th>Description</th>
        <th>Date Created</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>

    <tr>

        <td>
            <?= $row['lessonID'] ?>
        </td>

        <td>
            <?= htmlspecialchars($row['subjectName']) ?>
        </td>

        <td>
            <?= htmlspecialchars($row['lessonTitle']) ?>
        </td>

        <td>
            <?= htmlspecialchars($row['lessonDescription']) ?>
        </td>

        <td>
            <?= $row['date_created'] ?>
        </td>

        <td>

            <a
                class="btn add"
                href="add-question.php?lessonID=<?= $row['lessonID'] ?>"
            >
                Add Question
            </a>

            <a
                class="btn edit"
                href="edit-lesson.php?id=<?= $row['lessonID'] ?>"
            >
                Edit
            </a>

            <a
                class="btn delete"
                href="lessons.php?delete=<?= $row['lessonID'] ?>"
                onclick="return confirm('Delete this lesson?')"
            >
                Delete
            </a>

        </td>

    </tr>

    <?php } ?>

</table>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

</body>
</html>