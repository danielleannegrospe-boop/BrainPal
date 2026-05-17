<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET QUIZ ATTEMPTS
========================= */
$sql = "
    SELECT 
        qa.attemptID,
        qa.score,
        qa.totalQuestions,
        qa.submittedAt,

        u.firstName,
        u.lastName,
        u.email,

        l.lessonTitle,
        s.subjectName

    FROM quiz_attempts qa

    LEFT JOIN users u 
        ON qa.studentID = u.userID

    LEFT JOIN lessons l 
        ON qa.lessonID = l.lessonID

    LEFT JOIN subjects s 
        ON l.subjectID = s.subjectID

    ORDER BY qa.submittedAt DESC
";

/* IMPORTANT */
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Records</title>

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

        h2 {
            margin-bottom: 15px;
        }

        /* TABLE CARD */
        .table-box {
            background: white;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            border-bottom: 1px solid #eee;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
            font-weight: bold;
        }

        /* SCORE BADGES */
        .score {
            font-weight: bold;
        }

        .good {
            color: #16a34a;
        }

        .bad {
            color: #dc2626;
        }

        /* ACTION */
        a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* EMPTY STATE */
        .empty {
            text-align: center;
            color: gray;
            padding: 20px;
        }

    </style>
</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Quiz Records</h2>

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

<div class="table-box">

<table>

    <tr>
        <th>ID</th>
        <th>Student</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Lesson</th>
        <th>Score</th>
        <th>Total</th>
        <th>Percentage</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if ($result->num_rows > 0) { ?>

        <?php while ($row = $result->fetch_assoc()) {

            $percent = ($row['totalQuestions'] > 0)
                ? ($row['score'] / $row['totalQuestions']) * 100
                : 0;
        ?>

        <tr>

            <td><?= $row['attemptID'] ?></td>

            <td>
                <?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?>
            </td>

            <td><?= htmlspecialchars($row['email']) ?></td>

            <td><?= htmlspecialchars($row['subjectName']) ?></td>

            <td><?= htmlspecialchars($row['lessonTitle']) ?></td>

            <td class="score"><?= $row['score'] ?></td>

            <td><?= $row['totalQuestions'] ?></td>

            <td class="<?= ($percent >= 75) ? 'good' : 'bad' ?>">
                <?= round($percent, 2) ?>%
            </td>

            <td><?= $row['submittedAt'] ?></td>

            <td>
                <a href="view-attempt.php?id=<?= $row['attemptID'] ?>">
                    View
                </a>
            </td>

        </tr>

        <?php } ?>

    <?php } else { ?>

        <tr>
            <td colspan="10" class="empty">
                No quiz attempts found.
            </td>
        </tr>

    <?php } ?>

</table>

</div>

</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
Pusher.logToConsole = true;

var pusher = new Pusher('c96e86af5d96e7dd90f7', {
    cluster: 'ap1'
});

var channel = pusher.subscribe('quiz-channel');

channel.bind('quiz-submitted', function(data) {

    alert(
        'New Quiz Submission!\n\n' +
        'Attempt ID: ' + data.attemptID +
        '\nScore: ' + data.score + '/' + data.total +
        '\nPercentage: ' + data.percentage + '%'
    );

    location.reload();
});
</script>

</body>
</html>