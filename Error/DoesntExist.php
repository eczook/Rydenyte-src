<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<title>RYDENYTE - 404 Not Found</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/headless.php"; ?>
    <div id="Body" style="text-align: center;padding-top:50px;padding-bottom:50px">
        <h1 style="text-align: center">The item you have requested does not exist.</h1>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>