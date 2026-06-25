<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

if (!isset($_USER) || $_USER["role"] !== "Admin") {
    http_response_code(403);
    exit("Forbidden");
}

$itemId = (int)($_POST["item_id"] ?? 0);

if ($itemId <= 0) {
    http_response_code(400);
    exit("Invalid item ID");
}

$stmt = $db->prepare("
    SELECT id, asset_id, name
    FROM catalog
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$itemId]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    http_response_code(404);
    exit("Item not found");
};

$db->prepare("
    DELETE FROM owned_items
    WHERE item_id = ?
")->execute([$itemId]);

$db->prepare("
    DELETE FROM comments
    WHERE item_id = ?
      AND type = 'item'
")->execute([$itemId]);

$db->prepare("
    DELETE FROM catalog
    WHERE id = ?
")->execute([$itemId]);

header("Location: /Catalog.aspx");
exit;