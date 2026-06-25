<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$userId = $_USER["id"];

$itemId = $_GET["id"] ?? null;
$currency = $_GET["currency"] ?? null;

if (!$itemId || !in_array($currency, ["tix", "robux", "free"])) {
    die("Invalid request");
}

$stmt = $db->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found");
}

if ($item["for_sale"] === 0) {
    die("Item is offsale");
}

if ($currency === "tix") {
    $price = $item["price_tix"];
} else {
    $price = $item["price_robux"];
}

$userStmt = $db->prepare("SELECT tix, robux FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

if ($currency === "free") {
    if ($item["price_tix"] > 0 || $item["price_robux"] > 0) {
        die("This item is not free");
    }

} else {
    if ($currency === "tix") {
        $price = $item["price_tix"];
    } else {
        $price = $item["price_robux"];
    }
    if ($price <= 0) {
        die("This item cannot be bought with this currency");
    }
    $userStmt = $db->prepare("SELECT tix, robux FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found");
    }

    if ($currency === "tix") {

        if ($user["tix"] < $price) {
            die("Not enough Tx");
        }

        $newBalance = $user["tix"] - $price;
        $update = $db->prepare("
            UPDATE users
            SET tix = ?
            WHERE id = ?
        ");

        $update->execute([$newBalance, $userId]);
    } else {

        if ($user["robux"] < $price) {
            die("Not enough R$");
        }

        $newBalance = $user["robux"] - $price;
        $update = $db->prepare("
            UPDATE users
            SET robux = ?
            WHERE id = ?
        ");

        $update->execute([$newBalance, $userId]);
    }
}

$inv = $db->prepare("
    INSERT INTO owned_items (user_id, item_id)
    VALUES (?, ?)
");
$inv->execute([$userId, $itemId]);

$db->prepare("UPDATE catalog SET sold = sold + 1 WHERE id = ?")->execute([$itemId]);

header("Location: /Item.aspx?ID=" . $itemId);
exit;