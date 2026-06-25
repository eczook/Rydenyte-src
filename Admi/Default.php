<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php"; 

if (empty($_USER)) {
    die("not allowed");
}

if ($_USER["role"] !== "Admin") {
    die("not allowed");
}

$stmt = $db->query("SELECT COUNT(*) FROM users");
$userCount = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM games");
$gameCount = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(visits) FROM games");
$totalVisits = $stmt->fetchColumn();

if ($totalVisits === null) {
    $totalVisits = 0;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<div id="Container">
    <div id="Body">
        <h1>Welcome to the Admin Panel, <?= htmlspecialchars($_USER["username"]) ?>!</h1>
        <div class="Card">
            <h3>Statistics</h3>
            <p>Users: <?= number_format($userCount) ?></p>
            <p>Games: <?= number_format($gameCount) ?></p>
            <p>Total Visits: <?= number_format($totalVisits) ?></p>
        </div>
        <h3>Users</h3>
        <a href="/Admi/Users/Economy.aspx">Economy</a>
        <br>
        <a href="/Admi/Keys.aspx">Invite Keys</a>
        <h3>Catalog</h3>
        <a href="/Admi/Catalog/Face.aspx">Upload a Face</a>
        <br>
        <a href="/Admi/Catalog/Hat.aspx">Upload a Hat</a>
        <br>
        <a href="/Admi/Catalog/Gear.aspx">Upload Gears</a>
        <br>
        <a href="/Admi/Catalog/Mesh.aspx">Upload a Mesh</a>
        <br>
        <a href="/Admi/Catalog/Texture.aspx">Upload a Texture</a>
        <h3>Misc</h3>
        <a href="/Admi/Misc/Asset.aspx">Download an Asset</a>
    </div>
</div>