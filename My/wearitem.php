<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

header("Content-Type: application/json");

$userId = $_USER["id"];
$itemId = isset($_POST["itemId"]) ? intval($_POST["itemId"]) : 0;

if ($itemId <= 0) {
    echo json_encode(["success" => false]);
    exit;
}

$stmt = $db->prepare("SELECT 1 FROM owned_items WHERE user_id = ? AND item_id = ?");
$stmt->execute([$userId, $itemId]);

if (!$stmt->fetch()) {
    echo json_encode(["success" => false, "error" => "not owned"]);
    exit;
}

$stmt = $db->prepare("SELECT category FROM catalog WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo json_encode(["success" => false]);
    exit;
}

$category = $item["category"];

if ($category == 8) {

    // Check if already equipped
    $stmt = $db->prepare("
        SELECT 1
        FROM wearing
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$userId, $itemId]);

    if ($stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "error" => "already equipped"
        ]);
        exit;
    }
    
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM wearing w
        JOIN catalog c ON c.id = w.item_id
        WHERE w.user_id = ? AND c.category = 8
    ");
    $stmt->execute([$userId]);
    $count = $stmt->fetchColumn();

    if ($count >= 3) {
        echo json_encode([
            "success" => false,
            "error" => "max equipped"
        ]);
        exit;
    }
} else {
    $stmt = $db->prepare("
        DELETE w
        FROM wearing w
        JOIN catalog c ON c.id = w.item_id
        WHERE w.user_id = ? AND c.category = ?
    ");
    $stmt->execute([$userId, $category]);
}

$stmt = $db->prepare("
    INSERT INTO wearing (user_id, item_id)
    VALUES (?, ?)
");
$stmt->execute([$userId, $itemId]);

$stmt = $db->prepare("INSERT INTO wearing (user_id, item_id) VALUES (?, ?)");
$stmt->execute([$userId, $itemId]);

file_get_contents("http://www.ryblox.xyz/Thumbs/Renders/renderCharacter.ashx?userId=<?= $userId ?>");
echo json_encode(["success" => true]);