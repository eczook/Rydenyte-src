<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$client_id = "1511765543630409779";
$redirect = urlencode("https://www.ryblox.xyz/Data/Discord/Callback.aspx");

$url = "https://discord.com/api/oauth2/authorize". "?client_id=$client_id". "&redirect_uri=$redirect". "&response_type=code". "&scope=identify";
header("Location: $url");
exit;