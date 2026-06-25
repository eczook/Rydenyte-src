<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
$id = $_GET["id"] ?? null;
if (empty($id)) {
    die("Invalid Get Status");
}

$stmt = $db->prepare("SELECT * FROM catlaog WHERE id = ?");
$stmt->execute([$id]);
$catlaogItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($catlaogItem)) {
    die("Invalid Item, does not exist");
}

header("Content-Type: text/plain");
?>