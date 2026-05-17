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

        /* TOP BAR */
        .top-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        select, button {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        button {
            background: #4f46e5;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #3730a3;
        }

        .add-btn {
            background: #28a745;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
        }

        .add-btn:hover {
            background: #1e7e34;
        }

        /* TABLE CARD */
        .table-box {
            background: white;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border-bottom: 1px solid #eee;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
        }

        /* ACTION BUTTONS */
        .btn {
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            color: white;
            display: inline-block;
            margin-right: 5px;
        }

        .edit { background: #007bff; }
        .delete { background: #dc3545; }
        .addq { background: #28a745; }

        .btn:hover {
            opacity: 0.85;
        }

        .title {
            margin-bottom: 15px;
        }

    </style>
</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Lessons Management</h2>

    <a href="../admin/admin-dashboard.php"
       style="
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
       ">
        ⬅ Back to Dashboard
    </a>
</div>

<div class="container">

<h3 class="title">All Lessons</h3>

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

    <button type="submit">Filter</button>

    <a class="add-btn" href="create-lesson.php">
        + Add Lesson
    </a>

</form>

<!-- TABLE -->
<div class="table-box">

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
        <td><?= $row['lessonID'] ?></td>
        <td><?= htmlspecialchars($row['subjectName']) ?></td>
        <td><?= htmlspecialchars($row['lessonTitle']) ?></td>
        <td><?= htmlspecialchars($row['lessonDescription']) ?></td>
        <td><?= $row['date_created'] ?></td>

        <td>

            <a class="btn addq"
               href="add-question.php?lessonID=<?= $row['lessonID'] ?>">
                Add
            </a>

            <a class="btn edit"
               href="edit-lesson.php?id=<?= $row['lessonID'] ?>">
                Edit
            </a>

            <form method="POST"
      action="delete-lesson.php"
      style="display:inline;">

    <input type="hidden"
           name="lessonID"
           value="<?= $row['lessonID'] ?>">

    <input type="hidden"
           name="csrf_token"
           value="<?= $csrf ?>">

    <button type="submit"
            class="btn delete"
            onclick="return confirm('Delete this lesson?')">

        Delete

    </button>

</form>

        </td>
    </tr>

    <?php } ?>

</table>

</div>

</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

</body>
</html>