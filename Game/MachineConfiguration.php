<?php

$log = dirname(__DIR__) . '/request_log.txt';
$data = [
    'Time: ' . date('Y-m-d H:i:s'),
    'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? ''),
    'Method: ' . ($_SERVER['REQUEST_METHOD'] ?? ''),
    'URI: ' . ($_SERVER['REQUEST_URI'] ?? ''),
    'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    'GET: ' . print_r($_GET, true),
    'POST: ' . print_r($_POST, true),
    'Headers: ' . print_r(getallheaders(), true),
    'Body: ' . file_get_contents('php://input'),
    str_repeat('-', 80)
];
file_put_contents($log, implode("\n", $data) . "\n", FILE_APPEND);
echo "Logged";