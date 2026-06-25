<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php"; 
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="BuildersClubContainer">
            <div id="JoinBuildersClubNow"><img id="ctl00_cphRoblox_HeaderImage" src="/images/JoinBuildersClubNow.png" alt="Join Builders Club Now!" style="border-width:0px;"></div>
                <div id="MembershipOptions">
                    <div id="OneMonth">
                        <div class="BuildersClubButton"><a id="ctl00_cphRoblox_GetMonthlyImageLink" href="PaymentMethods.aspx?ap=2"><img src="/images/BuyBCMonthly.png" style="border-width:0px;"></a></div>
                        <div class="Label"><a id="ctl00_cphRoblox_GetMonthlyHyperLink" href="PaymentMethods.aspx?ap=2">Get Monthly</a></div>
                    </div>
                    <div id="SixMonths">
                        <div class="BuildersClubButton"><a id="ctl00_cphRoblox_Get6MonthsImageLink" href="PaymentMethods.aspx?ap=3"><img src="/images/BuyBC6Months.png" style="border-width:0px;"></a></div>
                        <div class="Label"><a id="ctl00_cphRoblox_Get6MonthsHyperLink" href="PaymentMethods.aspx?ap=3">Get 6 Months</a></div>
                    </div>
                    <div id="TwelveMonths">
                        <div class="BuildersClubButton"><a id="ctl00_cphRoblox_Get12MonthsImageLink" href="PaymentMethods.aspx?ap=4"><img src="/images/BuyBC12Months.png" style="border-width:0px;"></a></div>
                        <div class="Label"><a id="ctl00_cphRoblox_Get12MonthsHyperLink" href="PaymentMethods.aspx?ap=4">Get 12 Months</a></div>
                    </div>
                </div>
                <div id="WhyJoin">
                    <h3>Why Join Builders Club?</h3>
                    <ul id="MembershipBenefits">
                        <li id="Benefit_MultiplePlaces">Create up to 10 places on a single account</li>
                        <li id="Benefit_RobuxAllowance">Earn a daily income of 15 RYBUX</li>
                        <li id="Benefit_SellContent">Sell your creations to others in the RYDENYTE Catalog</li>
                        <li id="Benefit_SuppressAds">Never see any outside ads on ryblox.xyz</li>
                        <li id="Benefit_ExclusiveHat">Receive the exclusive Builders Club construction hard hat</li>
                    </ul>
                    <p>For more information, read our <a id="ctl00_cphRoblox_FAQHyperLink" href="../Parents/BuildersClub.aspx">Builders Club FAQs</a>.</p>
                </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>