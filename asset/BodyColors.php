<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core/config.php');

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Content-Type: application/xml");

$_GET = array_change_key_case($_GET, CASE_LOWER);
$userId = (int)($_GET['userId'] ?? $_GET['userid'] ?? 0);

$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);

$fetch = $stmt->fetch(PDO::FETCH_OBJ);

if (!$fetch) {
    http_response_code(404);
    exit;
}

$colors = explode(';', $fetch->bodycolors);
?>
<?xml version="1.0" encoding="utf-8"?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
    <External>null</External>
    <External>nil</External>
    <Item class="BodyColors">
        <Properties>
            <int name="HeadColor"><?=$colors[0]?></int>
            <int name="LeftArmColor"><?=$colors[3]?></int>
            <int name="LeftLegColor"><?=$colors[4]?></int>
            <string name="Name">Body Colors</string>
            <int name="RightArmColor"><?=$colors[1]?></int>
            <int name="RightLegColor"><?=$colors[5]?></int>
            <int name="TorsoColor"><?=$colors[2]?></int>
            <bool name="archivable">true</bool>
        </Properties>
    </Item>
</roblox>