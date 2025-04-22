<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Strict viewer access control
if ($_SESSION['role'] !== 'Viewer') {
    header('Location: ../../login.php');
    exit();
}

// Get filter parameters
$cargo_type = $_GET['cargo_type'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$doc_number = $_GET['doc_number'] ?? '';

// Base query
$query = "SELECT 
            d.id,
            d.document_number,
            d.file_no,
            d.created_at,
            ct.type AS cargo_type,
            c.shipment_cleared_date,
            del.description_of_cargo_goods
          FROM documents d
          JOIN cargo_types ct ON d.cargo_type_id = ct.id
          LEFT JOIN clearance c ON c.document_id = d.id
          LEFT JOIN delivery del ON del.document_id = d.id
          WHERE 1=1";

// Add filters
$params = [];
$types = '';

if (!empty($cargo_type)) {
    $query .= " AND ct.type = ?";
    $params[] = $cargo_type;
    $types .= 's';
}

if (!empty($start_date)) {
    $query .= " AND DATE(d.created_at) >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $query .= " AND DATE(d.created_at) <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if (!empty($doc_number)) {
    $query .= " AND d.document_number LIKE ?";
    $params[] = "%$doc_number%";
    $types .= 's';
}

$query .= " ORDER BY d.created_at DESC LIMIT 100";

// Prepare and execute
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Records - CargoMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-air { background-color: #0d6efd; }
        .badge-sea { background-color: #0dcaf0; }
        .badge-ubp { background-color: #ffc107; color: #000; }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
   
   
    <?php include '../../includes/viewer_nav.php'; ?> 
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- Filter Sidebar -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-filter me-2"></i>Filter Records</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="mb-3">
                                <label class="form-label">Cargo Type</label>
                                <select name="cargo_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="Aircargo" <?= $cargo_type === 'Aircargo' ? 'selected' : '' ?>>Aircargo</option>
                                    <option value="Seacargo" <?= $cargo_type === 'Seacargo' ? 'selected' : '' ?>>Seacargo</option>
                                    <option value="Ubpcargo" <?= $cargo_type === 'Ubpcargo' ? 'selected' : '' ?>>Ubpcargo</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Document Number</label>
                                <input type="text" name="doc_number" class="form-control" value="<?= htmlspecialchars($doc_number) ?>">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                                </div>
                                <div class="col">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Apply Filters
                            </button>
                            <a href="view_records.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <!-- Main Records Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Cargo Records</h5>
                        <span class="badge bg-secondary"><?= $result->num_rows ?> records found</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Doc. Number</th>
                                        <th>File No</th>
                                        <th>Cargo Type</th>
                                        <th>Description</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr class="clickable-row" onclick="window.location='view_document.php?id=<?= $row['id'] ?>'">
                                            <td><?= htmlspecialchars($row['document_number']) ?></td>
                                            <td><?= htmlspecialchars($row['file_no']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= strtolower(substr($row['cargo_type'], 0, 3)) ?>">
                                                    <?= $row['cargo_type'] ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(substr($row['description_of_cargo_goods'] ?? 'N/A', 0, 30)) ?>...</td>
                                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <?php if ($row['shipment_cleared_date']): ?>
                                                    <span class="badge bg-success">Cleared</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($result->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-database fa-2x mb-3"></i><br>
                                                No records found matching your criteria
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted">Showing <?= $result->num_rows ?> records (max 100)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Make rows clickable
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', () => {
                window.location = row.getAttribute('onclick').match(/'([^']+)'/)[1];
            });
        });
    </script>
</body>
</html>