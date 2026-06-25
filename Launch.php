<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$userId = $_SESSION["user_id"] ?? null;

if (!$userId) {
    die("not logged in");
}

if ($_USER["verified"] === 0) {
    header("Location: /VerifyDiscord.aspx");
    exit;
}

$auth = $_USER["auth"];
$gameid = $_GET["id"];

if (empty($gameid)) {
    die("stop");
}

$check = $db->prepare("
    SELECT port FROM players 
    WHERE user_id = ?
    LIMIT 1
");
$check->execute([$userId]);
$currentServer = $check->fetch(PDO::FETCH_ASSOC);

if ($currentServer) {
    die("you are already in a game");
}

$fetch = $db->prepare("SELECT * FROM games WHERE id = ?");
$fetch->execute([$gameid]);
$game = $fetch->fetch(PDO::FETCH_ASSOC);

if (empty($game)) {
    die("game not found");
}

$server = $db->prepare("
    SELECT * FROM gameservers 
    WHERE game_id = ? AND players < maxplayers
    ORDER BY players ASC
    LIMIT 1
");
$server->execute([$gameid]);
$server = $server->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("UPDATE users SET last_seen = ?, last_seen_time = CURRENT_TIMESTAMP WHERE id = ?");
$stmt->execute([$game["name"],$userId]);

if ($server) {
    $port = $server["port"];
} else {
    $gameAssetId = $game["asset_id"];
    $port = generatePort($db);

    $masterKey = "RYDENYTEMasterKey";

    $url = "http://127.0.0.1:7700/OpenGameserver"."?port=".urlencode($port)."&mapid=".urlencode($gameAssetId)."&key=".urlencode($masterKey);
    $response = file_get_contents($url);

    if ($response === false) {
        die("failed to start gameserver");
    }

    $insert = $db->prepare("
        INSERT INTO gameservers (port, game_id, players, maxplayers)
        VALUES (?, ?, 0, 12)
    ");
    $insert->execute([$port, $gameid]);
}

$stmt = $db->prepare("UPDATE games SET visits = visits + 1 WHERE id = ?");
$stmt->execute([$game["id"]]);

$creatorId = $game["creator_id"];

if ($creatorId != $userId) {
    $cooldown = $db->prepare("SELECT rewarded_at FROM game_visit_rewards WHERE user_id = ? AND game_id = ?");
    $cooldown->execute([$userId, $game["id"]]);
    $row = $cooldown->fetch(PDO::FETCH_ASSOC);

    $canReward = false;

    if (!$row) {
        $canReward = true;
    } else {
        $lastReward = strtotime($row["rewarded_at"]);
        if (time() - $lastReward >= 3600) {
            $canReward = true;
        }
    }

    if ($canReward) {
        $reward = $db->prepare("
            UPDATE users
            SET robux = robux + 1
            WHERE id = ?
        ");
        $reward->execute([$creatorId]);

        $save = $db->prepare("
            INSERT INTO game_visit_rewards
                (user_id, game_id, rewarded_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                rewarded_at = NOW()
        ");
        $save->execute([$userId, $game["id"]]);
    }
}

sleep(10);
header("Location: rydenyte://join?auth=$auth&port=$port");
exit;
?>