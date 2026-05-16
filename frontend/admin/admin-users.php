<?php
session_start();
require_once '../../backend/database.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================
   DELETE USER (SOFT DELETE)
========================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("
        UPDATE users 
        SET date_deleted = NOW()
        WHERE userID = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin-users.php");
    exit();
}

/* =========================
   SEARCH USERS
========================= */
$search = $_GET['search'] ?? '';

$sql = "
    SELECT 
        userID,
        firstName,
        middleInitial,
        lastName,
        suffix,
        email,
        role,
        date_created
    FROM users
    WHERE date_deleted IS NULL
";

if (!empty($search)) {
    $sql .= " AND (firstName LIKE ? OR lastName LIKE ? OR email LIKE ?)";
}

$sql .= " ORDER BY date_created DESC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Users</title>

    <style>
        body { font-family: Arial; margin: 20px; }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        input {
            padding: 8px;
            width: 250px;
        }

        button {
            padding: 8px 12px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .admin { color: red; font-weight: bold; }
        .student { color: green; font-weight: bold; }
    </style>
</head>

<body>

<h2>Users Management</h2>

<!-- SEARCH BAR -->
<div class="top-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search user..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>
</div>

<!-- TABLE -->
<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Middle Initial</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Email</th>
        <th>Role</th>
        <th>Date Created</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['userID'] ?></td>

        <td><?= htmlspecialchars($row['firstName']) ?></td>

        <td>
            <?= $row['middleInitial'] ? htmlspecialchars($row['middleInitial']) : '-' ?>
        </td>

        <td><?= htmlspecialchars($row['lastName']) ?></td>

        <td>
            <?= $row['suffix'] ? htmlspecialchars($row['suffix']) : '-' ?>
        </td>

        <td><?= htmlspecialchars($row['email']) ?></td>

        <td>
            <?php if ($row['role'] == 'admin') { ?>
                <span class="admin">ADMIN</span>
            <?php } else { ?>
                <span class="student">STUDENT</span>
            <?php } ?>
        </td>

        <td><?= $row['date_created'] ?></td>

        <td>
            <a href="edit-user.php?id=<?= $row['userID'] ?>">Edit</a> |
            <a href="admin-users.php?delete=<?= $row['userID'] ?>"
               onclick="return confirm('Delete this user?')">
               Delete
            </a>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>