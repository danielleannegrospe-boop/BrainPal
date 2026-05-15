<?php
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../backend/database.php';

/* =========================
   GET COUNTS
========================= */
$users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE date_deleted IS NULL")->fetch_assoc()['total'];
$subjects = $conn->query("SELECT COUNT(*) AS total FROM subjects WHERE date_deleted IS NULL")->fetch_assoc()['total'];
$lessons = $conn->query("SELECT COUNT(*) AS total FROM lessons WHERE date_deleted IS NULL")->fetch_assoc()['total'];
$quiz = $conn->query("SELECT COUNT(*) AS total FROM quiz_attempts")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        body {
            font-family: Arial;
            margin: 20px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 5px;
        }

        .welcome {
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-decoration: none;
            color: black;
            transition: 0.2s;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .count {
            font-size: 28px;
            margin-top: 10px;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn:hover {
            background: #0056b3;
        }

        .danger { background: #dc3545; }
        .danger:hover { background: #a71d2a; }
        .success { background: #28a745; }
        .success:hover { background: #1e7e34; }
    </style>
</head>

<body>

<h1>Admin Dashboard</h1>

<div class="welcome">
    <h2>Welcome, <?php echo $_SESSION['name']; ?></h2>
    <a href="logout.php">Logout</a>
</div>

<!-- CARDS -->
<div class="grid">

    <!-- USERS -->
    <a class="card" href="admin-users.php">
        <div class="title">Users</div>
        <div class="count"><?= $users ?></div>
        <small>Manage system users</small>
    </a>

    <!-- SUBJECTS -->
    <a class="card" href="subjects.php">
        <div class="title">Subjects</div>
        <div class="count"><?= $subjects ?></div>
        <small>Manage subjects</small>
    </a>

    <!-- LESSONS -->
    <a class="card" href="lesson.php">
        <div class="title">Lessons</div>
        <div class="count"><?= $lessons ?></div>
        <small>View & manage lessons</small>
    </a>

    <!-- QUIZ RECORDS -->
    <a class="card" href="quiz-records.php">
        <div class="title">Quiz Records</div>
        <div class="count"><?= $quiz ?></div>
        <small>View quiz attempts</small>
    </a>

    <!-- CREATE LESSON -->
    <a class="card success" href="create-lesson.php">
        <div class="title">Create Lesson</div>
        <div class="count">+</div>
        <small>Add new lesson</small>
    </a>

</div>

</body>
</html>