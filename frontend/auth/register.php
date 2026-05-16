<?php

session_start();
require_once '../../backend/database.php';

if (isset($_POST['register'])) {

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

            // redirect to login after success
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
</head>
<body>

<h2>Register</h2>

<?php if (isset($error)) : ?>
    <p style="color:red;">
        <?php echo $error; ?>
    </p>
<?php endif; ?>

<form method="POST">

    <input type="text" name="firstName" placeholder="First Name" required>
    <br><br>

    <input type="text" name="middleInitial" placeholder="Middle Initial">
    <br><br>

    <input type="text" name="lastName" placeholder="Last Name" required>
    <br><br>

    <input type="text" name="suffix" placeholder="Suffix">
    <br><br>

    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <input type="password" name="password" placeholder="Password" required>
    <br><br>

    <button type="submit" name="register">
        Register
    </button>

</form>

<br>

<a href="login.php">Back to Login</a>

</body>
</html>