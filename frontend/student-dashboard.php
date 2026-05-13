<?php

session_start();

if(!isset($_SESSION['userID'])){

    header("Location: login.php");
    exit();

}

if($_SESSION['role'] != 'student'){

    header("Location: login.php");
    exit();

}

?>

<h1>Student Dashboard</h1>

<h2>
    Welcome
    <?php echo $_SESSION['name']; ?>
</h2>

<a href="logout.php">
    Logout
</a>