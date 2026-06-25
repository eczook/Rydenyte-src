<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php"; 
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="VerifyFrame" style="margin: 50px auto 150px auto; width: 500px; border: black thin solid; padding: 21px; z-index: 8; background-color: white;">
            <h2>Before you can play, please verify your discord account.</h2>
            <p>We require this and we can ban you if we think you shouldnt be associated with RYDENYTE.</p>
            <a style="color: blue;" href="/Data/Discord/Verify.aspx">Ok, i wanna verify.</a>
            <br><br>
            <button onclick="window.location = '/Default.aspx'">Back to Home</button>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>