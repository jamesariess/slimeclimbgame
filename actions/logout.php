<?php
require __DIR__ . '/../api/config.php';
start_app_session();
verify_csrf_from_post();
$_SESSION = [];
session_destroy();
header('Location: ../index.php');
exit;
