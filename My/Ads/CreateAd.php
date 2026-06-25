<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$targetId = $_GET["targetId"] ?? null;
$type = $_GET["type"] ?? null;

$allowedTypes = ["item", "place"];

$adSizes = [
    "728x90"  => [728, 90],
    "160x600" => [160, 600],
    "300x250" => [300, 250],
];

$message = "";

if (!in_array($type, $allowedTypes)) {
    die("Invalid type.");
}

function resizeAdImage($srcPath, $destPath, $w, $h) {

    $src = imagecreatefrompng($srcPath);
    if (!$src) return false;

    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $dst = imagecreatetruecolor($w, $h);

    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    imagecopyresampled(
        $dst,
        $src,
        0, 0, 0, 0,
        $w, $h,
        $srcW, $srcH
    );

    imagepng($dst, $destPath);

    imagedestroy($src);
    imagedestroy($dst);

    return true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["adtitle"] ?? "");
    $size  = $_POST["size"] ?? "";

    if ($title === "" || !$targetId || !$type) {
        $message = "Missing required fields.";
    }
    elseif (!isset($adSizes[$size])) {
        $message = "Invalid ad size.";
    }
    elseif (!isset($_FILES["adfile"]) || $_FILES["adfile"]["error"] !== 0) {
        $message = "File upload failed.";
    }
    else {

        $fileTmp  = $_FILES["adfile"]["tmp_name"];
        $fileName = $_FILES["adfile"]["name"];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($ext !== "png") {
            $message = "Only PNG files are allowed.";
        }
        else {

            [$w, $h] = $adSizes[$size];

            $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/images/UserAds/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFile = "ad_" . time() . "_" . rand(1000,9999) . ".png";
            $destination = $uploadDir . $newFile;

            $ok = resizeAdImage($fileTmp, $destination, $w, $h);

            if ($ok) {

                $stmt = $db->prepare("
                    INSERT INTO ads
                    (alt, filename, creator_id, item_id, type, size, status, impressions, clicks)
                    VALUES
                    (:alt, :filename, :creator_id, :item_id, :type, :size, 'pending', 0, 0)
                ");

                $stmt->execute([
                    ":alt" => $title,
                    ":filename" => $newFile,
                    ":creator_id" => $_SESSION["user_id"],
                    ":item_id" => (int)$targetId,
                    ":type" => $type,
                    ":size" => $size
                ]);

                header("Location: /My/AdInventory.aspx");
                exit;
            }
            else {
                $message = "Image processing failed.";
            }
        }
    }
}
?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<div id="Container">
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

<div id="Body">
<div style="max-width:900px; margin:0 auto;">
<div id="Profile">

<h2>Create an Ad</h2>

<?php if ($message): ?>
    <p style="font-weight:bold; color:green;">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<div style="padding:10px 15px; font-family:Verdana,sans-serif; font-size:11px;">

<p><a href="/My/AdInventory.aspx">← Back to Ad Inventory</a></p>

<fieldset>
<legend>Upload Ad</legend>

<form method="POST" enctype="multipart/form-data">

    <p><b>Upload PNG Ad</b></p>
    <input type="file" name="adfile" accept=".png">

    <p><b>Ad Title</b></p>
    <input type="text" name="adtitle" class="TextBox" style="width:240px;">

    <p><b>Size</b></p>
    <select name="size" class="Button">
        <option value="728x90">728 x 90 Banner</option>
        <option value="160x600">160 x 600 Skyscraper</option>
        <option value="300x250">300 x 250 Rectangle</option>
    </select>

    <br><br>
    <input type="submit" value="Upload" class="Button">

</form>

</fieldset>

</div>
</div>
</div>
</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>