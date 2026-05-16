<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$userID = $_SESSION['userID'];

/* =========================
   GET ONLY CURRENT STUDENT ATTEMPTS
========================= */
$stmt = $conn->prepare("
    SELECT 
        qa.attemptID,
        qa.score,
        qa.totalQuestions,
        qa.attemptedAt,

        l.lessonTitle,
        s.subjectName

    FROM quiz_attempts qa

    LEFT JOIN lessons l 
        ON qa.lessonID = l.lessonID

    LEFT JOIN subjects s 
        ON l.subjectID = s.subjectID

    WHERE qa.studentID = ?

    ORDER BY qa.attemptedAt DESC
");

$stmt->bind_param("i", $userID);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Quiz Records</title>

    <style>

        body{
            font-family: Arial;
            margin: 20px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td{
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th{
            background: #f5f5f5;
        }

        .good{
            color: green;
            font-weight: bold;
        }

        .bad{
            color: red;
            font-weight: bold;
        }

        .btn{
            background: #007bff;
            color: white;
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 5px;
        }

        .empty{
            text-align: center;
            color: gray;
        }

    </style>

</head>

<body>

<h2>My Quiz Records</h2>

<a href="student-dashboard.php">← Back to Dashboard</a>

<table>

    <tr>
        <th>Attempt ID</th>
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

            <td><?= $row['attemptID'] ?></td>

            <td><?= htmlspecialchars($row['subjectName']) ?></td>

            <td><?= htmlspecialchars($row['lessonTitle']) ?></td>

            <td><?= $row['score'] ?></td>

            <td><?= $row['totalQuestions'] ?></td>

            <td class="<?= ($percent >= 75) ? 'good' : 'bad' ?>">
                <?= round($percent, 2) ?>%
            </td>

            <td><?= $row['attemptedAt'] ?></td>

            <td>
                <a class="btn"
                   href="student-view-attempt.php?id=<?= $row['attemptID'] ?>">
                   View Details
                </a>
            </td>

        </tr>

        <?php } ?>

    <?php } else { ?>

        <tr>
            <td colspan="8" class="empty">
                No quiz attempts found.
            </td>
        </tr>

    <?php } ?>

</table>

</body>
</html>