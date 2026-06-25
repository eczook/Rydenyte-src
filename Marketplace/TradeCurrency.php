<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$userId = $_USER['id'];

$stmt = $db->prepare("SELECT tix, robux FROM users WHERE id = ?");
$stmt->execute([$userId]);
$balances = $stmt->fetch();

$tickets = (int)$balances['tix'];
$rybux = (int)$balances['robux'];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $amount = (int)$_POST['amount'];
    $currency = $_POST['currency'];

    if ($amount <= 0) {

        $message = "Invalid amount.";

    } else {
        if ($currency === "Tickets") {

            if ($amount < 10) {

                $message = "Minimum is 10 tix.";

            } elseif ($amount % 10 !== 0) {

                $message = "Tix must be divisible by 10.";

            } elseif ($tickets < $amount) {

                $message = "Not enough tix.";

            } else {

                $rybuxGain = floor($amount / 10);

                $newTickets = $tickets - $amount;
                $newRybux = $rybux + $rybuxGain;

                $update = $db->prepare("
                    UPDATE users
                    SET tix = ?, robux = ?
                    WHERE id = ?
                ");

                $update->execute([
                    $newTickets,
                    $newRybux,
                    $userId
                ]);

                $tickets = $newTickets;
                $rybux = $newRybux;

                $message = "Converted {$amount} Tix into {$rybuxGain} RYBUX.";
            }
        }

        if ($currency === "Robux") {

            if ($rybux < $amount) {

                $message = "Not enough RYBUX.";

            } else {
                $ticketGain = $amount * 10;

                $newRybux = $rybux - $amount;
                $newTickets = $tickets + $ticketGain;

                $update = $db->prepare("
                    UPDATE users
                    SET tix = ?, robux = ?
                    WHERE id = ?
                ");

                $update->execute([
                    $newTickets,
                    $newRybux,
                    $userId
                ]);

                $tickets = $newTickets;
                $rybux = $newRybux;

                $message = "Converted {$amount} RYBUX into {$ticketGain} Tix.";
                header("Location: /Marketplace/TradeCurrency.aspx");
            }
        }
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="TradeCurrencyContainer">
        <h2>Currency Exchange</h2>
        <div style="margin-bottom:5px; text-align:center;"><a href="TradeCurrency.aspx">Refresh</a></div>
        <div class="LeftColumn">
            <div id="CurrencyBidsPane">
                <div class="CurrencyBids">
                    <h4>Available Tickets</h4>
                    
                            <div class="CurrencyBid">
                                10 @ 1
                            </div>
                            </div>
                            </div>
                        </div>
                        <div class="CenterColumn">
                            <div id="ctl00_cphRoblox_CurrencyTradePane">
                                <div class="CurrencyTrade">

                                    <h4>Trade</h4>

                                    <?php if (!empty($message)): ?>
                                        <p style="color:red; text-align:center;">
                                            <?= htmlspecialchars($message) ?>
                                        </p>
                                    <?php endif; ?>
                                    <form method="POST">

                                        <div class="CurrencyTradeDetails">

                                            <div class="CurrencyTradeDetail">
                                                <div>What I'll give:</div>

                                                <input
                                                    type="number"
                                                    name="amount"
                                                    maxlength="9"
                                                    id="ctl00_cphRoblox_HaveAmountTextBox"
                                                    tabindex="1"
                                                    class="TradeBox"
                                                    autocomplete="off"
                                                    required
                                                >

                                                &nbsp;&nbsp;

                                                <select
                                                    name="currency"
                                                    id="ctl00_cphRoblox_HaveCurrencyDropDownList"
                                                    onchange="EstimateTrade()"
                                                >
                                                    <option value="Tickets">Tickets</option>
                                                    <option value="Robux">RYBUX</option>
                                                </select>
                                            </div>

                                            <div id="MarketOrder" class="CurrencyTradeDetail">
                                                <div>What I'll get:</div>

                                                <p id="EstimatedTrade" style="color: Red;">
                                                    Estimated Trade: ?
                                                </p>

                                                <p style="color: Red;">
                                                    * NOTE: Your money will be held for safe-keeping until either the trade executes or you cancel your position.
                                                </p>

                                                <p style="font-size: smaller; margin: 15px; text-align: left;">
                                                    Exchange Rate:
                                                    10 Tix = 1 RYBUX
                                                </p>
                                            </div>

                                            <div class="CurrencyTradeDetail">
                                                <input
                                                    type="submit"
                                                    value="Submit Trade"
                                                    id="ctl00_cphRoblox_SubmitTradeButton"
                                                    tabindex="4"
                                                >
                                            </div>

                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                        <div class="RightColumn">
                            <div id="CurrencyOffersPane">
                                

                <div class="CurrencyOffers">
                    <h4>Available RYBUX</h4>
                        <div class="CurrencyOffer">
                            1 @ 10
                        </div>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>