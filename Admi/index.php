<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (empty($_USER)) {
    die("not allowed");
}

if ($_USER["role"] !== "Admin") {
    die("not allowed");
}

header("Location: /Admi/Default.aspx");
?>