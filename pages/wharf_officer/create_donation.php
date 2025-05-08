<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SESSION['role'] !== 'Wharf Officer') {
    header('Location: ../../login.php');
    exit(); 
}

// Initialize variables
$errors = [];
$success = '';
$documentData = $clearanceData = $deliveryData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Document Data
    $documentData = [
        'document_number' => sanitizeInput($_POST['document_number']),
        'original_received' => isset($_POST['original_received']) ? 'yes' : 'no',
        'received_from' => sanitizeInput($_POST['received_from']),
        'file_no' => sanitizeInput($_POST['file_no']),
        'invoice_no' => sanitizeInput($_POST['invoice_no']),
        'invoice_date' => $_POST['invoice_date'],
        'packing_list_no' => sanitizeInput($_POST['packing_list_no']),
        'letter_of_donation' => isset($_POST['letter_of_donation']) ? 'yes' : 'no',
        'analysis_certificate' => isset($_POST['analysis_certificate']) ? 'yes' : 'no',
        'purchase_order_no' => sanitizeInput($_POST['purchase_order_no']),
        'master_air_way_bill_no' => sanitizeInput($_POST['master_air_way_bill_no']),
        'house_air_way_bill_no' => sanitizeInput($_POST['house_air_way_bill_no']),
        'cargo_type_id' => (int)$_POST['cargo_type_id']
    ];

    // Validate required fields
    if (empty($documentData['document_number'])) {
        $errors['document_number'] = 'Document number is required';
    }

    if (empty($errors)) {
        try {
            $conn->begin_transaction();

            // Insert Document
            // In the form submission handler, modify the document insertion part:
				$stmt = $conn->prepare("INSERT INTO documents 
				(document_number, cargo_type_id, original_received, received_from, 
				file_no, invoice_no, invoice_date, packing_list_no, letter_of_donation, 
				analysis_certificate, purchase_order_no, master_air_way_bill_no, 
				house_air_way_bill_no, created_by)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

				$stmt->bind_param("sisssssssssssi", 
				$documentData['document_number'],
				$documentData['cargo_type_id'],
				$documentData['original_received'],
				$documentData['received_from'],
				$documentData['file_no'],
				$documentData['invoice_no'],
				$documentData['invoice_date'],
				$documentData['packing_list_no'],
				$documentData['letter_of_donation'],
				$documentData['analysis_certificate'],
				$documentData['purchase_order_no'],
				$documentData['master_air_way_bill_no'],
				$documentData['house_air_way_bill_no'],
				$_SESSION['user_id']  // Make sure this is set in your auth system
			);

            $stmt->execute();
            $documentId = $conn->insert_id;

            // Process and Insert Clearance Data
            $clearanceData = [
                'document_id' => $documentId,
                'nmra_approval' => $_POST['nmra_approval'] ?? 'no',
                'nmra_applied_date' => !empty($_POST['nmra_applied_date']) ? $_POST['nmra_applied_date'] : null,
                'nmra_received_date' => !empty($_POST['nmra_received_date']) ? $_POST['nmra_received_date'] : null,
                'wor_number' => sanitizeInput($_POST['wor_number'] ?? ''),
                'icl_apln_no' => sanitizeInput($_POST['icl_apln_no'] ?? ''),
                'icl_applied_date' => !empty($_POST['icl_applied_date']) ? $_POST['icl_applied_date'] : null,
                'icl_received_date' => !empty($_POST['icl_received_date']) ? $_POST['icl_received_date'] : null,
                'icl_no' => sanitizeInput($_POST['icl_no'] ?? ''),
                'icl_debit_applied_date' => !empty($_POST['icl_debit_applied_date']) ? $_POST['icl_debit_applied_date'] : null,
                'icl_debit_received_date' => !empty($_POST['icl_debit_received_date']) ? $_POST['icl_debit_received_date'] : null,
                'no_foreign_exchange' => $_POST['no_foreign_exchange'] ?? 'no',
                'foreign_exchange_applied_date' => !empty($_POST['foreign_exchange_applied_date']) ? $_POST['foreign_exchange_applied_date'] : null,
                'foreign_exchange_received_date' => !empty($_POST['foreign_exchange_received_date']) ? $_POST['foreign_exchange_received_date'] : null,
                'excise_certificate' => $_POST['excise_certificate'] ?? 'no',
                'excise_applied_date' => !empty($_POST['excise_applied_date']) ? $_POST['excise_applied_date'] : null,
                'excise_received_date' => !empty($_POST['excise_received_date']) ? $_POST['excise_received_date'] : null,
                'trc_approval' => $_POST['trc_approval'] ?? 'no',
                'trc_applied_date' => !empty($_POST['trc_applied_date']) ? $_POST['trc_applied_date'] : null,
                'trc_received_date' => !empty($_POST['trc_received_date']) ? $_POST['trc_received_date'] : null,
                'atomic_energy_certificate' => $_POST['atomic_energy_certificate'] ?? 'no',
                'atomic_energy_applied_date' => !empty($_POST['atomic_energy_applied_date']) ? $_POST['atomic_energy_applied_date'] : null,
                'atomic_energy_received_date' => !empty($_POST['atomic_energy_received_date']) ? $_POST['atomic_energy_received_date'] : null,
                'other_approval' => $_POST['other_approval'] ?? 'no',
                'other_applied_date' => !empty($_POST['other_applied_date']) ? $_POST['other_applied_date'] : null,
                'other_received_date' => !empty($_POST['other_received_date']) ? $_POST['other_received_date'] : null,
                'clearance_document_handed_over_date' => !empty($_POST['clearance_document_handed_over_date']) ? $_POST['clearance_document_handed_over_date'] : null,
                'custom_duty_charges_value' => !empty($_POST['custom_ duty_charges_value']) ? (float)$_POST['custom_duty_charges_value'] : null,
                'custom_duty_voucher_handed_over_date' => !empty($_POST['custom_duty_voucher_handed_over_date']) ? $_POST['custom_duty_voucher_handed_over_date'] : null,
                'custom_duty_cheque_received_date' => !empty($_POST['custom_duty_cheque_received_date']) ? $_POST['custom_duty_cheque_received_date'] : null,
                'shipping_line_charges_value' => !empty($_POST['shipping_line_charges_value']) ? (float)$_POST['shipping_line_charges_value'] : null,
                'shipping_line_approval_request_date' => !empty($_POST['shipping_line_approval_request_date']) ? $_POST['shipping_line_approval_request_date'] : null,
                'shipping_line_approval_received_date' => !empty($_POST['shipping_line_approval_received_date']) ? $_POST['shipping_line_approval_received_date'] : null,
                'shipping_line_voucher_handed_over_date' => !empty($_POST['shipping_line_voucher_handed_over_date']) ? $_POST['shipping_line_voucher_handed_over_date'] : null,
                'shipping_line_cheque_received_date' => !empty($_POST['shipping_line_cheque_received_date']) ? $_POST['shipping_line_cheque_received_date'] : null,
                'port_charges_value' => !empty($_POST['port_charges_value']) ? (float)$_POST['port_charges_value'] : null,
                'port_approval_request_date' => !empty($_POST['port_approval_request_date']) ? $_POST['port_approval_request_date'] : null,
                'port_approval_received_date' => !empty($_POST['port_approval_received_date']) ? $_POST['port_approval_received_date'] : null,
                'port_voucher_handed_over_date' => !empty($_POST['port_voucher_handed_over_date']) ? $_POST['port_voucher_handed_over_date'] : null,
                'port_cheque_received_date' => !empty($_POST['port_cheque_received_date']) ? $_POST['port_cheque_received_date'] : null,
                'shipment_cleared_date' => !empty($_POST['shipment_cleared_date']) ? $_POST['shipment_cleared_date'] : null,
                'shipment_delivery_date' => !empty($_POST['shipment_delivery_date']) ? $_POST['shipment_delivery_date'] : null,
                'mode_of_transport' => sanitizeInput($_POST['mode_of_transport'] ?? ''),
                'vehicle_no' => sanitizeInput($_POST['vehicle_no'] ?? ''),
                'final_payment_value' => !empty($_POST['final_payment_value']) ? (float)$_POST['final_payment_value'] : null,
                'final_payment_approval_request_date' => !empty($_POST['final_payment_approval_request_date']) ? $_POST['final_payment_approval_request_date'] : null,
                'final_payment_approval_received_date' => !empty($_POST['final_payment_approval_received_date']) ? $_POST['final_payment_approval_received_date'] : null,
                'final_payment_voucher_handed_over_date' => !empty($_POST['final_payment_voucher_handed_over_date']) ? $_POST['final_payment_voucher_handed_over_date'] : null,
                'final_payment_cheque_received_date' => !empty($_POST['final_payment_cheque_received_date']) ? $_POST['final_payment_cheque_received_date'] : null
            ];
            
            $stmt = $conn->prepare("INSERT INTO clearance 
                (document_id, nmra_approval, nmra_applied_date, nmra_received_date, 
                 wor_number, icl_apln_no, icl_applied_date, icl_received_date, 
                 icl_no, icl_debit_applied_date, icl_debit_received_date, 
                 no_foreign_exchange, foreign_exchange_applied_date, foreign_exchange_received_date,
                 excise_certificate, excise_applied_date, excise_received_date,
                 trc_approval, trc_applied_date, trc_received_date,
                 atomic_energy_certificate, atomic_energy_applied_date, atomic_energy_received_date,
                 other_approval, other_applied_date, other_received_date,
                 clearance_document_handed_over_date,
                 custom_duty_charges_value, custom_duty_voucher_handed_over_date, custom_duty_cheque_received_date,
                 shipping_line_charges_value, shipping_line_approval_request_date, shipping_line_approval_received_date,
                 shipping_line_voucher_handed_over_date, shipping_line_cheque_received_date,
                 port_charges_value, port_approval_request_date, port_approval_received_date,
                 port_voucher_handed_over_date, port_cheque_received_date,
                 shipment_cleared_date, shipment_delivery_date,
                 mode_of_transport, vehicle_no,
                 final_payment_value, final_payment_approval_request_date, final_payment_approval_received_date,
                 final_payment_voucher_handed_over_date, final_payment_cheque_received_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("isssssssssssssssssssssssssssssssssssssssssssss", 
                $clearanceData['document_id'],
                $clearanceData['nmra_approval'],
                $clearanceData['nmra_applied_date'],
                $clearanceData['nmra_received_date'],
                $clearanceData['wor_number'],
                $clearanceData['icl_apln_no'],
                $clearanceData['icl_applied_date'],
                $clearanceData['icl_received_date'],
                $clearanceData['icl_no'],
                $clearanceData['icl_debit_applied_date'],
                $clearanceData['icl_debit_received_date'],
                $clearanceData['no_foreign_exchange'],
                $clearanceData['foreign_exchange_applied_date'],
                $clearanceData['foreign_exchange_received_date'],
                $clearanceData['excise_certificate'],
                $clearanceData['excise_applied_date'],
                $clearanceData['excise_received_date'],
                $clearanceData['trc_approval'],
                $clearanceData['trc_applied_date'],
                $clearanceData['trc_received_date'],
                $clearanceData['atomic_energy_certificate'],
                $clearanceData['atomic_energy_applied_date'],
                $clearanceData['atomic_energy_received_date'],
                $clearanceData['other_approval'],
                $clearanceData['other_applied_date'],
                $clearanceData['other_received_date'],
                $clearanceData['clearance_document_handed_over_date'],
                $clearanceData['custom_duty_charges_value'],
                $clearanceData['custom_duty_voucher_handed_over_date'],
                $clearanceData['custom_duty_cheque_received_date'],
                $clearanceData['shipping_line_charges_value'],
                $clearanceData['shipping_line_approval_request_date'],
                $clearanceData['shipping_line_approval_received_date'],
                $clearanceData['shipping_line_voucher_handed_over_date'],
                $clearanceData['shipping_line_cheque_received_date'],
                $clearanceData['port_charges_value'],
                $clearanceData['port_approval_request_date'],
                $clearanceData['port_approval_received_date'],
                $clearanceData['port_voucher_handed_over_date'],
                $clearanceData['port_cheque_received_date'],
                $clearanceData['shipment_cleared_date'],
                $clearanceData['shipment_delivery_date'],
                $clearanceData['mode_of_transport'],
                $clearanceData['vehicle_no'],
                $clearanceData['final_payment_value'],
                $clearanceData['final_payment_approval_request_date'],
                $clearanceData['final_payment_approval_received_date'],
                $clearanceData['final_payment_voucher_handed_over_date'],
                $clearanceData['final_payment_cheque_received_date']
            );

            $stmt->execute();

            // Process and Insert Delivery Data
            $deliveryData = [
                'document_id' => $documentId,
                'description_of_cargo_goods' => sanitizeInput($_POST['description_of_cargo_goods'] ?? ''),
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'quantity' => (int)($_POST['quantity'] ?? 0),
                'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
                'temperature' => $_POST['temperature'] ?? 'room temperature',
                'item_value' => !empty($_POST['item_value']) ? (float)$_POST['item_value'] : null,
                'shipment_value' => !empty($_POST['shipment_value']) ? (float)$_POST['shipment_value'] : null,
                'total_value' => !empty($_POST['total_value']) ? (float)$_POST['total_value'] : null,
                'manufacture_date' => !empty($_POST['manufacture_date']) ? $_POST['manufacture_date'] : null,
                'expire_date' => !empty($_POST['expire_date']) ? $_POST['expire_date'] : null
            ];
            
            $stmt = $conn->prepare("INSERT INTO delivery 
                (document_id, description_of_cargo_goods, name, quantity, 
                 weight, temperature, item_value, shipment_value, 
                 total_value, manufacture_date, expire_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("issdssddsss",
                $deliveryData['document_id'],
                $deliveryData['description_of_cargo_goods'],
                $deliveryData['name'],
                $deliveryData['quantity'],
                $deliveryData['weight'],
                $deliveryData['temperature'],
                $deliveryData['item_value'],
                $deliveryData['shipment_value'],
                $deliveryData['total_value'],
                $deliveryData['manufacture_date'],
                $deliveryData['expire_date']
            );
            $stmt->execute();

            $conn->commit();
            $success = 'Donation created successfully!';
            header("Refresh: 3; url=view_donation.php?id=$documentId");
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Error creating donation: ' . $e->getMessage();
        }
    }
}

$cargoTypes = $conn->query("SELECT * FROM cargo_types");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Donation - Cargo Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .form-section {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .section-title {
            color: var(--secondary-color);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .form-control, .form-select {
            border-radius: 4px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-outline-secondary {
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .alert {
            border-radius: 6px;
        }
        
        /* Conditional field styling */
        .conditional-field {
            display: none;
            padding-left: 20px;
            border-left: 3px solid #eee;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .sub-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }
        
        .sub-section-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        /* Custom checkbox and radio styles */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-section {
                padding: 15px;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
        }
        
        /* Animation for form sections */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-section {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Custom file upload button */
        .custom-file-upload {
            border: 1px dashed #ccc;
            display: inline-block;
            padding: 40px 20px;
            cursor: pointer;
            text-align: center;
            width: 100%;
            border-radius: 4px;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }
        
        .custom-file-upload:hover {
            background-color: #f0f0f0;
            border-color: var(--primary-color);
        }
        
        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-pending {
            background-color: #f39c12;
        }
        
        .status-approved {
            background-color: var(--success-color);
        }
        
        .status-rejected {
            background-color: var(--danger-color);
        }
    </style>
</head>
<body>
    <?php include '../../includes/wharf_nav.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3 col-lg-2">
                <?php include '../../includes/wharf_sidebar.php'; ?>
            </div>
            
            <div class="col-md-9 col-lg-10">
                <!-- Success/Error messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> Please fix the    following issues:
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Create New Donation
                    </h2>
                </div>
                
                <form method="POST" id="donationForm" onsubmit="return validateForm()">
                    <!-- Tab navigation -->
                    <ul class="nav nav-tabs mb-4" id="donationTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="document-tab" data-bs-toggle="tab" data-bs-target="#document" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>Document
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="clearance-tab" data-bs-toggle="tab" data-bs-target="#clearance" type="button" role="tab">
                                <i class="fas fa-check-circle me-2"></i>Clearance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab">
                                <i class="fas fa-truck me-2"></i>Delivery
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="donationTabContent">
					
					
					
					
                        <!-- Document Tab -->
                        <div class="tab-pane fade show active" id="document" role="tabpanel">
                            <div class="form-section">
                                <h4 class="section-title"><i class="fas fa-file-alt me-2"></i>Document Information</h4>
                                
                                <div class="row">
                                    <!-- Document Number -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Document Number</label>
                                        <input type="text" class="form-control" name="document_number" required>
                                    </div>
                                    
                                    <!-- Cargo Type -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Cargo Type</label>
                                        <select class="form-select" name="cargo_type_id" required>
										<option value="" disabled selected>Select Cargo Type</option>
										<?php while ($type = $cargoTypes->fetch_assoc()): ?>
										<option value="<?= $type['id'] ?>"><?= $type['type'] ?></option>
										<?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Original Received -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="original_received" id="original_received" role="switch">
                                            <label class="form-check-label" for="original_received">Original Received</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Received From -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Received From</label>
                                        <input type="text" class="form-control" name="received_from">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- File No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">File No</label>
                                        <input type="text" class="form-control" name="file_no">
                                    </div>
                                    
                                    <!-- Invoice No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Invoice No</label>
                                        <input type="text" class="form-control" name="invoice_no">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Invoice Date -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Invoice Date</label>
                                        <input type="date" class="form-control" name="invoice_date">
                                    </div>
                                    
                                    <!-- Packing List No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Packing List No</label>
                                        <input type="text" class="form-control" name="packing_list_no">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Letter of Donation -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="letter_of_donation" id="letter_of_donation" role="switch">
                                            <label class="form-check-label" for="letter_of_donation">Letter of Donation</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Analysis Certificate -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="analysis_certificate" id="analysis_certificate" role="switch">
                                            <label class="form-check-label" for="analysis_certificate">Analysis Certificate</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Purchase Order No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Purchase Order No</label>
                                        <input type="text" class="form-control" name="purchase_order_no">
                                    </div>
                                    
                                    <!-- Master Air Way Bill No -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Master Air Way Bill No</label>
                                        <input type="text" class="form-control" name="master_air_way_bill_no">
                                    </div>
                                </div>
                                
                                <!-- House Air Way Bill No -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">House Air Way Bill No</label>
                                        <input type="text" class="form-control" name="house_air_way_bill_no">
                                    </div>
                                </div>
                                
                                <!-- Document Attachments -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Document Attachments</label>
                                        <label for="file-upload" class="custom-file-upload">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-3"></i>
                                            <p>Click to upload supporting documents</p>
                                            <small class="text-muted">(Max file size: 5MB. Supported formats: PDF, JPG, PNG)</small>
                                        </label>
                                        <input id="file-upload" type="file" multiple style="display:none;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary next-tab" data-next-tab="clearance-tab">
                                    Next: Clearance<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Clearance Tab -->
                        <div class="tab-pane fade" id="clearance" role="tabpanel">
                            <div class="form-section">
                                <h4 class="section-title"><i class="fas fa-check-circle me-2"></i>Clearance Information</h4>
                                
                                <!-- NMRA Approval -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">NMRA Approval</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Status</label>
                                            <select class="form-select nmra-approval-select" name="nmra_approval">
                                                <option value="yes">Approved</option>
                                                <option value="no" selected>Not Approved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field nmra-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="nmra_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="nmra_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- WOR Number -->
                                <div class="sub-section">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">WOR Number</label>
                                            <input type="text" class="form-control" name="wor_number">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ICL Section -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">ICL (Import Control License)</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">APLN No</label>
                                            <input type="text" class="form-control" name="icl_apln_no">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="icl_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="icl_received_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ICL No</label>
                                            <input type="text" class="form-control" name="icl_no">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- ICL Debit Process -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">ICL Debit Process</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="icl_debit_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="icl_debit_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Foreign Exchange -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Foreign Exchange</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">No Foreign Exchange Required</label>
                                            <select class="form-select foreign-exchange-select" name="no_foreign_exchange">
                                                <option value="yes">Yes</option>
                                                <option value="no" selected>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field foreign-exchange-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="foreign_exchange_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="foreign_exchange_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Excise Certificate -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Excise Certificate</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Certificate Status</label>
                                            <select class="form-select excise-certificate-select" name="excise_certificate">
                                                <option value="yes">Received</option>
                                                <option value="no" selected>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field excise-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="excise_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="excise_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- TRC Approval -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">TRC Approval</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Status</label>
                                            <select class="form-select trc-approval-select" name="trc_approval">
                                                <option value="yes">Approved</option>
                                                <option value="no" selected>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field trc-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="trc_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="trc_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Atomic Energy Certificate -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Atomic Energy Certificate</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Certificate Status</label>
                                            <select class="form-select atomic-energy-select" name="atomic_energy_certificate">
                                                <option value="yes">Received</option>
                                                <option value="no" selected>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field atomic-energy-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="atomic_energy_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="atomic_energy_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other Approvals -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Other Required Approvals</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Status</label>
                                            <select class="form-select other-approval-select" name="other_approval">
                                                <option value="yes">Received</option>
                                                <option value="no" selected>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row conditional-field other-approval-dates">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Applied Date</label>
                                            <input type="date" class="form-control" name="other_applied_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Received Date</label>
                                            <input type="date" class="form-control" name="other_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Clearance Document Handover -->
                                <div class="sub-section">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Clearance Document Handed Over to CSSL Date</label>
                                            <input type="date" class="form-control" name="clearance_document_handed_over_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Custom Duty Charges -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Custom Duty Charges</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Value (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" name="custom_duty_charges_value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher Handed Over Date to Account Section</label>
                                            <input type="date" class="form-control" name="custom_duty_voucher_handed_over_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cheque Received Date</label>
                                            <input type="date" class="form-control" name="custom_duty_cheque_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shipping Line Charges -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Shipping Line Charges</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Value (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" name="shipping_line_charges_value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Request Date</label>
                                            <input type="date" class="form-control" name="shipping_line_approval_request_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Received Date</label>
                                            <input type="date" class="form-control" name="shipping_line_approval_received_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher Handed Over Date to Account Section</label>
                                            <input type="date" class="form-control" name="shipping_line_voucher_handed_over_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cheque Received Date</label>
                                            <input type="date" class="form-control" name="shipping_line_cheque_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Port Charges -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Port Charges</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Value (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" name="port_charges_value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Request Date</label>
                                            <input type="date" class="form-control" name="port_approval_request_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Received Date</label>
                                            <input type="date" class="form-control" name="port_approval_received_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher Handed Over Date to Account Section</label>
                                            <input type="date" class="form-control" name="port_voucher_handed_over_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cheque Received Date</label>
                                            <input type="date" class="form-control" name="port_cheque_received_date">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shipment Details -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Shipment Details</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cleared Date</label>
                                            <input type="date" class="form-control" name="shipment_cleared_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Delivery Date</label>
                                            <input type="date" class="form-control" name="shipment_delivery_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mode of Transport</label>
                                            <input type="text" class="form-control" name="mode_of_transport">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vehicle No</label>
                                            <input type="text" class="form-control" name="vehicle_no">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Final Payment -->
                                <div class="sub-section">
                                    <h5 class="sub-section-title">Final Payment</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Value (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" name="final_payment_value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Request Date</label>
                                            <input type="date" class="form-control" name="final_payment_approval_request_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Approval Received Date</label>
                                            <input type="date" class="form-control" name="final_payment_approval_received_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher Handed Over Date to Account Section</label>
                                            <input type="date" class="form-control" name="final_payment_voucher_handed_over_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cheque Received Date</label>
                                            <input type="date" class="form-control" name="final_payment_cheque_received_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary prev-tab" data-prev-tab="document-tab">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary next-tab" data-next-tab="delivery-tab">
                                    Next: Delivery<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Delivery Tab -->
                        <div class="tab-pane fade" id="delivery" role="tabpanel">
                            <div class="form-section">
                                <h4 class="section-title"><i class="fas fa-truck me-2"></i>Delivery Information</h4>
                                
                                <!-- Description -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label class="form-label">Description of Cargo Goods</label>
                                        <textarea class="form-control" name="description_of_cargo_goods" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Item Details -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" class="form-control" name="weight">
                                    </div>
                                </div>
                                
                                <!-- Temperature -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <label class="form-label">Temperature</label>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="temperature" id="cool" value="cool">
                                            <label class="form-check-label" for="cool">Cool</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="temperature" id="room_temp" value="room temperature" checked>
                                            <label class="form-check-label" for="room_temp">Room Temperature</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Value Details -->
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Item Value (USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="item_value">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Shipment Value (USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="shipment_value">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Total Value (USD)</label>
                                        <input type="number" step="0.01" class="form-control" name="total_value">
                                    </div>
                                </div>
                                
                                <!-- Dates -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Manufacture Date</label>
                                        <input type="date" class="form-control" name="manufacture_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expire Date</label>
                                        <input type="date" class="form-control" name="expire_date">
                                    </div>
                                </div>
                                
                                <!-- Delivery Notes -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Special Handling Instructions</label>
                                        <textarea class="form-control" name="special_instructions" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary prev-tab" data-prev-tab="clearance-tab">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="submit" name="create_donation" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Save Donation
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab navigation
        document.querySelectorAll('.next-tab').forEach(button => {
            button.addEventListener('click', function() {
                const nextTabId = this.getAttribute('data-next-tab');
                const nextTab = document.getElementById(nextTabId);
                const tab = new bootstrap.Tab(nextTab);
                tab.show();
                
                // Scroll to top of the form
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
        
        document.querySelectorAll('.prev-tab').forEach(button => {
            button.addEventListener('click', function() {
                const prevTabId = this.getAttribute('data-prev-tab');
                const prevTab = document.getElementById(prevTabId);
                const tab = new bootstrap.Tab(prevTab);
                tab.show();
                
                // Scroll to top of the form
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
        
        // Conditional field display logic
        function setupConditionalFields() {
            // NMRA Approval
            const nmraSelect = document.querySelector('.nmra-approval-select');
            const nmraDates = document.querySelector('.nmra-dates');
            
            nmraSelect.addEventListener('change', function() {
                nmraDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            nmraDates.style.display = nmraSelect.value === 'no' ? 'block' : 'none';
            
            // Foreign Exchange
            const foreignExchangeSelect = document.querySelector('.foreign-exchange-select');
            const foreignExchangeDates = document.querySelector('.foreign-exchange-dates');
            
            foreignExchangeSelect.addEventListener('change', function() {
                foreignExchangeDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            foreignExchangeDates.style.display = foreignExchangeSelect.value === 'no' ? 'block' : 'none';
            
            // Excise Certificate
            const exciseSelect = document.querySelector('.excise-certificate-select');
            const exciseDates = document.querySelector('.excise-dates');
            
            exciseSelect.addEventListener('change', function() {
                exciseDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            exciseDates.style.display = exciseSelect.value === 'no' ? 'block' : 'none';
            
            // TRC Approval
            const trcSelect = document.querySelector('.trc-approval-select');
            const trcDates = document.querySelector('.trc-dates');
            
            trcSelect.addEventListener('change', function() {
                trcDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            trcDates.style.display = trcSelect.value === 'no' ? 'block' : 'none';
            
            // Atomic Energy Certificate
            const atomicEnergySelect = document.querySelector('.atomic-energy-select');
            const atomicEnergyDates = document.querySelector('.atomic-energy-dates');
            
            atomicEnergySelect.addEventListener('change', function() {
                atomicEnergyDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            atomicEnergyDates.style.display = atomicEnergySelect.value === 'no' ? 'block' : 'none';
            
            // Other Approvals
            const otherApprovalSelect = document.querySelector('.other-approval-select');
            const otherApprovalDates = document.querySelector('.other-approval-dates');
            
            otherApprovalSelect.addEventListener('change', function() {
                otherApprovalDates.style.display = this.value === 'no' ? 'block' : 'none';
            });
            otherApprovalDates.style.display = otherApprovalSelect.value === 'no' ? 'block' : 'none';
        }
        
        // File upload styling
        document.getElementById('file-upload').addEventListener('change', function() {
            const label = document.querySelector('.custom-file-upload');
            if (this.files.length > 0) {
                label.innerHTML = `
                    <i class="fas fa-check-circle text-success fa-2x mb-3"></i>
                    <p>${this.files.length} file(s) selected</p>
                    <small class="text-muted">Ready to upload</small>
                `;
                label.style.borderColor = '#28a745';
            } else {
                label.innerHTML = `
                    <i class="fas fa-cloud-upload-alt fa-2x mb-3"></i>
                    <p>Click to upload supporting documents</p>
                    <small class="text-muted">(Max file size: 5MB. Supported formats: PDF, JPG, PNG)</small>
                `;
                label.style.borderColor = '#ccc';
            }
        });
        
        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate required fields in the current tab
            const activeTab = document.querySelector('.tab-pane.active');
            const requiredFields = activeTab.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Create error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please fill in all required fields';
                
                // Insert error message
                const formSection = activeTab.querySelector('.form-section');
                if (formSection && !activeTab.querySelector('.alert-danger')) {
                    formSection.prepend(errorDiv);
                }
                
                // Scroll to first error
                const firstError = activeTab.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
        
		function validateForm() {
		const cargoType = document.querySelector('select[name="cargo_type_id"]');
		const documentNumber = document.querySelector('input[name="document_number"]');
		let isValid = true;
    
		// Reset error states
		cargoType.classList.remove('is-invalid');
		documentNumber.classList.remove('is-invalid');
    
		// Validate cargo type
		if (!cargoType.value) {
        cargoType.classList.add('is-invalid');
        isValid = false;
        
        // Scroll to the select element
        setTimeout(() => {
            cargoType.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Focus on the select element
            cargoType.focus();
			}, 100);
			}
    
		// Validate document number
				if (documentNumber.value.trim() === "") {
			documentNumber.classList.add('is-invalid');
			isValid = false;
        
        // If cargo type is valid, scroll to document number
			if (isValid) {
            setTimeout(() => {
                documentNumber.scrollIntoView({ behavior: 'smooth', block: 'center' });
                documentNumber.focus();
            }, 100);
			}
		}
    
			if (!isValid) {
			// Show error message
				alert("Please fill in all required fields");
				return false;
				}
    
			return true;
			}
		
        // Initialize conditional fields
        document.addEventListener('DOMContentLoaded', function() {
            setupConditionalFields();
        });
    </script>
</body>
</html>
