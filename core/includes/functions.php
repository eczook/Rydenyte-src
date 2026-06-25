<?php
function renderCharacter($payload, $db, $rcc, $job) {

    $userId = $payload["userId"];
    
    $fetch = $db->prepare("SELECT * FROM users WHERE id = ?");
    $fetch->execute([$userId]);
    $user = $fetch->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("user not found");
    }

    $charapp = file_get_contents(
        "http://www.ryblox.xyz/asset/CharacterFetch.ashx?userId=$userId"
    );

    if (!$charapp) {
        throw new Exception("failed to fetch character appearance");
    }

    $b64 = $rcc->execScript("
        game.Players:CreateLocalPlayer(0)

        local player = game.Players.Player
        player:LoadCharacter()

        player.CharacterAppearance = '$charapp'

        return game:GetService('ThumbnailGenerator'):Click('PNG', 500, 500, true)
    ", "character_" . $job["id"]);

    if (!$b64) {
        throw new Exception("rcc failed");
    }

    $img = base64_decode($b64);

    $path = $_SERVER["DOCUMENT_ROOT"] . "/Thumbs/Renders/Avatars/$userId.png";
    file_put_contents($path, $img);

    $db->prepare("
        UPDATE render_queue 
        SET status='done', result_path=?, updated_at=NOW()
        WHERE id=?
    ")->execute([$path, $job["id"]]);
}