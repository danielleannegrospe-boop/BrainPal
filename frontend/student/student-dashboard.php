<?php
session_start();
require_once '../../backend/database.php';

/* =========================
   🔐 AUTH GUARD
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   SAFE NAME DISPLAY
========================= */
$name = $_SESSION['name'] ?? 'Student';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>

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
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 22px;
        }

        .logout {
            background: rgba(255,255,255,0.2);
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            transition: 0.2s;
        }

        .logout:hover {
            background: rgba(255,255,255,0.35);
        }

        .container {
            padding: 30px;
        }

        .welcome {
            margin-bottom: 20px;
            font-size: 18px;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        /* CARD */
        .card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: 0.25s;
            position: relative;
            overflow: hidden;
            display: block;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .desc {
            color: #666;
            font-size: 14px;
        }

        /* ACCENT BARS */
        .quiz::before,
        .records::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
        }

        .quiz::before {
            background: #4f46e5;
        }

        .records::before {
            background: #22c55e;
        }

        /* MAKE BUTTON LOOK LIKE CARD */
        button.card {
            border: none;
            text-align: left;
            cursor: pointer;
            width: 100%;
        }

    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h1>Student Dashboard</h1>
    <a class="logout" href="#" onclick="confirmLogout(event)">Logout</a>
</div>

<div class="container">

    <div class="welcome">
        👋 Welcome, <b><?= htmlspecialchars($name) ?></b>
    </div>

    <!-- CARDS -->
    <div class="grid">

        <!-- TAKE QUIZ -->
        <form action="take-quiz.php" method="GET">
            <input type="hidden" name="start" value="1">

            <button type="submit" class="card quiz">
                <div class="title">Take Quiz</div>
                <div class="desc">Start answering available quizzes and lessons.</div>
            </button>
        </form>

        <!-- QUIZ RECORDS -->
        <a class="card records" href="student-quiz-records.php">
            <div class="title">Quiz Records</div>
            <div class="desc">View your scores, attempts, and results.</div>
        </a>

    </div>

</div>
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