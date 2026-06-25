<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<title>RYDENYTE - 404 Not Found</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="AccountUpgradesConfirmationContainer">
            <h2>Renewal Cancelled</h2>
            <div id="Message">
                <p>Automatic renewal of your account upgrade has been cancelled.  Thanks for your order.</p>
                <p>We hope you will continue to find your time at RYDENYTE both safe and fun.  If there is ever anything we can do to make your experience even better, <a href="/">please let us know</a>.</p>
                <p>Thanks again,<br>
                    The RYDENYTE Team
                </p>
            </div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>