<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

header("Content-Type: application/json");

$gameId = (int)($_GET["gameId"] ?? 0);

if ($gameId <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $db->prepare("
    SELECT 
        gs.id,
        gs.port,
        gs.players AS server_players,
        gs.maxplayers,
        COUNT(p.user_id) AS actual_players
    FROM gameservers gs
    LEFT JOIN players p ON p.port = gs.port
    WHERE gs.game_id = ?
    GROUP BY gs.id
    ORDER BY gs.id DESC
");

$stmt->execute([$gameId]);

$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($servers);