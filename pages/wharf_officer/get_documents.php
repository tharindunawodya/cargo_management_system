<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Wharf Officer') {
    die("Unauthorized access.");
}

$cargo_type_id = $_GET['cargo_type_id'];

// Fetch documents for the selected cargo type
$sql = "SELECT id, document_number, file_no FROM documents WHERE cargo_type_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cargo_type_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Document Number</th>
                <th>File No</th>
                <th>Action</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['document_number']}</td>
                <td>{$row['file_no']}</td>
                <td><a href='view_document.php?id={$row['id']}'>View</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No documents found.";
}
?>