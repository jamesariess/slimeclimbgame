<?php
require __DIR__ . '/../api/config.php';
start_app_session();
$_SESSION = [];
session_destroy();
header('Location: ../index.php');
exit;

