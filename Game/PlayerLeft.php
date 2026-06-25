<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$port = $_GET["Port"] ?? null;
$userId = $_GET["UserID"] ?? null;

if (!$port || !$userId || !is_numeric($port) || !is_numeric($userId)) {
    die("invalid");
}

$delete = $db->prepare("
    DELETE FROM players 
    WHERE user_id = ? AND port = ?
");
$delete->execute([$userId, $port]);

if ($delete->rowCount() > 0) {
    $db->prepare("
        UPDATE gameservers 
        SET players = GREATEST(players - 1, 0)
        WHERE port = ?
    ")->execute([$port]);
}

echo "ok";