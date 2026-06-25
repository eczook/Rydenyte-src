<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
$id = $_GET["id"] ?? null;
if (empty($id)) {
    die("Invalid Get Status");
}

$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$id]);
$gameItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($gameItem)) {
    die("Invalid game, does not exist");
}

header("Content-Type: text/plain");
readfile($_SERVER["DOCUMENT_ROOT"]."/asset/assets/{$gameItem["id"]}");
?>