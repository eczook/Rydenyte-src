<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
$pageTitle = $pageTitle ?? "RYDENYTE Corporation";

$pageDescription = $pageDescription ?? "RYDENYTE is SAFE for kids! RYDENYTE is a FREE casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.";

$pageImage = $pageImage ?? "https://www.ryblox.xyz/images/rydenyte_logo.png";

$pageUrl = $pageUrl ?? "https://www.ryblox.xyz/";

$theme = "default";
if (isset($_USER) && $_USER["theme"] !== "default") {
    $theme = $_USER["theme"];
}
?>
<!DOCTYPE html>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.ashx?v=3&theme=<?= $theme ?>">
<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="author" content="RYDENTYE Corporation">
<meta name="description" content="RYDENYTE is SAFE for kids! RYDENYTE is a FREE casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, virtual world, avatar chat">