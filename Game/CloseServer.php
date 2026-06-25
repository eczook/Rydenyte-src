<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$port = $_GET["Port"] ?? null;

if (!$port || !is_numeric($port)) {
    die("invalid");
}

$db->prepare("DELETE FROM gameservers WHERE port = ?")->execute([$port]);
$db->prepare("DELETE FROM players WHERE port = ?")->execute([$port]);

$masterKey = "RYDENYTEMasterKey";
$url = "http://127.0.0.1:7700/CloseGameserver". "?port=" . urlencode($port). "&key=" . urlencode($masterKey);

$response = file_get_contents($url);

if ($response === false) {
    die("failed to close gameserver");
}

echo "closed";