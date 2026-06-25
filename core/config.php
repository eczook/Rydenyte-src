<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "services/RCCService.php";

$apiConfig = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/core/includes/apiconfig.json"),true);
$db = Database::connect();
$rcc = new RCCServiceSoap("127.0.0.1",64989);
$config = [
    "db" => $db,
    "rcc" => $rcc,
];

$maxGames = 3;

date_default_timezone_set('Europe/Stockholm');

function timeAgo($datetime) {
    if (is_numeric($datetime)) {
        $datetime = (int)$datetime;
    } else {
        $datetime = strtotime($datetime);
    }

    if (!$datetime) {
        return "invalid date";
    }

    $now = time();
    $diff = $now - $datetime;

    if ($diff < 0) {
        return "in the future";
    }

    $units = [
        31536000 => "year",
        2592000 => "month",
        604800 => "week",
        86400 => "day",
        3600 => "hour",
        60 => "minute",
        1 => "second"
    ];

    foreach ($units as $seconds => $label) {
        $value = floor($diff / $seconds);
        if ($value >= 1) {
            return $value . " " . $label . ($value > 1 ? "s" : "") . " ago";
        }
    }

    return "just now";
}

function generatePort($db) {
    do {
        $port = rand(50000, 60000);

        $check = $db->prepare("SELECT COUNT(*) FROM gameservers WHERE port = ?");
        $check->execute([$port]);
        $exists = $check->fetchColumn();

    } while ($exists > 0);

    return $port;
}

function getCreator($db, $creatorId) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$creatorId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

function generateAuthToken($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = '';

    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $token;
}

function getAd($db, $size, $type = null, $itemId = null) {

    $sql = "
        SELECT *
        FROM ads
        WHERE size = ?
        AND status = 'approved'
    ";

    $params = [$size];

    if ($type && $itemId) {
        $sql .= " AND type = ? AND item_id = ? ";
        $params[] = $type;
        $params[] = $itemId;
    }

    $sql .= " ORDER BY RAND() LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) return null;
    
    $update = $db->prepare("UPDATE ads SET impressions = impressions + 1 WHERE id = ?");
    $update->execute([$ad["id"]]);

    return $ad;
}

function renderAd($ad) {
    if (!$ad) return "";

    $img = "/images/UserAds/" . htmlspecialchars($ad["filename"]);
    $alt = htmlspecialchars($ad["alt"]);

    $clickUrl = "/adclick.php?id=" . (int)$ad["id"];

    return <<<HTML
    <div style="position:relative;display:inline-block;">
        <a href="$clickUrl" target="_blank">
            <img src="$img" alt="$alt" style="display:block;border:1px solid black;margin-top:10px">
        </a>

        <a href="/AbuseReport/Ad.aspx?id={$ad['id']}"
        style="position:absolute;top:10px;right:0;background:#EEE;border:1px solid #000;font-family:Verdana;font-size:10px;color:blue;padding:2px;">
            [ report ]
        </a>
    </div>
    HTML;
}

function shutdownEmptyServers($db, $rcc) {
    $stmt = $db->prepare("SELECT port FROM gameservers WHERE players = 0 AND created_at <= DATE_SUB(NOW(), INTERVAL 120 SECOND)");
    $stmt->execute();

    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($servers as $server) {
        $port = (int)$server["port"];
        $db->prepare("DELETE FROM gameservers WHERE port = ?")->execute([$port]);
        $db->prepare("DELETE FROM players WHERE port = ?")->execute([$port]);
        $masterKey = "RYDENYTEMasterKey";
        $url = "http://127.0.0.1:7700/CloseGameserver". "?port=" . urlencode($port). "&key=" . urlencode($masterKey);

        $response = file_get_contents($url);

        if ($response === false) {
            die("failed to close gameserver");
        }
    }
}

shutdownEmptyServers($db, $rcc);
?>