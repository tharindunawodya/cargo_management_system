<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Wharf Officer') {
    header('Location: ../../login.php');
    exit();
}

$document_id = $_GET['id'];

// Fetch document details
$sql = "SELECT * FROM documents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

// Fetch clearance details
$sql = "SELECT * FROM clearance WHERE document_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$clearance = $stmt->get_result()->fetch_assoc();

// Fetch delivery details
$sql = "SELECT * FROM delivery WHERE document_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$delivery = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Document Details</h1>
    <h2>Document Information</h2>
    <pre><?php print_r($document); ?></pre>

    <h2>Clearance Information</h2>
    <pre><?php print_r($clearance); ?></pre>

    <h2>Delivery Information</h2>
    <pre><?php print_r($delivery); ?></pre>
</body>
</html>