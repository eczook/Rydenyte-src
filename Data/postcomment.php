<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (!isset($_USER["id"])) {
    http_response_code(403);
    exit("not logged in");
}

$item_id = isset($_POST["item_id"]) ? (int)$_POST["item_id"] : 0;
$type    = isset($_POST["type"]) ? $_POST["type"] : "item";
$content = trim($_POST["content"] ?? "");

if ($item_id <= 0 || $content === "") {
    exit("invalid request");
}

if ($type != "item" && $type != "place" && $type != "model") {
    exit("invalid type");
}

if (mb_strlen($content) > 2000) {
    exit("comment too long");
}

if ($type == "item") {
    $stmt = $db->prepare("SELECT id FROM catalog WHERE id = ?");
} else if ($type == "place") {
    $stmt = $db->prepare("SELECT id FROM games WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT id FROM models WHERE id = ?");
}

$stmt->execute([$item_id]);

if (!$stmt->fetchColumn()) {
    http_response_code(404);
    exit("not found");
}

$insert = $db->prepare("
    INSERT INTO comments
    (item_id, user_id, content, created_at, type)
    VALUES (?, ?, ?, NOW(), ?)
");

$insert->execute([
    $item_id,
    $_USER["id"],
    $content,
    $type
]);

if ($type == "item") {
    header("Location: /Item.aspx?ID=" . $item_id);
} else if ($type == "place") {
    header("Location: /Place.aspx?ID=" . $item_id);
} else {
    header("Location: /Model.aspx?ID=" . $item_id);
}

exit;
?>