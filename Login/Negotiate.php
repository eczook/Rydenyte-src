<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$authenticationToken = $_GET['suggest'] ?? '';

header("Content-Type: text/plain");

if ($authenticationToken === '') {
    http_response_code(400);
    echo "Missing authentication token";
    exit;
}

$stmt = $db->prepare("SELECT id FROM users WHERE auth = ? LIMIT 1");
$stmt->execute([$authenticationToken]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo "Invalid ticket";
    exit;
}

setcookie('.RYSECURITY',$authenticationToken,['expires' => time() + (30 * 60),'path'=> '/','httponly' => true,'secure' => !empty($_SERVER['HTTPS']),'samesite' => 'Lax']);

http_response_code(200);
echo 'true';