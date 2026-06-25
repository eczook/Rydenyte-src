<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
session_start();

$userId = $_SESSION["user_id"] ?? null;

if (!$userId) {
    exit;
}

$db->prepare("
    UPDATE users 
    SET online = 1, last_seen_time = NOW()
    WHERE id = ?
")->execute([$userId]);

echo "ok";