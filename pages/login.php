<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if (authenticateUser($username, $password)) {
            redirectBasedOnRole();
        } else {
            $error = "Invalid username or password";
        }
    }
    
    // Handle password reset request
    if (isset($_POST['reset_password'])) {
        $username = trim($_POST['username']);
        $old_password = trim($_POST['old_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Verify old password first
        if (!authenticateUser($username, $old_password)) {
            $reset_error = "Old password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $reset_error = "New passwords don't match";
        } elseif (strlen($new_password) < 8) {
            $reset_error = "Password must be at least 8 characters";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);
            
            if ($stmt->execute()) {
                $reset_success = "Password updated successfully! Please login with your new password.";
            } else {
                $reset_error = "Failed to update password. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Cargo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .forgot-password-link {
            font-size: 0.9rem;
            text-align: right;
            display: block;
            margin-top: 5px;  <!-- Increased from -10px -->
            margin-bottom: 20px; <!-- Increased from 15px -->
        }
        .login-btn {
            margin-top: 15px;  <!-- Added space above login button -->
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <!-- Moved link down here with more spacing -->
                            <div class="d-flex justify-content-end">
                                <a href="#" class="forgot-password-link" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    Forgot password or want to change password?
                                </a>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary w-100 login-btn">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Password Reset Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if (isset($reset_error)): ?>
                            <div class="alert alert-danger mb-3"><?= htmlspecialchars($reset_error) ?></div>
                        <?php endif; ?>
                        <?php if (isset($reset_success)): ?>
                            <div class="alert alert-success mb-3"><?= htmlspecialchars($reset_success) ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reset_password" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>