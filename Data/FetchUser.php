<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
$userId = $_GET["UserID"] ?? $_GET["userId"] ?? null;

if (empty($userId)) {
    die("User invalid");
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($user)) {
    die("User not found");
}

header("Content-Type: application/json");
echo json_encode([
    "id" => $user["id"],
    "name" => $user["username"],
    "description" => $user["blurb"],
    "knockouts" => $user["knockouts"],
    "wipeouts" => $user["wipeouts"],
    "role" => $user["role"],
    "tix" => $user["tix"],
    "robux" => $user["robux"],
    "last_seen" => $user["last_seen"],
    "last_seen_at" => $user["last_seen_time"],
    "thumb" => "https://www.ryblox.xyz/Thumbs/Avatar.ashx?userId={$user["id"]}",
    "joined" => $user["created_at"],
]);
?>