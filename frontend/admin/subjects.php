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
   ADD SUBJECT
========================= */
if (isset($_POST['addSubject'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6fb;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 18px 25px;
        }

        .container {
            padding: 25px;
        }

        /* BOX */
        .box {
            background: white;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        h2, h3 {
            margin-bottom: 10px;
        }

        label {
            font-size: 13px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        button {
            padding: 10px 14px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #3730a3;
        }

        /* SEARCH BAR */
        .top-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        /* TABLE */
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

        /* BUTTONS */
        .btn {
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            margin-right: 5px;
        }

        .edit { background: #007bff; }
        .delete { background: #dc3545; }

        .btn:hover {
            opacity: 0.85;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        form.search {
            display: flex;
            gap: 10px;
        }

        form.search input {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

    </style>
</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Subjects Management</h2>

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

<!-- SUCCESS / ERROR -->
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

<!-- ADD SUBJECT -->
<div class="box">

    <h3>Add Subject</h3>

    <form method="POST">

    <input type="hidden"
           name="csrf_token"
           value="<?= $csrf ?>">

    <label>Subject Name</label>
        <input type="text" name="subjectName" required>

        <label>Description</label>
        <textarea name="description"></textarea>

        <button type="submit" name="addSubject">
            Add Subject
        </button>

    </form>

</div>

<!-- SEARCH -->
<div class="top-bar">

    <form method="GET" class="search">
        <input type="text"
               name="search"
               placeholder="Search subject..."
               value="<?= htmlspecialchars($search) ?>">

        <button type="submit">Search</button>
    </form>

</div>

<!-- TABLE -->
<div class="box">

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

            <form method="POST"
      action="delete-subject.php"
      style="display:inline;">

    <input type="hidden"
           name="subjectID"
           value="<?= $row['subjectID'] ?>">

    <input type="hidden"
           name="csrf_token"
           value="<?= $csrf ?>">

    <button type="submit"
            class="btn delete"
            onclick="return confirm('Delete this subject?')">

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