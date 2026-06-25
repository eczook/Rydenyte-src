<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php";

if (empty($_USER)) {
    die("not allowed");
}

if ($_USER["role"] !== "Admin") {
    die("not allowed");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {

    $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/Admi/Textures/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedExtensions = ["png"];

    $fileName = basename($_FILES["image"]["name"]);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        $message = "Invalid image format.";
    } elseif ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $message = "Upload failed.";
    } else {
        $newName = uniqid("image_") . "." . $extension;
        $destination = $uploadDir . $newName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $destination)) {
            $message = "Image uploaded successfully! at http://www.ryblox.xyz/Admi/Textures/$newName";
        } else {
            $message = "Failed to save image.";
        }
    }
}
?>

<title>Upload PNG</title>

<div id="Container">
    <div id="Body">
        <form method="POST" enctype="multipart/form-data">
            <h1>Upload PNG</h1>

            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <label for="image">PNG Image</label>
            <input type="file" name="image" id="image" accept=".png,image/png" required>

            <br><br>

            <button type="submit">Upload PNG</button>
        </form>
    </div>
</div>