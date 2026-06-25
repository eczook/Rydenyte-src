<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

header("Content-Type: application/json");

if (!isset($_USER)) {
    die(json_encode([
        "success" => false
    ]));
}

$part = (int)($_POST["part"] ?? 0);
$color = (int)($_POST["color"] ?? 0);

if ($part < 1 || $part > 6) {
    die(json_encode([
        "success" => false
    ]));
}

$stmt = $db->prepare("SELECT bodycolors FROM users WHERE id = ?");
$stmt->execute([$_USER["id"]]);

$bodycolors = $stmt->fetchColumn();

if (!$bodycolors) {
    $bodycolors = "24;24;23;24;119;119";
}

$colors = explode(";", $bodycolors);

while (count($colors) < 6) {
    $colors[] = 24;
}

switch ($part) {
    case 1:
        $colors[5] = $color;
        break;

    case 2:
        $colors[0] = $color;
        break;

    case 3:
        $colors[2] = $color;
        break;

    case 4:
        $colors[3] = $color;
        break;

    case 5:
        $colors[1] = $color;
        break;

    case 6:
        $colors[4] = $color;
        break;
}

$newColors = implode(";", $colors);

$update = $db->prepare("UPDATE users SET bodycolors = ? WHERE id = ?");
$update->execute([$newColors, $_USER["id"]]);

echo json_encode([
    "success" => true,
    "bodycolors" => $newColors
]);