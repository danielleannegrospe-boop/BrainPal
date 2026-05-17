<?php
session_start();
require_once '../../backend/database.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {

        $stmt = $conn->prepare("
            SELECT userID, firstName, middleInitial, lastName, suffix, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                // ✅ IMPORTANT SESSION VALUES
                $_SESSION['userID'] = (int)$user['userID'];
                $_SESSION['role'] = $user['role'];

                $_SESSION['name'] = trim(
                    $user['firstName'] . ' ' .
                    $user['middleInitial'] . ' ' .
                    $user['lastName'] . ' ' .
                    $user['suffix']
                );

                // DEBUG (optional)
                // var_dump($_SESSION); exit;

                // ROLE REDIRECT
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/admin-dashboard.php");
                } else {
                    header("Location: ../student/student-dashboard.php");
                }

                exit();

            } else {
                $error = "Incorrect password";
            }

        } else {
            $error = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
        }

        .login-container {
            background: #fff;
            padding: 40px;
            width: 100%;
            max-width: 380px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        input:focus {
            border-color: #4f46e5;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-login {
            background: #4f46e5;
            color: white;
        }

        .btn-login:hover {
            background: #3730a3;
        }

        .btn-register {
            background: transparent;
            border: 1px solid #4f46e5;
            color: #4f46e5;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h2>Welcome Back 👋</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Password" required>

    <button type="submit" name="login" class="btn-login">
        Login
    </button>

</form>

    <a href="register.php">
        <button type="button" class="btn-register">
            Create Account
        </button>
    </a>

</div>

</body>
</html>