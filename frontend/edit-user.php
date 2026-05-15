<?php
session_start();
require_once '../backend/database.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT userID, firstName, lastName, email, role
    FROM users
    WHERE userID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (isset($_POST['update'])) {

    $role = $_POST['role'];

    $stmt = $conn->prepare("
        UPDATE users
        SET role = ?
        WHERE userID = ?
    ");

    $stmt->bind_param("si", $role, $id);
    $stmt->execute();

    header("Location: admin-users.php");
    exit();
}
?>

<h2>Edit User</h2>

<form method="POST">

    <p>Name: <?= $user['firstName'] . ' ' . $user['lastName'] ?></p>
    <p>Email: <?= $user['email'] ?></p>

    <label>Role</label>
    <select name="role">
        <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <br><br>

    <button type="submit" name="update">Update</button>
</form>