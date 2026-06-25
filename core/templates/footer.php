<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
?>
<div id="Footer">
    <hr>
    <div class="FooterNav">
        <a id="ctl00_rbxFooter_hlPrivacyPolicy" href="info/Privacy.aspx"><b>Privacy Policy</b></a>
        &nbsp;|&nbsp; 
        <a id="ctl00_rbxFooter_hlAdvertise" href="/">Advertise with Us</a>
        &nbsp;|&nbsp; 
        <a id="ctl00_rbxFooter_hlContact" href="info/ContactUs.aspx">Contact Us</a>
        &nbsp;|&nbsp;
        <a id="ctl00_rbxFooter_hlAboutRoblox" href="info/About.aspx">About Us</a>
        &nbsp;|&nbsp;
        <a id="ctl00_rbxFooter_HyperLink1" href="/">Jobs</a>
        <?php if ($_USER["role"] === "Admin"): ?>
        &nbsp;|&nbsp;
        <a id="ctl00_rbxFooter_HyperLink2" href="/Admi/Default.aspx">Admin Panel</a>
        <?php endif; ?>
    </div>
    <hr>
     <p class="Legalese">
        RYDENYTE, "Online Building Toy", characters, logos, names, and all related indicia
        are trademarks of
        <a id="ctl00_rbxFooter_hlRobloxCorporation" href="info/About.aspx">RYDENYTE Corporation</a>,
        ©2009. Patents pending.
        <br>
        RYDENYTE is not sponsored, authorized or endorsed by any producer of plastic building
        bricks, including The LEGO Group, MEGA Brands, ROBLOX Corporation, and K’Nex,<br> and no resemblance to
        the products of these companies is intended.<br>Use of this site signifies your acceptance of the
        <a id="ctl00_rbxFooter_hlTermsOfService" href="info/TermsOfService.aspx">Terms and Conditions</a>.
        <br>
    </p>
</div>
<script>
    setInterval(function () {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "/Heartbeat.ashx", true);
        xhr.withCredentials = true;
        xhr.send(null);
    }, 30000);
</script>