<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

// Validate ID
if (!isset($_GET["ID"]) || !is_numeric($_GET["ID"])) {
    die("Invalid item ID.");
}

$itemId = (int)$_GET["ID"];

$stmt = $db->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$itemId]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}

if ($item["creator_id"] != $_USER["id"]) {
    die("You do not own this item.");
}

if (isset($_POST["updateall"])) {

    $name = trim($_POST["iName"]);
    $desc = trim($_POST["iDesc"]);

    $forSale = isset($_POST["sellItem"]) ? 1 : 0;

    $useRobux = isset($_POST["use_robux"]);
    $useTix = isset($_POST["use_tix"]);

    $priceRobux = $useRobux ? (int)$_POST["price_robux"] : 0;
    $priceTix = $useTix ? (int)$_POST["price_tix"] : 0;

    $update = $db->prepare("
        UPDATE catalog
        SET
            name = ?,
            description = ?,
            for_sale = ?,
            price_robux = ?,
            price_tix = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $update->execute([
        $name,
        $desc,
        $forSale,
        $priceRobux,
        $priceTix,
        $itemId
    ]);

    header("Location: /Item.aspx?ID=" . $itemId);
    exit;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="EditItemContainer">
            <div id="EditItem">

            <h2>Edit Item</h2>

            <form method="post">
                <div id="ItemName">
                    <b>Name:</b><br>
                    <input name="iName" class="TextBox" maxlength="64" value="<?= htmlspecialchars($item["name"]) ?>">
                </div>

                <div id="ItemDescription">
                    <b>Description:</b><br>
                    <textarea name="iDesc" class="MultilineTextBox"></textarea>
                </div>

                <div id="SellThisItem">
                <fieldset>
                <legend>Sell this Item</legend>

                <div class="SellThisItemRow">
                <input type="checkbox" id="sellItemChk" name="sellItem" <?= $item["for_sale"] ? "checked" : "" ?>>
                <label>Sell this Item</label>
                </div>

                <div id="PricingPanel" style="display: none;">

                <div style="text-align:center;margin-bottom:10px;">
                <label>
                </label></div>

                <div id="PaidOptions">
                    <div style="text-align:center;margin-bottom:10px;">
                        <label>
                            <input type="checkbox" id="robuxChk" name="use_robux" <?= $item["price_robux"] > 0 ? "checked" : "" ?>>
                            Robux
                        </label>

                        <label>
                            <input type="checkbox" id="tixChk" name="use_tix" <?= $item["price_tix"] > 0 ? "checked" : "" ?>>
                            Tix
                        </label>
                    </div>

                    <div style="text-align:center;">
                        Robux:
                        <input type="number" id="robuxPrice" name="price_robux" value="<?= (int)$item["price_robux"] ?>" style="width:60px;" min="0">
                        Tix:
                        <input type="number" id="tixPrice" name="price_tix" value="<?= (int)$item["price_tix"] ?>" style="width:60px;" min="0" disabled="">
                        </div>
                    </div>

                </div>
                </fieldset>
                </div>

                <div class="Buttons">
                <input type="submit" name="updateall" class="Button" value="Update">
                <a href="/Item.aspx?ID=134" class="Button">Cancel</a>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
<script>
const sellItemChk = document.getElementById("sellItemChk");
const pricingPanel = document.getElementById("PricingPanel");

const robuxChk = document.getElementById("robuxChk");
const tixChk = document.getElementById("tixChk");

const robuxPrice = document.getElementById("robuxPrice");
const tixPrice = document.getElementById("tixPrice");

function updatePricingPanel() {
    pricingPanel.style.display = sellItemChk.checked
        ? "block"
        : "none";
}

function updateCurrencyInputs() {

    robuxPrice.disabled = !robuxChk.checked;
    tixPrice.disabled = !tixChk.checked;

    if (!robuxChk.checked) {
        robuxPrice.value = 0;
    }

    if (!tixChk.checked) {
        tixPrice.value = 0;
    }
}

sellItemChk.addEventListener("change", updatePricingPanel);

robuxChk.addEventListener("change", updateCurrencyInputs);
tixChk.addEventListener("change", updateCurrencyInputs);

updatePricingPanel();
updateCurrencyInputs();
</script>