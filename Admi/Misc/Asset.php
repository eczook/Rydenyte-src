<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (empty($_USER)) {
    die("not allowed");
}

if ($_USER["role"] !== "Admin") {
    die("not allowed");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $assetid = $_POST["assetid"];

    $useVersion1 = isset($_POST["useversion1"]);

    $url = "https://assetdelivery.ttblox.mom/v1/asset/?id={$assetid}";

    if ($useVersion1) {
        $url .= "&version=1";
    }

    $response = file_get_contents($url);

    if ($response !== false) {

        $folder = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $path = $folder . "/" . $assetid;

        file_put_contents($path, $response);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $assetid . ".rbxm\"");
        header("Content-Length: " . strlen($response));

        echo $response;
        exit;
    } else {
        $message = "Failed to download asset.";
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Asset Downloader</title>
<div id="Container">
    <h3>Asset Downloader</h3>
    <form method="POST">
        <input type="text" name="assetid" placeholder="asset id" required>
        <br><br>
        <label>
            <input type="checkbox" name="useversion1">
            Use version 1
        </label>
        <br><br>
        <button type="submit">Download</button>
    </form>

    <?php if ($message): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

</div>