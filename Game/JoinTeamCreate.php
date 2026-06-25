<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
$auth = $_GET["auth"] ?? null;
$ip = $_GET["ip"] ?? null;
$port = $_GET["port"] ?? 53640;

if (empty($auth)) {
    http_response_code(403);
    die("print('invalid auth')");
}

if (empty($ip)) {
    http_response_code(403);
    die("print('invalid ip')");
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
?>
pcall(function() game:GetService("Players"):SetChatStyle(Enum.ChatStyle.ClassicAndBubble) end)

local Visit = game:service("Visit")
local Players = game:service("Players")
local NetworkClient = game:service("NetworkClient")
local camera = workspace.Camera

local userId = <?php echo $auth !== null ? $player["id"]  : 0 . "\n"; ?> <?php echo "\n"; ?>
local username = "<?php echo $auth !== null ? $player["username"] : ''; ?>"
local fakeModel = nil

local ContentProvider = nil
pcall(function() ContentProvider = game:service("ContentProvider") end)

local function onConnectionRejected()
    game:SetMessage("This game is not available. Please try another")
end

local function onConnectionFailed(_, id, reason)
    game:SetMessage("Failed to connect to the Game. (ID="..id..")")
end

local function cleanup()
    if fakeModel then
        fakeModel:Destroy()
        fakeModel = nil
    end
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

    while not workspace.Camera do
        wait()
    end

    local camera = workspace.Camera

    fakeModel = Instance.new("Model")
    fakeModel.Name = player.Name

    local head = Instance.new("Part")
    head.Name = "Head"
    head.Size = Vector3.new(2,2,2)
    head.Shape = "Ball"
    head.TopSurface = "Smooth"
    head.BottomSurface = "Smooth"
    head.FrontSurface = "Hinge"
    head.BrickColor = BrickColor.new(math.random(1,24))
    head.Position = Vector3.new(0,1.5,0)
    head.Anchored = true
    head.CanCollide = false
    head.Transparency = 0.25
    head.Locked = true
    head.Parent = fakeModel

    local decal = Instance.new("Decal")
    decal.Parent = head
    decal.Texture = "rbxasset://textures\face.png"

    local humanoid = Instance.new("Humanoid")
    humanoid.Parent = fakeModel
    humanoid.Health = 0
    humanoid.MaxHealth = 0

    --local hat = game:GetObjects("http://ryblox.xyz/asset/?id=15")[1]
    --hat.Parent = fakeModel
    --hat.Handle.Anchored = true

    fakeModel.PrimaryPart = head
    fakeModel.Parent = workspace

    wait(1.2)
    game:ClearMessage()

    while true do
        wait(0.01)

        if camera then
            --hat.Handle.CFrame = camera.CoordinateFrame * CFrame.new(0, 1, 3)
            head.Size = Vector3.new(2,2,2)
            head.CFrame = camera.CoordinateFrame * CFrame.new(0, 0, 3)
        end
    end
end

NetworkClient.ConnectionAccepted:connect(onConnectionAccepted)
NetworkClient.ConnectionRejected:connect(onConnectionRejected)
NetworkClient.ConnectionFailed:connect(onConnectionFailed)

game:SetMessage("Connecting to Server")

local success, errorMsg = pcall(function ()
    local player = Players.LocalPlayer
    if not player then
        player = Players:createLocalPlayer(userId)
    end

    player.Name = username
    NetworkClient:connect("<?= $ip ?>", <?= $port ?>, 0)
end)

Players.PlayerRemoving:connect(function(plr)
    if plr == Players.LocalPlayer then
        cleanup()
    end
end)

if not success then
    game:SetMessage(errorMsg)
end