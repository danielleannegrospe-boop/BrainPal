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
        body{
            font-family: Arial;
            margin: 20px;
            background: #f5f5f5;
        }

        h1{ margin-bottom: 5px; }

        .welcome{ margin-bottom: 20px; }

        .grid{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card{
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-decoration: none;
            color: black;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: 0.2s;
        }

        .card:hover{
            transform: scale(1.03);
        }

        .title{
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .desc{
            color: #555;
        }

        .quiz{ border-left: 6px solid #007bff; }
        .records{ border-left: 6px solid #28a745; }

        .logout{
            display: inline-block;
            margin-top: 20px;
            padding: 8px 12px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout:hover{
            background: #a71d2a;
        }
    </style>
</head>

<body>

<h1>Student Dashboard</h1>

<div class="welcome">
    <h2>
        Welcome, <?= htmlspecialchars($name) ?>
    </h2>
</div>

<!-- CARDS -->
<div class="grid">

    <!-- TAKE QUIZ -->
    <form action="take-quiz.php" method="GET">
    <input type="hidden" name="start" value="1">

    <button type="submit" class="card quiz" style="border:none; width:100%; text-align:left;">
        <div class="title">Take Quiz</div>
        <div class="desc">Start answering available quizzes and lessons.</div>
    </button>
</form>

    <!-- QUIZ RECORDS -->
    <a class="card records" href="student-quiz-records.php">
        <div class="title">View Quiz Records</div>
        <div class="desc">
            View your quiz scores, attempts, and detailed quiz results.
        </div>
    </a>

</div>

<br>

<a class="logout" href="../auth/logout.php">
    Logout
</a>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</body>
</html>