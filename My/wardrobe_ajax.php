 <?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$uid = $_USER["id"];
$type = isset($_GET["type"]) ? intval($_GET["type"]) : 8;

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT i.id, i.name, i.category, i.creator_id
    FROM owned_items o
    JOIN catalog i ON i.id = o.item_id
    WHERE o.user_id = ?
      AND i.category = ?
      AND NOT EXISTS (
          SELECT 1
          FROM wearing w
          WHERE w.user_id = ?
            AND w.item_id = i.id
      )
    LIMIT ? OFFSET ?
");

$stmt->bindValue(1, $uid, PDO::PARAM_INT);
$stmt->bindValue(2, $type, PDO::PARAM_INT);
$stmt->bindValue(3, $uid, PDO::PARAM_INT);
$stmt->bindValue(4, $perPage, PDO::PARAM_INT);
$stmt->bindValue(5, $offset, PDO::PARAM_INT);

$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT i.id, i.name, i.category, i.creator_id
    FROM wearing w
    JOIN catalog i ON i.id = w.item_id
    WHERE w.user_id = ?
");

$stmt->execute([$uid]);

$wearing = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php foreach ($items as $item): ?>
<?php $creator = getCreator($db, $item["creator_id"]); ?>
                            <div class="Asset">
                                <div class="AssetThumbnail">
                                    <a style="display:inline-block;height:110px;width:110px;cursor:pointer;">
                                        <img src="/Thumbs/Item.ashx?id=<?= $item["id"] ?>&x=110&y=110">
                                    </a>

                                    <a class="DeleteButtonOverlay"
                                    href="javascript:void(0);"
                                    style="top: -4px"
                                    onclick="wearItem(<?= $item["id"] ?>, <?= $item["category"] ?>)">
                                    [ wear ]
                                    </a>
                                </div>

                                <div class="AssetDetails">
                                    <div class="AssetName">
                                        <a href="/item.aspx?id=<?= $item["id"] ?>">
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </a>
                                    </div>
                                    <div class="AssetCreator">
                                            <span class="Label">Creator:</span> 
                                            <span class="Detail">
                                                <a href="/User.aspx?ID=1"><?= htmlspecialchars($creator["username"]) ?></a>
                                            </span>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>

<?php
$wardrobeHtml = ob_get_clean();

ob_start();
?>

<?php foreach ($wearing as $item): ?>
<?php $creator = getCreator($db, $item["creator_id"]); ?>
                            <div class="Asset">
                                <div class="AssetThumbnail">
                                    <a title="click to remove" style="display:inline-block;height:110px;width:110px;cursor:pointer;">
                                        <img style="height:110px;width:110px" src="/Thumbs/Item.aspx?id=<?= $item["id"] ?>" border="0" alt="click to remove">
                                    </a>
                                    <a title="click to remove" class="DeleteButtonOverlay" href="javascript:void(0);" style="top: -4px" onclick="removeItem(<?= $item["id"] ?>)">[ remove ]</a>
                                </div>

                                <div class="AssetDetails">
                                    <div class="AssetName">
                                        <?= htmlspecialchars($item["name"]) ?>
                                    </div>
                                    <div class="AssetCreator">
                                                <span class="Label">Creator:</span> 
                                                <span class="Detail">
                                                    <a href="/User.aspx?ID=1"><?= htmlspecialchars($creator["username"]) ?></a>
                                                </span>
                                        </div>
                                </div>
                            </div>
<?php endforeach; ?>

<?php
$wearingHtml = ob_get_clean();

echo json_encode([
    "wardrobe" => $wardrobeHtml,
    "wearing" => $wearingHtml
]);