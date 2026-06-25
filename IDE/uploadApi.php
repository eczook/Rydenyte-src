<?php
$xml = file_get_contents("php://input");

if ($xml) {
    $filename = uniqid() . ".rbxl";
    file_put_contents(__DIR__ . "/uploads/" . $filename, $xml);
    echo "OK";
} else {
    http_response_code(400);
    echo "No data received";
}
?>