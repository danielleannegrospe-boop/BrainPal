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
        qa.attemptedAt,

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

    ORDER BY qa.attemptedAt DESC
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
        body {
            font-family: Arial;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .score {
            font-weight: bold;
        }

        .good {
            color: green;
        }

        .bad {
            color: red;
        }

        .empty {
            text-align: center;
            color: gray;
        }
    </style>
</head>

<body>

<h2>Quiz Records</h2>

<table>

    <tr>
        <th>Attempt ID</th>
        <th>Student</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Lesson</th>
        <th>Score</th>
        <th>Total</th>
        <th>Percentage</th>
        <th>Date Taken</th>
        <th>Action</th>
    </tr>

    <?php if ($result->num_rows > 0) { ?>

        <?php while ($row = $result->fetch_assoc()) {

            $percent = 0;

            if ($row['totalQuestions'] > 0) {
                $percent = ($row['score'] / $row['totalQuestions']) * 100;
            }
        ?>

        <tr>

            <td>
                <?= $row['attemptID'] ?>
            </td>

            <td>
                <?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['email']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['subjectName']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['lessonTitle']) ?>
            </td>

            <td class="score">
                <?= $row['score'] ?>
            </td>

            <td>
                <?= $row['totalQuestions'] ?>
            </td>

            <td class="<?= ($percent >= 75) ? 'good' : 'bad' ?>">
                <?= round($percent, 2) ?>%
            </td>

            <td>
                <?= $row['attemptedAt'] ?>
            </td>

            <td>
                <a href="view-attempt.php?id=<?= $row['attemptID'] ?>">
                    View Details
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
        '\nPercentage: ' + data.percentage + '%' +
        '\nSubmitted: ' + data.attemptedAt
    );

    location.reload();
});

</script>

</body>
</html>