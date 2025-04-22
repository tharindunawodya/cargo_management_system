<?php
require_once '../../includes/auth.php';

// Strict viewer access control
if (!isViewer()) {
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Viewer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/viewer_nav.php'; ?>
    
    <div class="container mt-4">
        <div class="alert alert-success">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! 
            You are logged in as a <strong>Viewer</strong>.
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Viewer Functions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="view_records.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-eye"></i> View Cargo Records
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="reports.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-chart-bar"></i> Generate Reports
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="../../logout.php" class="btn btn-outline-danger w-100">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>