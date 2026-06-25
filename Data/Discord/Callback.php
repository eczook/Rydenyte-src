<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$client_id = "1511765543630409779";
$client_secret = "hOGogYwXbVEB-AJxrUHImG97Clkc693Y";
$redirect_uri = "https://www.ryblox.xyz/Data/Discord/Callback.aspx";

$code = $_GET['code'] ?? null;
if (!$code) die("No code provided");

$token_url = "https://discord.com/api/oauth2/token";

$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "authorization_code",
    "code" => $code,
    "redirect_uri" => $redirect_uri,
];

$options = [
    "http" => [
        "header" => "Content-Type: application/x-www-form-urlencoded",
        "method" => "POST",
        "content" => http_build_query($data)
    ]
];

$response = file_get_contents($token_url, false, stream_context_create($options));
$token = json_decode($response, true);

$access_token = $token["access_token"];

$user_url = "https://discord.com/api/users/@me";

$options = ["http" => ["header" => "Authorization: Bearer $access_token"]];

$user_response = file_get_contents($user_url, false, stream_context_create($options));
$user = json_decode($user_response, true);

$discord_id = $user["id"];
$username = $user["username"];

// Check if this Discord ID is already used by another account
$stmt = $db->prepare("
    SELECT id
    FROM users
    WHERE discord_id = ?
    AND id != ?
    LIMIT 1
");
$stmt->execute([$discord_id, $_SESSION['user_id']]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {

    // Permanently ban the current account
    $stmt = $db->prepare("
        INSERT INTO bans (
            user_id,
            ban_type,
            reason,
            moderator_note,
            issued_at
        ) VALUES (?, 'permanent', ?, ?, NOW())
    ");

    $stmt->execute([$_SESSION['user_id'],'Attempted verification with a Discord account already linked to another user.','Automatic anti-alt detection.']);

    die("This Discord account is already linked to another user. Your account has been permanently banned.");
}

$stmt = $db->prepare("UPDATE users SET discord_id = ?, verified = 1 WHERE id = ?");
$stmt->execute([$discord_id, $_SESSION['user_id']]);
$stmt = $db->prepare("
    SELECT id FROM badges
    WHERE owned_by = ? AND name = 'Verified'
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$hasBadge = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hasBadge) {
    $stmt = $db->prepare("
        INSERT INTO badges (owned_by, name)
        VALUES (?, 'Verified')
    ");
    $stmt->execute([$_SESSION['user_id']]);
}

$webhook_url = "https://discord.com/api/webhooks/1515696501295284355/njokOHdynR7m6XODHOh1skzRsFroEThsUz2tg0XlkZ4fMKX15vhE-KVY2S68UW1tZBMh";
$avatar = $user["avatar"] ?? null;

if ($avatar) {
    $avatar_url = "https://cdn.discordapp.com/avatars/{$discord_id}/{$avatar}.png?size=256";
} else {
    $avatar_url = "https://cdn.discordapp.com/embed/avatars/0.png";
}

$profile_url = "https://discord.com/users/" . $discord_id;
$payload = [
    "embeds" => [[
        "title" => "discord verification token",
        "description" => "someone verified",
        "thumbnail" => [
            "url" => $avatar_url
        ],
        "fields" => [
            [
                "name" => "Discord Username",
                "value" => $username,
                "inline" => true
            ],
            [
                "name" => "Discord ID",
                "value" => $discord_id,
                "inline" => false
            ]
        ]
    ]]
];

$ch = curl_init($webhook_url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true
]);
curl_exec($ch);
curl_close($ch);

header("Location: /VerifySuccess.aspx");
exit;