<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/headless.php"; ?>
    <div id="Body">
        <h3>Hey! Do you wanna make a trailer for RYDENYTE?</h3>
        <p>Well look no further, this is the official RYDENYTE Trailer Contest!</p>
        <div id="SpeechBubble" style="margin-left: 150px;">
            <h4>Even i wanna take part!</h4>
        </div>
        <img src="/Thumbs/Avatar.ashx?userId=1" width="152" height="152">
        <p>Submit your trailers in the #trailer-submissions channel!</p>
        <a href="/Default.aspx">Back to Home</a>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>