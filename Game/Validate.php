<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
$auth = $_GET["auth"] ?? null;

if (empty($auth)) {
    die("INVALID");
}

$stmt = $db->prepare("SELECT * FROM users WHERE auth = ?");
$stmt->execute([$auth]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo md5(bin2hex(50) + bin2hex(500));
} else {
    echo "INVALID";
}
?>