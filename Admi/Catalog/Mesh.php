<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php";

if (empty($_USER)) {
    die("not allowed");
}

if ($_USER["role"] !== "Admin") {
    die("not allowed");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["mesh"])) {

    $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/Admi/Meshes/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedExtensions = ["mesh"];

    $fileName = basename($_FILES["mesh"]["name"]);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        $message = "Invalid mesh format.";
    } elseif ($_FILES["mesh"]["error"] !== UPLOAD_ERR_OK) {
        $message = "Upload failed.";
    } else {
        $newName = uniqid("mesh_") . "." . $extension;
        $destination = $uploadDir . $newName;

        if (move_uploaded_file($_FILES["mesh"]["tmp_name"], $destination)) {
            $message = "Mesh uploaded successfully! at http://www.ryblox.xyz/Admi/Meshes/$newName";
        } else {
            $message = "Failed to save mesh.";
        }
    }
}
?>

<title>Upload Mesh</title>

<div id="Container">
    <div id="Body">
        <form method="POST" enctype="multipart/form-data">
            <h1>Upload Mesh (has to be v1 format)</h1>

            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <label for="mesh">Mesh</label>
            <input type="file" name="mesh" id="mesh" accept=".mesh" required>

            <br><br>
            <a href="https://stan2474.github.io/roblox_utils/">To convert this to a 1.0.0 mesh, click me</a>
            <br><br>
            <p>^</p>
            <p>|</p>
            <a href="https://docs.google.com/document/d/1m64GqcpgO7Z_AJSkc4p0WPdocQBpXx-1nWKXDBSeq1Q/edit?tab=t.0">Guide on how to use stans guide</a>
            <button type="submit">Upload Mesh</button>
        </form>
    </div>
</div>