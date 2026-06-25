<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core/config.php');

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Content-Type: text/plain");

$userId = $_GET["userId"] ?? null;

if (empty($userId)) {
    die("Invalid userid");
}

$appearanceParts = [];

$wearing = $db->prepare("
    SELECT c.asset_id, c.category
    FROM wearing w
    JOIN catalog c ON c.id = w.item_id
    WHERE w.user_id = ?
");
$wearing->execute([$userId]);
$wearingItems = $wearing->fetchAll(PDO::FETCH_ASSOC);

foreach ($wearingItems as $item) {
    if (!empty($item["asset_id"])) {
        if ($item["category"] === 19) {
            $appearanceParts[] = $item["asset_id"];
        }
    }
}

$appearanceString = implode(",", $appearanceParts);
echo $appearanceString;