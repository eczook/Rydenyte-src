<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
$stmt = $db->query("SELECT COUNT(*) AS game_count FROM games");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$gameCount = $result["game_count"] ?? 0;

echo $gameCount;
?>