<?php
session_start();
include '../../includes/db.php';
include '../../includes/auth.php';
// include '../../includes/sidebar.php';

redirectIfNotLoggedIn();
if ($_SESSION['role'] != 'Wharf Officer') {
    header('Location: ../../login.php');
    exit();
}

// Fetch cargo types
$sql = "SELECT * FROM cargo_types";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Cargo</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <h1>Select Cargo Type</h1>
    <form id="cargoForm">
        <label for="cargo_type">Cargo Type:</label>
        <select id="cargo_type" name="cargo_type">
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['type']; ?></option>
            <?php endwhile; ?>
        </select>
        <button type="button" onclick="loadDocuments()">Load Documents</button>
    </form>
    <div id="document_list"></div>

    <script>
    function loadDocuments() {
        const cargoTypeId = document.getElementById('cargo_type').value;
        fetch(`get_documents.php?cargo_type_id=${cargoTypeId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('document_list').innerHTML = data;
            });
    }
    </script>
</body>
</html>