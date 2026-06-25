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

    $update = $db->prepare("
        UPDATE models
        SET
            name = ?,
            description = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $update->execute([
        $name,
        $desc,
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

            <h2>Edit Model</h2>

            <form method="post">
                <div id="ItemName">
                    <b>Name:</b><br>
                    <input name="iName" class="TextBox" maxlength="64" value="<?= htmlspecialchars($item["name"]) ?>">
                </div>

                <div id="ItemDescription">
                    <b>Description:</b><br>
                    <textarea name="iDesc" class="MultilineTextBox"></textarea>
                </div>
                <div class="Buttons">
                <input type="submit" name="updateall" class="Button" value="Update">
                <a href="/Model.aspx?ID=<?= $itemId ?>" class="Button">Cancel</a>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>