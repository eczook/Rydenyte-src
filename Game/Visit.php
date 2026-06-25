<?php
header("Content-Type: text/lua");
?>
game:GetService("RunService"):Run() 
local Visit = game:service("Visit")
local plr = game.Players:CreateLocalPlayer(0) 
plr:LoadCharacter() 
game.Players.Player.userId = 1818
plr.Character.Humanoid.Changed:connect(function() 
    if plr.Character.Humanoid.Health == 0 then 
        plr:LoadCharacter() 
    end 
end)