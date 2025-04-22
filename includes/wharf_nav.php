<?php
// C:\xampp\htdocs\cargo_management_system\includes\wharf_nav.php
if (!isset($_SESSION['user_id'])) {  // Added missing parenthesis
    header('Location: ../login.php');
    exit();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">CargoMS - Wharf Officer</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#wharfNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="wharfNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="create_donation.php"><i class="fas fa-plus-circle"></i> Create Donation</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-light">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>