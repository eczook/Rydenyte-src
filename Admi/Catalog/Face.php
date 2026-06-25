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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $name = $_FILES['file']['name'];
    $facename = $_POST["name"];
    $description = $_POST["description"];
    $pricetix = $_POST["price_tix"];
    $pricerobux = $_POST["price_robux"];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $filename = pathinfo($name, PATHINFO_FILENAME);

    if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
        die('Only PNG, JPG, and JPEG files are allowed.');
    }

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

    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd"
            version="4">
        <External>null</External>
        <External>nil</External>
        <Item class="Decal" referent="RBX0">
            <Properties>
                <token name="Face">5</token>
                <string name="Name">face</string>
                <float name="Shiny">20</float>
                <float name="Specular">0</float>
                <Content name="Texture">
                    <url>http://www.ryblox.xyz/asset/?id='.$nextId.'</url>
                </Content>
                <bool name="archivable">true</bool>
            </Properties>
        </Item>
    </roblox>';

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $nextId)) {

        file_put_contents($dir . ($nextId + 1), $xml);

        $stmt = $db->prepare("
            INSERT INTO catalog
            (asset_id, creator_id, name, description, price_tix, price_robux, category)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $nextId + 1,
            $_USER["id"],
            $facename,
            $description,
            $pricetix,
            $pricerobux,
            18
        ]);

        $lastId = $db->lastInsertId();

        file_get_contents(
            "https://www.ryblox.xyz/Thumbs/Renders/renderItem.ashx?itemId=$lastId"
        );

        header("Location: /Item.aspx?ID=$lastId");
        exit;
    }

    die("Failed to upload.");
}
?>

<!DOCTYPE html>
<title>Face Uploader</title>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<div id="Container">
    <div id="Body">
        <h1>Create Face</h1>
        <?php if (!empty($message)): ?>
            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">

            <label>Face Name</label>
            <br>
            <input type="text" name="name" required>

            <br><br>

            <label>Description</label>
            <br>
            <textarea name="description"></textarea>
            <br><br>
            <label>PNG File</label>
            <input type="file" name="file" accept=".png" required>
            <br><br>
            <label>Price (Tix)</label>
            <input type="number" name="price_tix" value="0">
            <br><br>
            <label>Price (Robux)</label>
            <input type="number" name="price_robux" value="0">
            <br>
            <label>
                <input type="checkbox" name="for_sale" checked>
                For Sale
            </label>

            <br><br>

            <button type="submit" class="Button">Create Face</button>

        </form>

    </div>
</div>