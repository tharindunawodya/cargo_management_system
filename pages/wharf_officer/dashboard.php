<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Only allow Wharf Officers
if ($_SESSION['role'] !== 'Wharf Officer') {
    header('Location: ../../login.php');
    exit();
}

// Fetch all cargo records
$records = $conn->query("
    SELECT d.id, d.document_number, d.file_no, ct.type as cargo_type, 
           d.created_at, c.shipment_cleared_date
    FROM documents d
    JOIN cargo_types ct ON d.cargo_type_id = ct.id
    LEFT JOIN clearance c ON c.document_id = d.id
    ORDER BY d.created_at DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wharf Officer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .record-card {
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .record-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white vh-100">
                <div class="sidebar-sticky pt-3">
                    <h4 class="text-center mb-4">CargoMS</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="create_donation.php">
                                <i class="fas fa-plus-circle me-2"></i> Open a Donation
                            </a>
                        </li>
                        <li class="nav-item mt-3 border-top pt-2">
                            <a class="nav-link text-white" href="../../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Wharf Officer Dashboard</h2>
                    <a href="create_donation.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Open a Donation
                    </a>
                </div>

                <!-- Records Section -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Recent Cargo Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Doc. Number</th>
                                        <th>File No</th>
                                        <th>Type</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = $records->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record['document_number']) ?></td>
                                            <td><?= htmlspecialchars($record['file_no']) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= $record['cargo_type'] == 'Aircargo' ? 'bg-info' : 
                                                       ($record['cargo_type'] == 'Seacargo' ? 'bg-primary' : 'bg-warning') ?>">
                                                    <?= $record['cargo_type'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($record['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $record['shipment_cleared_date'] ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= $record['shipment_cleared_date'] ? 'Cleared' : 'Pending' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_record.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>