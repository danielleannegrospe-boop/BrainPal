<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF(); 

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int) $_GET['id'];

/* =========================
   FETCH USER
========================= */
$stmt = $conn->prepare("
    SELECT userID, firstName, lastName, email, role
    FROM users
    WHERE userID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

/* =========================
   UPDATE USER
========================= */
if (isset($_POST['update'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);
    $email     = trim($_POST['email']);
    $role      = $_POST['role'];

    if (!empty($firstName) && !empty($lastName) && !empty($email)) {

        $stmt = $conn->prepare("
            UPDATE users
            SET firstName = ?, lastName = ?, email = ?, role = ?
            WHERE userID = ?
        ");

        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $id);
        $stmt->execute();

        header("Location: admin-users.php");
        exit();

    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>

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
            display: flex;
            justify-content: center;
        }

        .box {
            background: white;
            width: 100%;
            max-width: 520px;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            font-size: 13px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
        }

        input:focus, select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 5px rgba(79,70,229,0.2);
        }

        button {
            width: 100%;
            margin-top: 18px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #3730a3;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
            color: #4f46e5;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

    </style>
</head>

<body>

<div class="header">
    <h2>Edit User</h2>
</div>

<div class="container">

<div class="box">

    <h2>Update User 👤</h2>

    <?php if (isset($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <label>First Name</label>
        <input type="text"
               name="firstName"
               value="<?= htmlspecialchars($user['firstName']) ?>"
               required>

        <label>Last Name</label>
        <input type="text"
               name="lastName"
               value="<?= htmlspecialchars($user['lastName']) ?>"
               required>

        <label>Email</label>
        <input type="email"
               name="email"
               value="<?= htmlspecialchars($user['email']) ?>"
               required>

        <label>Role</label>
        <select name="role">
            <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>
                Student
            </option>
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>
                Admin
            </option>
        </select>

        <button type="submit" name="update">
            Update User
        </button>

    </form>

    <a class="back" href="admin-users.php">
        ← Back to Users
    </a>

</div>

</div>

</body>
</html>