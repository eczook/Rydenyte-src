<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
$auth = $_GET["auth"] ?? null;
$port = $_GET["port"] ?? 53640;

if (empty($auth)) {
    http_response_code(403);
    die("print('invalid auth')");
}

$player = null;
if (!empty($auth)) {
    $stmt = $db->prepare("SELECT * FROM users WHERE auth = ?");
    $stmt->execute([$auth]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$player) {
    http_response_code(403);
    die("print('invalid auth')");
}

if ($player["verified"] === 0) {
    http_response_code(403);
    die("print('user not verified')");
}

header("Content-Type: text/lua"); 
$userId = $player["id"];
$charapp = file_get_contents("http://www.ryblox.xyz/asset/CharacterFetch.ashx?userId=$userId");

$authNew = generateAuthToken(10);
$stmt = $db->prepare("UPDATE users SET auth = ? WHERE id = ?");
$stmt->execute([$authNew,$player["id"]]);

$wearing = $db->prepare("SELECT * FROM wearing w JOIN catalog c ON c.id = w.item_id WHERE w.user_id = ?");
$wearing->execute([$userId]);
$wearingItems = $wearing->fetchAll(PDO::FETCH_ASSOC);
?>

local Visit = game:service("Visit")
local Players = game:service("Players")
local NetworkClient = game:service("NetworkClient")

local userId = <?php echo $auth !== null ? $player["id"]  : 0 . "\n"; ?> <?php echo "\n"; ?>
local serverIp = "26.92.122.9"
local authEmpty = <?php echo $auth !== null ? 'false' : 'true' . "\n"; ?> <?php echo "\n"; ?>
local username = "<?php echo $auth !== null ? $player["username"] : ''; ?>"

local ContentProvider = nil
pcall(function() ContentProvider = game:service("ContentProvider") end)

local function onConnectionRejected()
    game:SetMessage("This game is not available. Please try another")
end

local function onConnectionFailed(_, id, reason)
    game:SetMessage("Failed to connect to the Game. (ID="..id..")")
end

local function onConnectionAccepted(peer, replicator)
    local worldReceiver = replicator:SendMarker()
    local received = false

    local function onWorldReceived()
        received = true
    end

    worldReceiver.Received:connect(onWorldReceived)
    game:SetMessageBrickCount()

    while not received do
        wait(0.3)
    end

    local player = Players.LocalPlayer
    game:SetMessage("Requesting character")
    replicator:RequestCharacter()

    wait(1.2)
    game:ClearMessage()
end

NetworkClient.ConnectionAccepted:connect(onConnectionAccepted)
NetworkClient.ConnectionRejected:connect(onConnectionRejected)
NetworkClient.ConnectionFailed:connect(onConnectionFailed)

if authEmpty then
    game:SetMessage("Auth cannot be empty")
    return
end
game:SetMessage("Connecting to Server")

local success, errorMsg = pcall(function ()
    local player = Players.LocalPlayer
    if not player then
        player = Players:createLocalPlayer(userId)
    end

    player.Name = username
    player.CharacterAppearance = "<?php echo $charapp; ?>"

    local clientTicket = Instance.new("StringValue",player)
    clientTicket.Value = "<?= $auth ?>:<?= $port ?>"
    <?php 
    if ($player["chat_mode"] === 1) { 
        echo "player:SetSuperSafeChat(true)" . "\n"; 
    } else {
        echo "\n";
    }; 
    ?>
    Visit:SetUploadUrl("")
    NetworkClient:connect(serverIp, <?php echo $port; ?>, 0)
end)

if not success then
    game:SetMessage(errorMsg)
end