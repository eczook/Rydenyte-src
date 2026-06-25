from flask import Flask, Response, request
from flask import jsonify

import subprocess

app = Flask(__name__)

directory = r"C:\Program Files (x86)\Rydenyte"
gameservers = {}

MASTER_KEY = "RYDENYTEMasterKey"
BASE_URL = "ryblox.xyz"

def check_key(req):
    return req.args.get("key") == MASTER_KEY

@app.route("/OpenGameserver")
def open_gameserver():
    if not check_key(request):
        return Response("Unauthorized", status=401)
    
    port = request.args.get("port")
    mapid = request.args.get("mapid")

    if not port:
        return Response("Missing port parameter", status=400)

    exe = directory + r"\Rydenyte.exe"
    script = f"dofile('http://www.{BASE_URL}/Game/Host.ashx?port={port}&assetid={mapid}&key={MASTER_KEY}')"

    process = subprocess.Popen([exe, "-script", script])
    gameservers[int(port)] = process

    return Response("OK", mimetype="text/plain")

@app.route("/CloseGameserver")
def close_gameserver():
    if not check_key(request): return Response("Unauthorized", status=401)

    port = request.args.get("port")

    if not port: return Response("Missing port parameter", status=400)
    
    port = int(port)

    process = gameservers.get(port)

    if process is None: return Response("Server not found", status=404)

    process.terminate()
    gameservers.pop(port, None)

    return Response("OK", mimetype="text/plain")

@app.route("/ListGameservers")
def list_gameservers():
    if not check_key(request): return Response("Unauthorized", status=401)
    servers = []
    for port, process in gameservers.items():
        servers.append({
            "port": port,
            "running": process.poll() is None
        })
    return jsonify(servers)

if __name__ == "__main__":
    app.run(host="0.0.0.0",port=7700, debug=True)