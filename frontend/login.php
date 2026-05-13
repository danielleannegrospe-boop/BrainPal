<?php

session_start();

require_once '../backend/database.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        // VERIFY HASHED PASSWORD
        if(password_verify($password, $user['password'])){

            $_SESSION['userID'] = $user['userID'];

            $_SESSION['name'] =
                $user['firstName'] . ' ' .
                $user['middleInitial'] . ' ' .
                $user['lastName'] . ' ' .
                $user['suffix'];

            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'admin'){

                header("Location: admin-dashboard.php");

            }else{

                header("Location: student-dashboard.php");

            }

            exit();

        }else{

            echo "Wrong Password";

        }

    }else{

        echo "User not found";

    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<form method="POST">

    <input
        type="email"
        name="email"
        placeholder="Email"
        required
    >

    <br><br>

    <input
        type="password"
        name="password"
        placeholder="Password"
        required
    >

    <br><br>

   <button type="submit" name="login">
    Login
</button>

<br><br>

<a href="register.php">
    <button type="button">
        Register
    </button>
</a>

</form>

</body>
</html>