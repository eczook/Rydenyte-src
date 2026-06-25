<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";
header("Content-Type: text/plain");

$userId = $_SESSION["user_id"] ?? null;

if (!$userId) {
    die("ERROR|NOT_LOGGED_IN");
}

$gameId = intval($_GET["id"] ?? 0);

if ($gameId <= 0) {
    die("ERROR|INVALID_GAME");
}

$fetch = $db->prepare("
    SELECT *
    FROM games
    WHERE id = ?
    LIMIT 1
");

$fetch->execute([$gameId]);

$game = $fetch->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("ERROR|GAME_NOT_FOUND");
}

$server = $db->prepare("
    SELECT *
    FROM gameservers
    WHERE game_id = ?
    AND players < maxplayers
    ORDER BY players ASC
    LIMIT 1
");

$server->execute([$gameId]);
$server = $server->fetch(PDO::FETCH_ASSOC);

if ($server) {
    $port = $server["port"];
}
else {
    $port = generatePort($db);
}

echo "OK|" . $_USER["auth"] . "|" . $port;
exit;
?>