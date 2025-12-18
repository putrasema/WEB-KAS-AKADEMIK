<?php
require_once __DIR__ . '/../src/Config/init.php';

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: home.php");
}
exit();
