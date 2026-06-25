<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
$placeId = $_GET["PlaceID"] ?? $_GET["placeId"] ?? null;

if (empty($placeId)) {
    die("Game invalid");
}

$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$placeId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($game)) {
    die("Game not found");
}

$creator = getCreator($db, $game["creator_id"]);

header("Content-Type: application/json");
echo json_encode([
    "name" => $game["name"],
    "description" => $game["description"],
    "thumbnail" => "http://www.ryblox.xyz/Thumbs/Place.ashx?placeId=".$game["id"],
    "visits" => $game["visits"],
    "creator" => [
        "name" => $creator["username"],
        "userid" => $creator["id"]
    ],
    "created" => $game["created_at"],
    "updated" => $game["updated_at"]
]);
?>