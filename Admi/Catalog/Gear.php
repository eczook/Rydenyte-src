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

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $asset_id = 0;
    if (
        isset($_FILES["rbxm_file"]) &&
        $_FILES["rbxm_file"]["error"] === UPLOAD_ERR_OK
    ) {

        $assetDir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";

        if (!is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
        }

        $highestId = 0;

        foreach (scandir($assetDir) as $file) {

            if ($file === "." || $file === "..") {
                continue;
            }

            if (ctype_digit($file)) {
                $id = (int)$file;

                if ($id > $highestId) {
                    $highestId = $id;
                }
            }
        }

        $asset_id = $highestId + 1;

        $destination = $assetDir . $asset_id;

        if (!move_uploaded_file($_FILES["rbxm_file"]["tmp_name"], $destination)) {
            $message = "Failed to upload file.";
            $asset_id = 0;
        }
    }
    $price_tix = (int)$_POST["price_tix"];
    $price_robux = (int)$_POST["price_robux"];
    $for_sale = isset($_POST["for_sale"]) ? 1 : 0;

    if (empty($name)) {
        $message = "Name is required.";
    } elseif ($asset_id <= 0) {
        $message = "Invalid asset id.";
    } else {
        $stmt = $db->prepare("
                INSERT INTO catalog (
                    creator_id,
                    asset_id,
                    name,
                    description,
                    price_tix,
                    price_robux,
                    favorites,
                    sold,
                    category,
                    for_sale,
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 0, 0, 19, ?, NOW(), NOW()
                )
            ");

            $stmt->execute([
                $_USER["id"],
                $asset_id,
                $name,
                $description,
                $price_tix,
                $price_robux,
                $for_sale
            ]);

            $message = "Gear added successfully!";
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Upload Gear</title>
<div id="Container">
    <div id="Body">
        <h1>Create Gear</h1>

        <?php if (!empty($message)): ?>
            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Gear Name</label>
            <br>
            <input type="text" name="name" required>
            <br><br>
            <label>Description</label>
            <br>
            <textarea name="description"></textarea>
            <br><br>
            <label>RBXM File</label>
            <br>
            <input type="file" name="rbxm_file" accept=".rbxm" required>
            <br><br>
            <label>Price (Tix)</label>
            <br>
            <input type="number" name="price_tix" value="0">
            <br><br>
            <label>Price (Robux)</label>
            <br>
            <input type="number" name="price_robux" value="0">
            <br>
            <label>
                <input type="checkbox" name="for_sale" checked>
                For Sale
            </label>

            <br><br>

            <button type="submit">Create Hat</button>

        </form>
    </div>
</div>