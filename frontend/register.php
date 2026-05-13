<?php

require_once '../backend/database.php';

if(isset($_POST['register'])){

    $firstName = $_POST['firstName'];
    $middleInitial = $_POST['middleInitial'];
    $lastName = $_POST['lastName'];
    $suffix = $_POST['suffix'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // HASH PASSWORD
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
    INSERT INTO users(
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

    if($stmt->execute()){

        echo "Registered Successfully";

    }else{

        echo "Registration Failed";

    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<form method="POST">

    <input
        type="text"
        name="firstName"
        placeholder="First Name"
        required
>

    <br><br>

    <input
        type="text"
        name="middleInitial"
        placeholder="Middle Initial"
>

    <br><br>

    <input
        type="text"
        name="lastName"
        placeholder="Last Name"
        required
>

    <br><br>

    <input
        type="text"
        name="suffix"
        placeholder="Suffix"
>

    <br><br>

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

    <button type="submit" name="register">
        Register
    </button>

</form>

</body>
</html>