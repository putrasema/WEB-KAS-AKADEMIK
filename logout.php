<?php
require_once 'config/init.php';
$auth->logout();
header("Location: login.php");
exit();
