@echo off
title deploy

echo Starting Cloudflare Tunnel...
start "Cloudflared Tunnel" cmd /k "cloudflared tunnel run ryblox-tunnel"

timeout /t 3 >nul

echo Starting RCCService...
start "RCCService" cmd /k "rccservice -console"

start "Gameservers" cmd /k "python tracks/gameservers.py"

echo Deployed!
pause