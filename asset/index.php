<?php
$id = $_GET['id'] ?? null;
$version = $_GET['version'] ?? null;

if (!$id || !is_numeric($id)) {
    header("Content-Type: text/plain");
    exit("Invalid asset");
}

$assetpath = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/$id";

$api = "https://proxy.95.fyi/assetrequest.php?id=" . urlencode($id);

if ($version === "1") {
    $api .= "&version=1";
}

function fetch($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => "Mozilla/5.0",
    ]);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        return false;
    }

    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http >= 400 || !$data) {
        return false;
    }

    return $data;
}

if (!file_exists($assetpath)) {
    $content = fetch($api);

    if ($content === false) {
        http_response_code(502);
        exit("Failed to download asset");
    }
} else {
    $content = file_get_contents($assetpath);
}

$content = str_replace('roblox.com', 'ryblox.xyz', $content);

header("Content-Type: application/octet-stream");
echo $content;
