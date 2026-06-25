<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$type = $_GET["TypeID"] ?? null;
$userId = $_GET["UserID"] ?? null;
$key = $_GET["key"] ?? null;

$correct = "RYDENYTEMasterKey";

$assocUserId = $_GET["AssociatedUserID"] ?? null;
$placeId = $_GET["AssociatedPlaceID"] ?? null;

if ($correct !== $key) {
    http_response_code(400);
    exit("invalid key");
}

if (!$type || !$userId) {
    http_response_code(400);
    exit("missing params");
}

if ($type == 15 && $userId) {
    $stmt = $db->prepare("
        UPDATE users 
        SET knockouts = COALESCE(knockouts, 0) + 1
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    
    $stmt = $db->prepare("
        SELECT knockouts
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $badgesToGive = [];
    $kos = (int)$user["knockouts"];

    if ($kos == 10) { $badgesToGive[] = "Combat Initiation"; } 
    if ($kos == 100) { $badgesToGive[] = "Warrior"; } 
    if ($kos == 250) { $badgesToGive[] = "Bloxxer"; }

    foreach ($badgesToGive as $badge) {
        $stmt = $db->prepare("
            INSERT IGNORE INTO badges (owned_by, name)
            VALUES (?, ?)
        ");

        $stmt->execute([$userId, $badge]);
    }
}

if ($type == 16 && $userId) {

    $stmt = $db->prepare("
        UPDATE users 
        SET wipeouts = COALESCE(wipeouts, 0) + 1
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

echo "ok";