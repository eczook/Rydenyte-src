<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$uid = $_USER["id"];
$pose = $_POST["pose"] ?? "normal";

$allowed = ["normal", "walking", "sitting", "overlord", "zombie", "crime", "pistol"];

if (!in_array($pose, $allowed)) {
    echo json_encode(["success" => false]);
    exit;
}

$stmt = $db->prepare("UPDATE users SET pose = ? WHERE id = ?");
$stmt->execute([$pose, $uid]);

echo json_encode(["success" => true]);