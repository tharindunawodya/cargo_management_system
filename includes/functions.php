<?php

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')));
}

function processClearanceData($postData, $documentId) {
    return [
        'document_id' => $documentId,
        'nmra_approval' => $postData['nmra_approval'] ?? 'no',
        'nmra_applied_date' => !empty($postData['nmra_applied_date']) ? $postData['nmra_applied_date'] : null,
        'nmra_received_date' => !empty($postData['nmra_received_date']) ? $postData['nmra_received_date'] : null,
        'wor_number' => sanitizeInput($postData['wor_number'] ?? ''),
        // Add all other clearance fields here
    ];
}

function insertClearanceData($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO clearance 
        (document_id, nmra_approval, nmra_applied_date, nmra_received_date, wor_number, ...)
        VALUES (?, ?, ?, ?, ?, ...)");
    
    $stmt->bind_param("issss...", 
        $data['document_id'],
        $data['nmra_approval'],
        $data['nmra_applied_date'],
        $data['nmra_received_date'],
        $data['wor_number'],
        // Bind all other parameters
    );
    
    return $stmt->execute();
}

function processDeliveryData($postData, $documentId) {
    return [
        'document_id' => $documentId,
        'description_of_cargo_goods' => sanitizeInput($postData['description_of_cargo_goods'] ?? ''),
        'name' => sanitizeInput($postData['name'] ?? ''),
        'quantity' => (int)($postData['quantity'] ?? 0),
        // Add all other delivery fields here
    ];
}

function insertDeliveryData($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO delivery 
        (document_id, description_of_cargo_goods, name, quantity, ...)
        VALUES (?, ?, ?, ?, ...)");
    
    $stmt->bind_param("issi...", 
        $data['document_id'],
        $data['description_of_cargo_goods'],
        $data['name'],
        $data['quantity'],
        // Bind all other parameters
    );
    
    return $stmt->execute();
}