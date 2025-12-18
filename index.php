<?php
require_once 'config/init.php';

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: home.php");
}
exit();
