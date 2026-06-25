<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
session_start();

$userId = $_SESSION["user_id"] ?? null;

if ($userId) {
    $db->prepare("
        UPDATE users 
        SET online = 0
        WHERE id = ?
    ")->execute([$userId]);
}

session_unset();
session_destroy();

echo json_encode([
    "success" => true
]);

header("Location: /Default.aspx");