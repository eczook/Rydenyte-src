<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/core/config.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {

    $stmt = $db->prepare("UPDATE ads SET clicks = clicks + 1 WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("SELECT * FROM ads WHERE id = ?");
    $stmt->execute([$id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ad) {
        if ($ad["type"] === "place") {
            header("Location: /Place.aspx?ID={$ad['item_id']}");
        } else {
            header("Location: /Item.aspx?ID={$ad['item_id']}");
        }
        exit;
    }
}
exit;