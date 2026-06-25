<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$stmt = $db->prepare("SELECT SUM(visits) FROM games WHERE creator_id = ?");
$stmt->execute([$_USER["id"]]);
$totalVisits = $stmt->fetchColumn();

if (empty($totalVisits)) {
    $totalVisits = 0;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE - Balance</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="MyAccountBalanceContainer">
            <h2>My Account Balance</h2>
            <div id="AboutRobux">
                <h3>What are RYBUX?</h3>
                <p>RYBUX are the principle currency of Rydenyia. Citizens in the Builders Club receive a daily allowance of RYBUX to help them live a comfortable life of leisure. For this and other benefits, consider joining the Builders Club!</p>
                <h3>What are Tickets?</h3>
                <p>Rydenyia Tickets are similar to tickets you win in an arcade. You play the game, get tickets, and are rewarded with fabulous prizes. Tickets are granted to citizens who are helping to expand and improve Robloxia. The primary way to get tickets is to make a cool place, and then get people to visit it. You can also get the daily login bonus, just by showing up!</p>
                <h3>Where do I buy things?</h3>
                <p>Browse the <a id="ctl00_cphRoblox_CatalogHyperLink" href="../Catalog.aspx">RYDENYTE Catalog</a></p>
            </div>
            <div id="Earnings">
                <h3>Earnings</h3>
                <div>
                    <div class="Label"></div>
                    <div class="Field"><img id="ctl00_cphRoblox_RobuxIcon" src="../images/Robux.png" alt="Rybux" style="border-width:0px;" /></div>
                    <div class="Field"><img id="ctl00_cphRoblox_TicketsIcon" src="../images/Tickets.png" alt="Tickets" style="border-width:0px;" /></div>
                </div>
                <div class="Earnings_Period">
                    <h4>Past Day</h4>
                    <div class="Earnings_LoginAward">
                        <div class="Label">Login Award</div>
                        <div class="Field"></div>
                        <div class="Field">10</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastDay_PlaceTrafficAward" class="Earnings_PlaceTrafficAward">
                        <div class="Label">Place Traffic Award</div>
                        <div class="Field"></div>
                        <div class="Field"><?= number_format($totalVisits) ?></div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastDay_SaleOfGoods" class="Earnings_SaleOfGoods">
                        <div class="Label">Sale of Goods</div>
                        <div class="Field"></div>
                        <div class="Field">10</div>
                    </div>
                    <div class="Earnings_PeriodTotal">
                        <div class="Label">Total:</div>
                        <div class="Field">15</div>
                        <div class="Field">206</div>
                    </div>
                </div>
                <div class="Earnings_Period">
                    <h4>Past Week</h4>
                    <div class="Earnings_LoginAward">
                        <div class="Label">Login Award</div>
                        <div class="Field"></div>
                        <div class="Field">60</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastWeek_PlaceTrafficAward" class="Earnings_PlaceTrafficAward">
                        <div class="Label">Place Traffic Award</div>
                        <div class="Field"></div>
                        <div class="Field"><?= number_format($totalVisits) ?></div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastWeek_SaleOfGoods" class="Earnings_SaleOfGoods">
                        <div class="Label">Sale of Goods</div>
                        <div class="Field"></div>
                        <div class="Field">20</div>
                    </div>
                    <div class="Earnings_PeriodTotal">
                        <div class="Label">Total:</div>
                        <div class="Field">90</div>
                        <div class="Field"><?= number_format($totalVisits) ?></div>
                    </div>
                </div>
                <div class="Earnings_Period">
                    <h4>Past Month</h4>
                    <div class="Earnings_LoginAward">
                        <div class="Label">Login Award</div>
                        <div class="Field"></div>
                        <div class="Field">260</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastMonth_PlaceTrafficAward" class="Earnings_PlaceTrafficAward">
                        <div class="Label">Place Traffic Award</div>
                        <div class="Field"></div>
                        <div class="Field">10,033</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_PastMonth_SaleOfGoods" class="Earnings_SaleOfGoods">
                        <div class="Label">Sale of Goods</div>
                        <div class="Field">1</div>
                        <div class="Field">59</div>
                    </div>
                    <div class="Earnings_PeriodTotal">
                        <div class="Label">Total:</div>
                        <div class="Field">391</div>
                        <div class="Field">10,352</div>
                    </div>
                </div>
                <div class="Earnings_Period">
                    <h4>All Time</h4>
                    <div class="Earnings_LoginAward">
                        <div class="Label">Login Award</div>
                        <div class="Field">290</div>
                        <div class="Field">1,780</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_AllTime_PlaceTrafficAward" class="Earnings_PlaceTrafficAward">
                        <div class="Label">Place Traffic Award</div>
                        <div class="Field">6,328</div>
                        <div class="Field">43,163</div>
                    </div>
                    <div id="ctl00_cphRoblox_Earnings_AllTime_SaleOfGoods" class="Earnings_SaleOfGoods">
                        <div class="Label">Sale of Goods</div>
                        <div class="Field">7</div>
                        <div class="Field">361</div>
                    </div>
                    <div class="Earnings_PeriodTotal">
                        <div class="Label">Total:</div>
                        <div class="Field">6,575</div>
                        <div class="Field">45,354</div>
                    </div>
                </div>
            </div>
        </div>    
    </div>
<div style="clear: both;"></div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>