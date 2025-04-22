<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $username = $_POST['username'];
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate inputs
    if ($newPassword !== $confirmPassword) {
        $error = "Passwords don't match";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!validateResetToken($username, $token)) {
        $error = "Invalid or expired reset token";
    } else {
        if (updatePassword($username, $newPassword)) {
            // Delete the used token
            $conn->query("DELETE FROM password_resets WHERE username = '$username'");
            $success = "Password updated successfully. <a href='../login.php'>Login with your new password</a>";
        } else {
            $error = "Failed to update password";
        }
    }
} elseif (isset($_GET['username']) && isset($_GET['token'])) {
    $username = $_GET['username'];
    $token = $_GET['token'];
    
    if (!validateResetToken($username, $token)) {
        $error = "Invalid or expired reset token";
    }
} else {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Cargo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Reset Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required minlength="8">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required minlength="8">
                                </div>
                                <button type="submit" name="reset_password" class="btn btn-primary w-100">Reset Password</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>