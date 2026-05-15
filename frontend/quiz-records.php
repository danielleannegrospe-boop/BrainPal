<?php
session_start();
require_once '../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
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
    LEFT JOIN users u ON qa.studentID = u.userID
    LEFT JOIN lessons l ON qa.lessonID = l.lessonID
    LEFT JOIN subjects s ON l.subjectID = s.subjectID

    ORDER BY qa.submittedAt DESC
";

$result = $conn->query($sql);
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

        .good { color: green; }
        .bad { color: red; }
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

    <?php while ($row = $result->fetch_assoc()) {

        $percent = 0;
        if ($row['totalQuestions'] > 0) {
            $percent = ($row['score'] / $row['totalQuestions']) * 100;
        }
    ?>

    <tr>
        <td><?= $row['attemptID'] ?></td>

        <td>
            <?= $row['firstName'] . ' ' . $row['lastName'] ?>
        </td>

        <td><?= $row['email'] ?></td>

        <td><?= $row['subjectName'] ?></td>

        <td><?= $row['lessonTitle'] ?></td>

        <td class="score"><?= $row['score'] ?></td>

        <td><?= $row['totalQuestions'] ?></td>

        <td class="<?= ($percent >= 75) ? 'good' : 'bad' ?>">
            <?= round($percent, 2) ?>%
        </td>

        <td><?= $row['submittedAt'] ?></td>

        <td>
            <a href="view-attempt.php?id=<?= $row['attemptID'] ?>">
                View Details
            </a>
        </td>
    </tr>

    <?php } ?>

</table>

</body>
</html>