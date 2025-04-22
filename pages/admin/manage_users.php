<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Only allow admins
if ($_SESSION['role'] !== 'Admin') {
    header('Location: ../../login.php');
    exit();
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password, $role, $created_by);
    
    if ($stmt->execute()) {
        $success = "User created successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch all users
$users = $conn->query("SELECT u.*, creator.username as creator_name FROM users u LEFT JOIN users creator ON u.created_by = creator.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <!-- Header with Logout Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <div>
                <span class="me-3">Logged in as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <a href="../../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Create User Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Create New User</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin">Admin</option>
                            <option value="Wharf Officer">Wharf Officer</option>
                            <option value="Viewer">Viewer</option>
                        </select>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                </form>
            </div>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Existing Users</h5>
                <a href="../../logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= isset($user['creator_name']) ? htmlspecialchars($user['creator_name']) : 'System' ?></td>
                                <td><?= htmlspecialchars($user['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>