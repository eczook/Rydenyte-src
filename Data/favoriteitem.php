<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

header("Content-Type: text/plain");

if (empty($_USER)) {
    echo "NO";
    exit;
}

$userId = $_USER["id"];

$itemId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$type = $_GET["type"] ?? "";

$allowedTypes = ["game", "catalog"];
if ($itemId <= 0 || !in_array($type, $allowedTypes)) {
    echo "NO";
    exit;
}

$stmt = $db->prepare("
    SELECT id 
    FROM favorites 
    WHERE user_id = ? 
      AND item_type = ? 
      AND item_id = ?
    LIMIT 1
");
$stmt->execute([$userId, $type, $itemId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmt = $db->prepare("
        DELETE FROM favorites 
        WHERE id = ?
    ");
    $stmt->execute([$existing["id"]]);

    echo "NO";
    exit;
}

$stmt = $db->prepare("
    INSERT INTO favorites (user_id, item_type, item_id)
    VALUES (?, ?, ?)
");
$stmt->execute([$userId, $type, $itemId]);

echo "OK";