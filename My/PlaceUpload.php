<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

if (empty($_USER)) {
    header("Location: /Login/Default.aspx");
    exit;
}

if ($_USER["role"] === "Admin") {
    $maxGames = 99999;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES["rbxl"])) {
        $message = "No file uploaded.";
    } else {

        $file = $_FILES["rbxl"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $message = "Upload failed.";
        } else {

            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, ["rbxl", "rbxlx"])) {
                $message = "Invalid file type.";
            } else {

                $dir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";

                $files = scandir($dir);
                $max = 0;

                foreach ($files as $f) {
                    if (is_numeric($f)) {
                        $num = (int)$f;
                        if ($num > $max) {
                            $max = $num;
                        }
                    }
                }

                $nextId = $max + 1;

                $targetPath = $dir . $nextId;
                $tmp = $file["tmp_name"];
                $data = file_get_contents($tmp);
                $data = str_replace("roblox.com", "ryblox.xyz", $data);

                if (file_put_contents($targetPath, $data) !== false) {
                    $fileName = pathinfo($_FILES["rbxl"]["name"], PATHINFO_FILENAME);
                    $stmt = $db->prepare("
                        INSERT INTO games (asset_id, name, description, creator_id)
                        VALUES (?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $nextId,
                        $fileName,
                        "",
                        $_USER["id"],
                    ]);
                    $gameid = $db->lastInsertId();
                    file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderPlace.ashx?placeId=$gameid");
                    header("Location: /Place.aspx?ID=$gameid");
                } else {
                    $message = "Failed to upload file.";
                }
            }
        }
    }
}

$baseMaxGames = $maxGames ?? 0;
$isAdmin = $_USER["role"] === "Admin";

$stmt = $db->prepare("SELECT extra_places FROM memberships WHERE user_id = ?");
$stmt->execute([$_USER["id"]]);

$extraPlaces = $stmt->fetchColumn();
$extraPlaces = $extraPlaces !== false ? (int)$extraPlaces : 0;

if ($isAdmin) {
    $finalMaxGames = 99999;
} else {
    $finalMaxGames = $baseMaxGames + $extraPlaces;
}

$stmt = $db->prepare("SELECT COUNT(*) FROM games WHERE creator_id = ?");
$stmt->execute([$_USER["id"]]);
$gameCount = (int)$stmt->fetchColumn();

$gamesRemaining = max(0, $finalMaxGames - $gameCount);

if ($gamesRemaining <= 0) {
    header("Location: /Error/DoesntExist.aspx");
    exit;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE - Place Uploader</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div id="ContentBuilderContainer">
                <h2>Place Uploader</h2><br>
                
                <div class="InstructionsPanel">
                    <h3>Instructions</h3>
                    <p>On RYDENYTE, a Place is a file that can be played by other users or edited by its creator. To create a Place:</p>
                    <ol>
                        <li>Click the "Browse" button below.</li>
                        <li>Use the File Explorer that pops up to browse your computer.</li>
                        <li>Find and select the file that you want to use as your place. Any place file (.rbxl, .rbxlx) will work.</li>
                        <li>Finally, click the "Create Place" button.</li>
                    </ol>
                    <p>The place you selected will be uploaded to RYDENYTE, where we will create a Place and add it to your inventory. To use this Place, simply go to your user and look at your user places panel and you will find the place item. Press the visit solo or visit online button to play it!</p>
                </div>
                
                <div id="upload" class="UploaderPanel">
                    <h3>Upload Place</h3>
                    <br>
                    
                    <input id="filename" type="text" name="filename" disabled="" placeholder="No file selected">
                    <input id="files" type="file" name="rbxl" accept=".rbxl,.rbxlx">
                    <br>
                    <br>
                    <input type="submit" value="Create Place">
                    
                                    
                                    
                    <br>
                    <div style="padding:10px 1px 1px 1px"></div>
                    
                    <script>
                        document.getElementById("files").addEventListener("change", function(e) {
                            var filename = this.files[0] ? this.files[0].name : "";
                            document.getElementById("filename").value = filename;
                        });
                    </script>
                </div>
            </div>
        </form>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>