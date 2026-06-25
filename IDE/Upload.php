<?php
$xml = file_get_contents("php://input");

if ($xml) {
    $filename = uniqid() . ".rbxl";
    file_put_contents(__DIR__ . "/gameuploadtest/" . $filename, $xml);
    echo "uploaded";
}
?>
<title>RYDENYTE - Upload</title>
<p>Publish this game?</p>
<button onclick="uploadGame()">Save to RYDENYTE</button>
<script>
function uploadGame() {
    try {
        var content = window.external.Write();
        alert("Uploading...");
        content.Upload("http://www.ryblox.xyz/IDE/Upload.ashx");
    } catch (e) {
        alert("Upload failed: " + e.message);
    }
}
</script>