<?php

session_start();
require_once '../../backend/database.php';
require_once '../../backend/pusher.php';
require_once '../../backend/csrf.php';

$csrf = generateCSRF();

if (isset($_POST['register'])) {

    // CSRF CHECK
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $firstName = trim($_POST['firstName']);
    $middleInitial = trim($_POST['middleInitial']);
    $lastName = trim($_POST['lastName']);
    $suffix = trim($_POST['suffix']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // CHECK IF EMAIL EXISTS FIRST
    $checkStmt = $conn->prepare("
        SELECT userID 
        FROM users 
        WHERE email = ?
        LIMIT 1
    ");

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {

        $error = "Email already exists";

    } else {

        // HASH PASSWORD
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            INSERT INTO users (
                firstName,
                middleInitial,
                lastName,
                suffix,
                email,
                password,
                role
            )
            VALUES (?, ?, ?, ?, ?, ?, 'student')
        ");

        $stmt->bind_param(
            "ssssss",
            $firstName,
            $middleInitial,
            $lastName,
            $suffix,
            $email,
            $hashedPassword
        );

        if ($stmt->execute()) {

            /* =========================
               REALTIME USER REGISTER
            ========================= */

            $pusher->trigger(
                'user-channel',
                'user-registered',
                [
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'registeredAt' => date('Y-m-d H:i:s')
                ]
            );

            header("Location: login.php");
            exit();

        } else {
            $error = "Registration failed";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>

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

        .register-container {
            background: #fff;
            padding: 35px;
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .row {
            display: flex;
            gap: 10px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 5px rgba(79,70,229,0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-register {
            background: #4f46e5;
            color: white;
        }

        .btn-register:hover {
            background: #3730a3;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }

        .link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .link a {
            color: #4f46e5;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="register-container">

    <h2>Create Account ✨</h2>

    <?php if (isset($error)) : ?>
        <div class="error">
            <?= $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div class="row">
            <input type="text" name="firstName" placeholder="First Name" required>
            <input type="text" name="middleInitial" placeholder="MI">
        </div>

        <input type="text" name="lastName" placeholder="Last Name" required>

        <input type="text" name="suffix" placeholder="Suffix (Jr, Sr, etc)">

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register" class="btn-register">
            Register
        </button>

    </form>

    <div class="link">
        Already have an account?
        <a href="login.php">Login here</a>
    </div>

</div>

</body>
</html>