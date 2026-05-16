<?php

session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


/* =========================
   ADD SUBJECT
========================= */
if (isset($_POST['addSubject'])) {

    $subjectName = trim($_POST['subjectName']);
    $description = trim($_POST['description']);

    if (!empty($subjectName)) {

        $stmt = $conn->prepare("
            INSERT INTO subjects (subjectName, description)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ss", $subjectName, $description);

        if ($stmt->execute()) {
            $success = "Subject added successfully!";
        } else {
            $error = "Failed to add subject.";
        }

    } else {
        $error = "Subject name is required.";
    }
}

/* =========================
   DELETE SUBJECT (SOFT DELETE)
========================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("
        UPDATE subjects
        SET date_deleted = NOW()
        WHERE subjectID = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: subjects.php");
    exit();
}

/* =========================
   SEARCH
========================= */
$search = $_GET['search'] ?? '';

$sql = "
    SELECT subjectID, subjectName, description, date_created
    FROM subjects
    WHERE date_deleted IS NULL
";

if (!empty($search)) {
    $sql .= " AND subjectName LIKE ?";
}

$sql .= " ORDER BY date_created DESC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $like = "%$search%";
    $stmt->bind_param("s", $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subjects Management</title>

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

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 8px 12px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .btn {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
        }

        .edit { background: #007bff; }
        .delete { background: #dc3545; }
    </style>
</head>

<body>

<h2>Subjects Management</h2>

<!-- SUCCESS / ERROR -->
<?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<!-- ADD SUBJECT -->
<div class="box">
    <h3>Add Subject</h3>

    <form method="POST">

        <label>Subject Name</label>
        <input type="text" name="subjectName" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <button type="submit" name="addSubject">Add Subject</button>

    </form>
</div>

<!-- SEARCH -->
<div class="top-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search subject..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>

<!-- TABLE -->
<table>
    <tr>
        <th>ID</th>
        <th>Subject Name</th>
        <th>Description</th>
        <th>Date Created</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['subjectID'] ?></td>
        <td><?= htmlspecialchars($row['subjectName']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['date_created'] ?></td>
        <td>
            <a class="btn edit"
   href="./edit-subject.php?id=<?= $row['subjectID'] ?>">
   Edit
</a>
            <a class="btn delete"
               href="subjects.php?delete=<?= $row['subjectID'] ?>"
               onclick="return confirm('Delete this subject?')">
               Delete
            </a>
        </td>
    </tr>
    <?php } ?>

</table>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</body>
</html>