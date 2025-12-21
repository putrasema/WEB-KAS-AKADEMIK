<?php
require_once __DIR__ . '/../src/Config/init.php';

$auth->requireLogin();
$currentUser = $auth->getCurrentUser();


if ($currentUser['role'] === 'admin') {

    header("Location: admin_dashboard.php");
    exit();
} else {

    header("Location: user_dashboard.php");
    exit();
}
