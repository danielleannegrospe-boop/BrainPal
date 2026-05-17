<?php
session_start();

require_once '../../backend/database.php';
$env = require_once '../../backend/pusher.php';
require_once '../../backend/csrf.php';
$csrf = generateCSRF();

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   DELETE USER (SOFT DELETE)
========================= */
if (isset($_POST['deleteUser'])) {

    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $id = (int) $_POST['delete_id'];

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6fb;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 18px 25px;
        }

        .container {
            padding: 25px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        input {
            padding: 10px;
            width: 280px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        button {
            padding: 10px 14px;
            border: none;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            cursor: pointer;
        }

        .table-box {
            background: white;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #4f46e5;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9f9ff;
        }

        .admin {
            background: #dc2626;
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
        }

        .student {
            background: #16a34a;
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
        }

        a {
            text-decoration: none;
            margin-right: 8px;
        }

        .edit { color: #2563eb; font-weight: bold; }
        .delete { color: #dc2626; font-weight: bold; }
    </style>
</head>

<body>

<div class="header" style="display:flex;justify-content:space-between;align-items:center;">
    <h2>User Management</h2>

    <a href="../admin/admin-dashboard.php"
       style="
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
       ">
        ⬅ Back to Dashboard
    </a>
</div>

<div class="container">

    <div class="top-bar">
        <form method="GET">
            <input type="text" name="search"
                   placeholder="Search user..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-box">
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>MI</th>
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
                <td><?= $row['middleInitial'] ?: '-' ?></td>
                <td><?= htmlspecialchars($row['lastName']) ?></td>
                <td><?= $row['suffix'] ?: '-' ?></td>
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
                    <a class="edit" href="edit-user.php?id=<?= $row['userID'] ?>">Edit</a>
                    <form method="POST" style="display:inline;">

                    <input type="hidden" name="delete_id" value="<?= $row['userID'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                    <button type="submit"
                        name="deleteUser"
                        class="delete"
                        onclick="return confirm('Delete this user?')"
                        style="background:none;border:none;cursor:pointer;">

                         Delete
                    </button>

                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

<!-- =========================
     PUSHER REAL-TIME
========================= -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
const PUSHER_APP_KEY = "<?= htmlspecialchars($env['PUSHER_APP_KEY']) ?>";
const PUSHER_CLUSTER = "<?= htmlspecialchars($env['PUSHER_APP_CLUSTER']) ?>";

Pusher.logToConsole = false;

var pusher = new Pusher(PUSHER_APP_KEY, {
    cluster: PUSHER_CLUSTER
});

var channel = pusher.subscribe('user-channel');

channel.bind('user-registered', function(data) {

    alert(
        '👤 New User Registered!\n\n' +
        'Name: ' + data.name +
        '\nEmail: ' + data.email
    );

    location.reload();
});
</script>

</body>
</html>