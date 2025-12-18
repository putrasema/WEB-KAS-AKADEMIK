<?php
require_once 'config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Redirect based on role
if ($currentUser['role'] === 'admin') {
    // Admin request to see the "User" view style (Transactions focused)
    header("Location: user_dashboard.php");
    exit();
} else {
    // Students/Users request to see the "Admin" view style (Currency Graph, Global Stats)
    header("Location: admin_dashboard.php");
    exit();
}