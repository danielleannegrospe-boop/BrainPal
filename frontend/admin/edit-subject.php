<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET ID
========================= */
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: subjects.php");
    exit();
}

/* =========================
   FETCH SUBJECT
========================= */
$stmt = $conn->prepare("
    SELECT subjectID, subjectName, description
    FROM subjects
    WHERE subjectID = ? AND date_deleted IS NULL
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    die("Subject not found.");
}

/* =========================
   UPDATE SUBJECT (CSRF SECURED)
========================= */
if (isset($_POST['updateSubject'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $subjectName = trim($_POST['subjectName']);
    $description = trim($_POST['description']);

    if (!empty($subjectName)) {

        $stmt = $conn->prepare("
            UPDATE subjects
            SET subjectName = ?, description = ?
            WHERE subjectID = ?
        ");

        $stmt->bind_param("ssi", $subjectName, $description, $id);
        $stmt->execute();

        header("Location: subjects.php");
        exit();

    } else {
        $error = "Subject name is required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Subject</title>

    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 15px;
            max-width: 500px;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Edit Subject</h2>

<?php if (isset($error)) : ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<div class="box">

    <form method="POST">

        <!-- CSRF TOKEN -->
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <label>Subject Name</label>
        <input type="text"
               name="subjectName"
               value="<?= htmlspecialchars($subject['subjectName']) ?>"
               required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($subject['description']) ?></textarea>

        <button type="submit" name="updateSubject">
            Update Subject
        </button>

    </form>

</div>

</body>
</html>