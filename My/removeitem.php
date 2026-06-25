<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

header("Content-Type: application/json");

$userId = $_USER["id"];
$itemId = intval($_POST["itemId"] ?? 0);

$stmt = $db->prepare("DELETE FROM wearing WHERE user_id = ? AND item_id = ?");
$stmt->execute([$userId, $itemId]);

echo json_encode(["success" => true]);