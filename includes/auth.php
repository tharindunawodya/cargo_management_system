<?php

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db.php';

function authenticateUser($username, $password) {
    global $conn; // Now $conn is available
    
    // Validate input
    if (empty($username) || empty($password)) {
        return false;
    }

    try {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
        }
        return false;
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function adminOnly() {
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Admin privileges required.');
    }
}

function isViewer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Viewer';
}

function viewerOnly() {
    if (!isViewer()) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Viewer privileges required.');
    }
}

function redirectBasedOnRole() {
    if (!isset($_SESSION['role'])) {
        header('Location: login.php');
        exit();
    }
    
    switch ($_SESSION['role']) {
        case 'Admin':
            header('Location: admin/manage_users.php');
            break;
        case 'Wharf Officer':
            header('Location: wharf_officer/dashboard.php');
            break;
        case 'Viewer':
            header('Location: viewer/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit();
}


// Add to your existing auth.php
function verifyUserExists($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows === 1;
}

function updateUserPassword($username, $new_password) {
    global $conn;            
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $username);
    return $stmt->execute();
}

function updatePassword($username, $newPassword) {
    global $conn;
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    return $stmt->execute();
}


?>