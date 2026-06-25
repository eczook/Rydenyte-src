<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
$port = $_GET["port"] ?? 53640;
$key = $_GET["key"] ?? null;
$assetid = $_GET["assetid"] ?? null;

$correct = "RYDENYTEMasterKey";

if (empty($assetid)) {
    die("print('no asset id')");
}

if (empty($key)) {
    die("print('invalid key')");
}

if ($key !== $correct) {
	http_response_code(400);
	die("print('invalid key')");
}

$stmt = $db->prepare("SELECT * FROM games WHERE asset_id = ?");
$stmt->execute([$assetid]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if ($game["gears_allowed"] == 1) {
	$gearsAllowed = "true";
} else {
	$gearsAllowed = "false";
}

if (empty($game)) {
	die("print('game not found')");
}
header("Content-Type: text/lua");
?>
game:Load('http://www.ryblox.xyz/asset/?id=<?php echo $assetid; ?>&accessKey=RYDENYTEAssetMasterKey1234')
local port = <?php echo $port . "\n"; ?>
local gearsAllowed = <?php echo $gearsAllowed . "\n"; ?> 
local sleepTime = 10
local placeId = 1

-- establish this peer as the Server
local ns = game:service("NetworkServer")

_G.Rydenyte = {
	cloudstore = {
		new = function()

		end,
	}
}

-- utility
function waitForChild(parent, childName)
	while true do
		local child = parent:findFirstChild(childName)
		if child then
			return child
		end
		parent.ChildAdded:wait()
	end
end

-- returns the player object that killed this humanoid
-- returns nil if the killer is no longer in the game
function getKillerOfHumanoidIfStillInGame(humanoid)

	-- check for kill tag on humanoid - may be more than one - todo: deal with this
	local tag = humanoid:findFirstChild("creator")

	-- find player with name on tag
	if tag then
		local killer = tag.Value
		if killer.Parent then -- killer still in game
			return killer
		end
	end

	return nil
end

-- send kill and death stats when a player dies
function onDied(victim, humanoid)
	local killer = getKillerOfHumanoidIfStillInGame(humanoid)

	local victorId = 0
	if killer then
		victorId = killer.userId
		print("STAT: kill by " .. victorId .. " of " .. victim.userId)
		game:httpGet("http://www.ryblox.xyz/Game/Statistics.ashx?TypeID=15&UserID=" .. victorId .. "&AssociatedUserID=" .. victim.userId .. "&AssociatedPlaceID=" .. placeId .. "&key=RYDENYTEMasterKey")
	end
	print("STAT: death of " .. victim.userId .. " by " .. victorId)
	game:httpGet("http://www.ryblox.xyz/Game/Statistics.ashx?TypeID=16&UserID=" .. victim.userId .. "&AssociatedUserID=" .. victorId .. "&AssociatedPlaceID=" .. placeId .. "&key=RYDENYTEMasterKey")
end

-- listen for the death of a Player
function createDeathMonitor(player)
	-- we don't need to clean up old monitors or connections since the Character will be destroyed soon
	if player.Character then
		local humanoid = waitForChild(player.Character, "Humanoid")
		humanoid.Died:connect(
			function ()
				onDied(player, humanoid)
			end
		)
	end
end

-- listen to all Players Characters
game:service("Players").ChildAdded:connect(
	function (player)
		createDeathMonitor(player)
		player.Changed:connect(
			function (property)
				if property=="Character" then
					createDeathMonitor(player)
				end
			end
		)
	end
)

-- This code might move to C++
function characterRessurection(player)
	if player.Character then
		local humanoid = player.Character.Humanoid
		humanoid.Died:connect(function() wait(5) player:LoadCharacter() end)
	end
end
game:service("Players").PlayerAdded:connect(function(player)
	print("Player " .. player.userId .. " added")
	characterRessurection(player)
    game:HttpGet("http://www.ryblox.xyz/Game/PlayerJoined.ashx?Port="..port.."&UserID="..player.userId)
	player.Changed:connect(function(name)
		if name=="Character" then
			local gears = game:HttpGet("http://www.ryblox.xyz/Data/gears.ashx?userId="..player.userId,true)
			if gears ~= "" and gearsAllowed then
				for gear in string.gmatch(gears, "[^,]+") do
					local gear = game:GetObjects("http://www.ryblox.xyz/asset/?id="..gear)[1]
					gear.Parent = player.Backpack
				end
			end
			characterRessurection(player)
		end
	end)
	player.Chatted:connect(function(msg)
		if msg == "/reset" then
			player.Character.Humanoid.Health = 0
		end
	end)
end)

game:service("Players").PlayerRemoving:connect(function(player)
	print("Player " .. player.userId .. " left!")
    game:HttpGet("http://www.ryblox.xyz/Game/PlayerLeft.ashx?Port="..port.."&UserID="..player.userId)
    if #game.Players:GetPlayers() == 0 then
        game:httpGet("http://www.ryblox.xyz/Game/CloseServer.ashx?Port=<?php echo $port;?>&t="..tick(), true)
    end
end)

if port>0 then
	ns:start(port, sleepTime) 
end

game:service("RunService"):run()