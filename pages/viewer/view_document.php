<?php
require_once '../../includes/auth.php';

if ($_SESSION['role'] !== 'Viewer') {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$doc_id = $_GET['id'];

// Fetch document with related data
$stmt = $conn->prepare("
    SELECT d.*, ct.type as cargo_type, c.*, del.*
    FROM documents d
    JOIN cargo_types ct ON d.cargo_type_id = ct.id
    LEFT JOIN clearance c ON c.document_id = d.id
    LEFT JOIN delivery del ON del.document_id = d.id
    WHERE d.id = ?
");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
    die("Document not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Document #<?= $document['document_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/viewer_nav.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-file-alt"></i> Document #<?= htmlspecialchars($document['document_number']) ?>
                <span class="badge bg-<?= $document['cargo_type'] == 'Aircargo' ? 'primary' : 
                                      ($document['cargo_type'] == 'Seacargo' ? 'info' : 'warning') ?>">
                    <?= $document['cargo_type'] ?>
                </span>
            </h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <!-- Document Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-file"></i> Document Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>File No:</strong> <?= htmlspecialchars($document['file_no']) ?></p>
                        <p><strong>Received From:</strong> <?= htmlspecialchars($document['received_from']) ?></p>
                        <p><strong>Invoice No:</strong> <?= htmlspecialchars($document['invoice_no']) ?></p>
                        <p><strong>Created:</strong> <?= date('M d, Y', strtotime($document['created_at'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Clearance Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-check-circle"></i> Clearance Details</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($document['nmra_approval']): ?>
                            <p><strong>Status:</strong> Cleared on <?= date('M d, Y', strtotime($document['shipment_cleared_date'])) ?></p>
                            <p><strong>Custom Duty:</strong> $<?= number_format($document['custom_duty_charges_value'], 2) ?></p>
                        <?php else: ?>
                            <p class="text-danger"><strong>Status:</strong> Pending Clearance</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-truck"></i> Delivery Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong> <?= htmlspecialchars($document['description_of_cargo_goods']) ?></p>
                        <p><strong>Quantity:</strong> <?= $document['quantity'] ?></p>
                        <p><strong>Total Value:</strong> $<?= number_format($document['total_value'], 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Details Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#documentTab">Document</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#clearanceTab">Clearance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#deliveryTab">Delivery</a>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content">
                <!-- Document Tab -->
                <div class="tab-pane fade show active" id="documentTab">
                    <?php include './viewer/document_details.php'; ?>
                </div>
                
                <!-- Clearance Tab -->
                <div class="tab-pane fade" id="clearanceTab">
                    <?php include './viewer/clearance_details.php'; ?>
                </div>
                
                <!-- Delivery Tab -->
                <div class="tab-pane fade" id="deliveryTab">
                    <?php include './viewer/delivery_details.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>