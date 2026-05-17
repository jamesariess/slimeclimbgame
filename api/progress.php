<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
$userId = require_user();
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $_GET['action'] ?? ($input['action'] ?? 'load');

if ($action === 'load') {
    json_response(['ok' => true, 'save' => load_player_save($userId)]);
}

if ($action === 'save') {
    save_player_progress($userId, $input);
    json_response(['ok' => true]);
}

json_response(['ok' => false, 'message' => 'Unknown progress action.'], 400);
