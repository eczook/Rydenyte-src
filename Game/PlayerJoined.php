<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$port = $_GET["Port"] ?? null;
$userId = $_GET["UserID"] ?? null;

if (!$port || !$userId || !is_numeric($port) || !is_numeric($userId)) {
    die("invalid");
}

$server = $db->prepare("SELECT * FROM gameservers WHERE port = ?");
$server->execute([$port]);
$server = $server->fetch(PDO::FETCH_ASSOC);

if (!$server) {
    die("server not found");
}

$check = $db->prepare("
    SELECT COUNT(*) FROM players 
    WHERE user_id = ? AND port = ?
");
$check->execute([$userId, $port]);

if ($check->fetchColumn() == 0) {
    $db->prepare("
        INSERT INTO players (user_id, port)
        VALUES (?, ?)
    ")->execute([$userId, $port]);

    $db->prepare("
        UPDATE gameservers 
        SET players = players + 1 
        WHERE port = ?
    ")->execute([$port]);
}

echo "ok";