<?php
// Verify admin status
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
if ($_SESSION['role'] !== 'Admin') {
    die('Access denied. Admin privileges required.');
}
?>
<!-- Admin Sidebar Navigation -->
<div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh;">
    <div class="sidebar-header p-3">
        <h4>CargoMS Admin</h4>
        <hr>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white active" href="manage_users.php">
                <i class="fas fa-users-cog me-2"></i>User Management
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link text-white dropdown-toggle" data-bs-toggle="collapse" href="#cargoMenu">
                <i class="fas fa-boxes me-2"></i>Cargo Operations
            </a>
            <div class="collapse" id="cargoMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="cargo_types.php">Cargo Types</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="all_documents.php">All Documents</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="reports.php">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="audit_logs.php">
                <i class="fas fa-clipboard-list me-2"></i>Audit Logs
            </a>
        </li>
        <li class="nav-item mt-3 border-top pt-2">
            <a class="nav-link text-white" href="../../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Bootstrap JS for dropdowns -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>