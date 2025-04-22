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
           // Prepare the statement
$stmt = $conn->prepare("INSERT INTO documents 
    (document_number, original_received, received_from, file_no, 
     invoice_no, invoice_date, packing_list_no, letter_of_donation, 
     analysis_certificate, purchase_order_no, master_air_way_bill_no, 
     house_air_way_bill_no, cargo_type_id, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind parameters correctly
$stmt->bind_param("ssssssssssssii", 
    $documentData['document_number'],
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
    $documentData['cargo_type_id'],
    $_SESSION['user_id']
);

// Execute the statement
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
                'custom_duty_charges_value' => !empty($_POST['custom_duty_charges_value']) ? (float)$_POST['custom_duty_charges_value'] : null,
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
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
    <!-- Head content remains the same -->
</head>
<body>
    <?php include '../../includes/wharf_nav.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3 col-lg-2">
                <?php include '../../includes/wharf_sidebar.php'; ?>
            </div>
            
            <div class="col-md-9 col-lg-10">
                <!-- Success/Error messages remain the same -->
                
                <form method="POST" id="donationForm">
                    <!-- Tab navigation remains the same -->
                    
                    <div class="tab-content" id="donationTabContent">
                        <!-- Document Tab -->
                        <div class="tab-pane fade show active" id="document" role="tabpanel">
                            <div class="form-section">
                                <h4 class="section-title"><i class="fas fa-file-alt me-2"></i>Document Information</h4>
                                
                                <div class="row">
                                    <!-- Document Number -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Document Number*</label>
                                        <input type="text" class="form-control" name="document_number" required>
                                    </div>
                                    
                                    <!-- Cargo Type -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cargo Type*</label>
                                        <select class="form-select" name="cargo_type_id" required>
                                            <option value="">Select Cargo Type</option>
                                            <?php while ($type = $cargoTypes->fetch_assoc()): ?>
                                                <option value="<?= $type['id'] ?>"><?= $type['type'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <!-- Original Received -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="original_received" id="original_received">
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
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="letter_of_donation" id="letter_of_donation">
                                            <label class="form-check-label" for="letter_of_donation">Letter of Donation</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Analysis Certificate -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="analysis_certificate" id="analysis_certificate">
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
                            </div>
                            
                            <!-- Navigation buttons -->
                        </div>

                        <!-- Clearance Tab -->
                        <div class="tab-pane fade" id="clearance" role="tabpanel">
                            <div class="form-section">
                                <h4 class="section-title"><i class="fas fa-check-circle me-2"></i>Clearance Information</h4>
                                
                                <!-- NMRA Approval -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>NMRA Approval</h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Approval Status</label>
                                        <select class="form-select" name="nmra_approval">
                                            <option value="yes">Approved</option>
                                            <option value="no" selected>Not Approved</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Applied Date</label>
                                        <input type="date" class="form-control" name="nmra_applied_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Received Date</label>
                                        <input type="date" class="form-control" name="nmra_received_date">
                                    </div>
                                </div>
                                
                                <!-- WOR Number -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">WOR Number</label>
                                        <input type="text" class="form-control" name="wor_number">
                                    </div>
                                </div>
                                
                                <!-- ICL Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>ICL (Import Control License)</h5>
                                    </div>
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
                                
                                <!-- ICL Debit Process -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>ICL Debit Process</h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Applied Date</label>
                                        <input type="date" class="form-control" name="icl_debit_applied_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Received Date</label>
                                        <input type="date" class="form-control" name="icl_debit_received_date">
                                    </div>
                                </div>
                                
                                <!-- Continue with all other clearance sections -->
                                <!-- Foreign Exchange, Excise Certificate, TRC Approval, etc. -->
                                
                                <!-- Custom Duty Charges -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>Custom Duty Charges</h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Value (LKR)</label>
                                        <input type="number" step="0.01" class="form-control" name="custom_duty_charges_value">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Voucher Handed Over Date</label>
                                        <input type="date" class="form-control" name="custom_duty_voucher_handed_over_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cheque Received Date</label>
                                        <input type="date" class="form-control" name="custom_duty_cheque_received_date">
                                    </div>
                                </div>
                                
                                <!-- Continue with Shipping Line Charges, Port Charges, etc. -->
                                
                                <!-- Shipment Details -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>Shipment Details</h5>
                                    </div>
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
                            
                            <!-- Navigation buttons -->
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
                            </div>
                            
                            <!-- Submit button -->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab navigation and form validation scripts
    </script>
</body>
</html>