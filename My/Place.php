<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$placeId = $_GET["ID"] ?? null;

if (empty($placeId)) {
    die("not found");
}

$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$placeId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($game)) {
    die("not found");
}

if ($game["creator_id"] !== $_USER["id"]) {
    die("this game isnt urs");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $cbAllowGears = isset($_POST["cbAllowGears"]) ? 1 : 0;

    if ($name === "" || mb_strlen($name) > 50) {
        die("invalid name");
    }

    if (mb_strlen($description) > 2000) {
        die("description too long");
    }

     if (!empty($_FILES["placefile"]["tmp_name"])) {
        $fileTmp = $_FILES["placefile"]["tmp_name"];
        $fileName = $_FILES["placefile"]["name"];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, ["rbxl", "rbxlx"])) {
            die("invalid file type");
        }
        
        $assetId = $game["asset_id"];
        $uploadPath = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/" . $assetId;

        if (!move_uploaded_file($fileTmp, $uploadPath)) {
            die("upload failed");
        }
        
        file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderPlace.ashx?placeId=$placeId");
    }

    $stmt = $db->prepare("
        UPDATE games 
        SET name = ?, description = ?, gears_allowed = ?
        WHERE id = ? AND creator_id = ?
    ");

    $stmt->execute([
        $name,
        $description,
        $cbAllowGears,
        $placeId,
        $_USER["id"]
    ]);

    header("Location: /Place.aspx?ID=" . $placeId);
    exit;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="ConfigurePlaceContainer">
            <h2>Configure Place</h2>
            
            <form method="post" enctype="multipart/form-data">
            <div id="PlaceName">
                <span class="Label">Name:</span><br>
                <input name="name" type="text" value="<?= htmlspecialchars($game["name"]) ?>" maxlength="50" class="TextBox">
            </div>
            
            <div id="PlaceThumbnail">
                <a disabled="disabled" supportsalphachannel="False" title="<?= htmlspecialchars($game["name"]) ?>" onclick="return false" style="display:inline-block;height:230px;width:420px;">
                    <img src="/Thumbs/Place.ashx?placeId=<?= $game["id"] ?>" border="0" id="img" alt="<?= htmlspecialchars($game["name"]) ?>">
                </a>
            </div>
            <div id="PlaceDescription">
                <span class="Label">Description:</span><br>
                <textarea name="description" rows="2" cols="20" class="MultilineTextBox" style="height:150px;"><?= htmlspecialchars($game["description"]) ?></textarea>
            </div>
            
            <div id="PlaceAccess">
                <fieldset title="Access"> 
                    <legend>Access</legend>
                    <div class="Suggestion">
                        This determines who can access your place.
                    </div>
                    <div class="PlaceAccessRow">
                        <img src="/images/public.png" alt="Public" style="border-width:0px;">
                        <input type="radio" name="PlaceAccess" value="rbPublicAccess" id="rbPublicAccess" checked="checked">
                        <label for="rbPublicAccess">Public: Anybody can visit my place</label><br>
                        <img src="/images/locked.png" alt="Friends-only" style="border-width:0px;">
                        <input type="radio" name="PlaceAccess" value="rbPrivateAccess" id="rbPrivateAccess">
                        <label for="rbPrivateAccess">Friends: Only my friends can visit my place</label>
                    </div>
                </fieldset>
            </div>
            
            <div id="PlaceCopyProtection">
                <fieldset title="Copy Protection">
                    <legend>Copy Protection</legend>
                    <div class="Suggestion">
                        Checking this will prevent your place from being copied but will also make it available to others only in online mode.
                    </div>
                    <div class="CopyProtectionRow">
                        <input type="checkbox" name="cbIsCopyProtected" id="cbIsCopyProtected" disabled="disabled">
                        <label for="cbIsCopyProtected">Copy-Lock my place</label>
                    </div>
                </fieldset>
            </div>

            <div id="PlaceCopyProtection">
                <fieldset title="Allow Gears">
                    <legend>Allow Gears</legend>
                    <div class="Suggestion">Checking this will allow you to have gears in your place.</div>
                    <div class="CopyProtectionRow">
                        <input type="checkbox" name="cbAllowGears" id="cbAllowGears"<?= $game["gears_allowed"] ? 'checked="checked"' : '' ?>>
                        <label for="cbIsCopyProtected">Gears Allowed</label>
                    </div>
                </fieldset>
            </div>
            
            <div id="PlaceReset">
                <fieldset>
                    <legend>RBXL</legend>
                    <div class="Suggestion">
                        Upload your place file (.rbxl / .rbxlx)
                    </div>
                    <center>
                    <input class="Button" type="file" name="placefile" accept=".rbxl,.rbxlx">
                                    </center>
                    <br>
                </fieldset>
                <center><br>
                    <input class="Button" type="submit" value="Update" style="cursor:pointer;">
                    <a class="Button" href="/User.aspx">Cancel</a>
                </center>
            </div>
            </form>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>