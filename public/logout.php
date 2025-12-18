<?php
require_once __DIR__ . '/../src/Config/init.php';
$auth->logout();
header("Location: login.php");
exit();
