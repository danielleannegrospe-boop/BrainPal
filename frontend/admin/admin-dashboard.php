<?php
session_start();

// 🔐 AUTH GUARD (NO BYPASS)
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../backend/database.php';
$env = require_once '../../backend/pusher.php';

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

    <!-- PUSHER LIBRARY -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

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

        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 { font-size: 22px; }

        .logout {
            background: rgba(255,255,255,0.2);
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
        }

        .container { padding: 30px; }

        .welcome { margin-bottom: 20px; font-size: 18px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            position: relative;
        }

        .card:hover { transform: translateY(-5px); }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .count {
            font-size: 32px;
            font-weight: bold;
            color: #4f46e5;
        }

        small { color: #666; display:block; margin-top:8px; }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: #4f46e5;
        }

        .card:nth-child(2)::before { background: #06b6d4; }
        .card:nth-child(3)::before { background: #22c55e; }
        .card:nth-child(4)::before { background: #f59e0b; }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h1>Admin Dashboard</h1>
    <a class="logout" href="#" onclick="confirmLogout(event)">Logout</a>
</div>

<div class="container">

    <div class="welcome">
        👋 Welcome, <b><?= htmlspecialchars($_SESSION['name']) ?></b>
    </div>

    <!-- CARDS -->
    <div class="grid">

        <a class="card" href="../admin/admin-users.php">
            <div class="title">Users</div>
            <div class="count"><?= $users ?></div>
            <small>Manage system users</small>
        </a>

        <a class="card" href="../admin/subjects.php">
            <div class="title">Subjects</div>
            <div class="count"><?= $subjects ?></div>
            <small>Manage subjects</small>
        </a>

        <a class="card" href="../admin/lessons.php">
            <div class="title">Lessons</div>
            <div class="count"><?= $lessons ?></div>
            <small>View & manage lessons</small>
        </a>

        <a class="card" href="../admin/quiz-records.php">
            <div class="title">Quiz Records</div>
            <div class="count"><?= $quiz ?></div>
            <small>View quiz attempts</small>
        </a>

    </div>
</div>

<!-- =========================
     PUSHER REAL-TIME SCRIPT
========================= -->
<script>
const PUSHER_APP_KEY = "<?= htmlspecialchars($env['PUSHER_APP_KEY']) ?>";
const PUSHER_CLUSTER = "<?= htmlspecialchars($env['PUSHER_APP_CLUSTER']) ?>";

Pusher.logToConsole = false;

var pusher = new Pusher(PUSHER_APP_KEY, {
    cluster: PUSHER_CLUSTER
});

var channel = pusher.subscribe("quiz-channel");

channel.bind("quiz-submitted", function(data) {

    console.log("Real-time quiz submission:", data);

    alert(
        "📢 New Quiz Submitted!\n\n" +
        "Student ID: " + data.studentID + "\n" +
        "Score: " + data.score + "/" + data.total + "\n" +
        "Status: " + data.status
    );

    // auto update quiz count
    let quizCount = document.querySelectorAll(".count")[3];

    if (quizCount) {
        quizCount.innerText = parseInt(quizCount.innerText) + 1;
    }
});
</script>
<script>
function confirmLogout(event) {
    event.preventDefault();

    let confirmAction = confirm("Are you sure you want to logout?");

    if (confirmAction) {
        window.location.href = "../auth/logout.php";
    } else {
        // cancel = do nothing
        return false;
    }
}
</script>
</body>
</html>