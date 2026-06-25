<?php
$port = $_GET["port"] ?? 53640;
$gameid = $_GET["mapid"] ?? null;

header("Content-Type: text/lua"); 
?>
<?php if (empty($gameid)): ?>
game:Load('http://ryblox.xyz/Data/Baseplate.rbxl')
<?php else: ?>
game:Load('http://ryblox.xyz/Data/GetGame.ashx?id=<?= $gameid ?>')
<?php endif; ?>
local port = <?= $port . "\n" ?>
local sleepTime = 10
local placeId = 1

local ns = game:service("NetworkServer")

game:GetService("Players").PlayerAdded:connect(function(player)
	print("Player " .. player.userId .. " added")
	player.Chatted:connect(function(msg)
		local fakeChar = workspace:FindFirstChild(player.Name)

		if fakeChar then
			local originalName = player.Name
			fakeChar.Name = originalName .. ": " .. msg
			local co = coroutine.create(function()
				wait(3)
				if fakeChar then
					fakeChar.Name = originalName
				end
			end)
			coroutine.resume(co)
		end
	end)
end)

game:service("Players").PlayerRemoving:connect(function(player)
    print("Player " .. player.userId .. " left")
    local fakeChar = workspace:FindFirstChild(player.Name)
    if fakeChar then
	    fakeChar:Remove()
    end
end)

if port>0 then
   ns:start(port, sleepTime) 
end

game:service("RunService"):run()