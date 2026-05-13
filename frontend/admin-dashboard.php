<?php
session_start();

if(!isset($_SESSION['userID'])){
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}
?>

<h1>Admin Dashboard</h1>

<h2>Welcome <?php echo $_SESSION['fullName']; ?></h2>