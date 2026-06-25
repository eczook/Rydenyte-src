<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
$itemId = $_GET["ItemID"] ?? $_GET["itemId"] ?? null;

if (empty($itemId)) {
    die("Item invalid");
}

$stmt = $db->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($item)) {
    die("Item not found");
}

$data = [
    "id" => $item["id"],
    "name" => $item["name"],
    "description" => $item["description"],
];

$map = [
    2 => "T-Shirt",
    11 => "Shirt",
    12 => "Pants",
    8 => "Hat",
    17 => "Head",
    18 => "Face",
];

if ($item["price_tix"] !== 0) {
    $data["tixPrice"] = $item["price_tix"];
}

if ($item["price_robux"] !== 0) {
    $data["robuxPrice"] = $item["price_robux"];
}

$data["type"] = $map[$item["category"]];
$data["thumb"] = "https://www.ryblox.xyz/Thumbs/Item.ashx?id=".$item["id"];

header("Content-Type: application/json");
echo json_encode($data);
?>