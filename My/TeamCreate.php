<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Rydenyte - Team Create</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <h1 style="color: red;">DO NOT JOIN ANY SERVERS YOU DONT TRUST. ONLY JOIN SERVERS YOU TRUST.</h1>
        <h2>Welcome.</h2>
        <p>Team create, a feature that hasnt existed in older version of roblox. Has been downported into rydenyte now.</p>
        <div id="Host">
            <i>Host, this will make a gameserver on 0.0.0.0 essentially meaning any open ip. It is recommended to use Radmin VPN for this.</i>
           <form id="hostForm">
                <p>
                    It is recommended to use a game id for this, so you can save
                    progress where you left off with your friend(s), if not provided
                    a plain baseplate with starter bricks will be loaded.
                </p>

                <input type="text" id="game-id" placeholder="Game id here">
                <input type="text" id="port" placeholder="Port here">

                <button type="submit">Host</button>
            </form>
        </div>
        <div id="Join">
            <br>
            <i>Join, you will join a team create server with the provided details.</i>
            <br><br>
            <form id="joinForm">
                <input type="text" id="ip-address" placeholder="IP Address here">
                <input type="text" id="join-port" placeholder="Port here">

                <button type="submit">Join</button>
            </form>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>
<script>
document.getElementById("hostForm").addEventListener("submit", function(e) {
    e.preventDefault();

    var port = document.getElementById("port").value;
    var gameId = document.getElementById("game-id").value;

    if (port === null) {
        port = 53640;
    }

    let url = "rydenyte://hostcreate?port=" + encodeURIComponent(port);

    if (gameId.trim() !== "") {
        url += "&mapid=" + encodeURIComponent(gameId);
    }

    window.location.href = url;
});
document.getElementById("joinForm").addEventListener("submit", function(e) {
    e.preventDefault();

    var ip = document.getElementById("ip-address").value;
    var port = document.getElementById("join-port").value;
    var auth = "<?= $_USER["auth"] ?>";

    var url = "rydenyte://joincreate" + "?auth=" + encodeURIComponent(auth) + "&port=" + encodeURIComponent(port) + "&ip=" + ip;

    window.location.href = url;
});
</script>