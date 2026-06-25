<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$brickColors = [
    "1" => "#F2F3F2",
    "208" => "#E5E4DE",
    "194" => "#A3A2A4",
    "199" => "#635F61",
    "26" => "#1B2A34",
    "21" => "#C4281B",
    "24" => "#F5CD2F",
    "226" => "#FDEA8C",
    "23" => "#0D69AB",
    "107" => "#008F9B",
    "102" => "#6E99C9",
    "11" => "#80BBDB",
    "45" => "#B4D2E3",
    "135" => "#74869C",
    "106" => "#DA8540",
    "105" => "#E29B3F",
    "141" => "#27462C",
    "28" => "#287F46",
    "37" => "#4B974A",
    "119" => "#A4BD46",
    "29" => "#A1C48B",
    "151" => "#789082",
    "38" => "#A05F34",
    "192" => "#694027",
    "104" => "#6B327B",
    "9" => "#E8BAC7",
    "101" => "#DA8679",
    "5" => "#D7C599",
    "153" => "#957976",
    "217" => "#7C5C45",
    "18" => "#CC8E69",
    "125" => "#EAB891"
];

$bodyColorIds = explode(";", $_USER["bodycolors"]);

$userBodyColors = [
    "RightLeg" => $brickColors[$bodyColorIds[5]] ?? "#FFFFFF",
    "Head" => $brickColors[$bodyColorIds[0]] ?? "#FFFFFF",
    "Torso" => $brickColors[$bodyColorIds[2]] ?? "#FFFFFF",
    "LeftArm" => $brickColors[$bodyColorIds[3]] ?? "#FFFFFF",
    "RightArm" => $brickColors[$bodyColorIds[1]] ?? "#FFFFFF",
    "LeftLeg" => $brickColors[$bodyColorIds[4]] ?? "#FFFFFF"
];

$uid = $_USER["id"];
$type = isset($_GET["type"]) ? intval($_GET["type"]) : 8;

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$countStmt = $db->prepare("
    SELECT COUNT(*)
    FROM owned_items o
    JOIN catalog i ON i.id = o.item_id
    WHERE o.user_id = ? AND i.category = ?
");
$countStmt->execute([$uid, $type]);

$totalItems = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalItems / $perPage));

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

$wearingIds = array_column($wearing, "id");

function selectedCategory($currentType, $categoryType) {
    return $currentType == $categoryType
        ? 'AttireCategorySelector AttireCategorySelector_Selected'
        : 'AttireCategorySelector';
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>My Character - RYDENYTE</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <style>
        h4 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            border-bottom: black 1px solid;
            text-align: center;
            <?php if ($theme === "dark"): ?>
            background-color: #333333;
            border-bottom: solid 1px #ffffff;
            color: #ffffff;
            <?php else: ?>
            background-color: #ccc;
            color: #333;
            <?php endif; ?>
            border-bottom: solid 1px #000;
        }

        .CharacterViewer2 {
            float: right;
            width: 354px;
        }
        .spinner {
            position: absolute;
            width: 30px;
            height: 30px;
            pointer-events: none;
        }
         .popupControl {
            position: absolute;
            background: white;
            border: 1px solid black;
            padding: 5px;
            z-index: 1000;
            visibility: hidden;
        }
       .ColorPickerItem {
            cursor: pointer;
            border: 1px solid #ccc;
            display: inline-block;
            height: 32px;
            width: 32px;
        }
        .ColorPickerItem:hover {
            border: 2px solid #000;
        }
        .disabled {
            color: gray;
            pointer-events: none;
        }
        .FooterPager {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
        }
        .FooterPager a {
            margin: 0 2px;
            text-decoration: none;
        }
        .FooterPager span {
            margin: 0 2px;
        }

        .AttireContent {
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        
        .Asset {
            width: 110px;
            margin: 10px;
            display: inline-block;
            vertical-align: top;
        }
        
        .AssetThumbnail {
            text-align: center;
        }
        
        .AssetDetails {
            text-align: center;
            font-size: 11px;
        }
        
        .DeleteButtonOverlay {
            display: block;
            text-align: center;
            margin-top: 5px;
        }
        </style>
        <div class="MyRobloxContainer">
            <div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_CustomizeCharacterUpdatePanel"></div>
            <div class="CharacterViewer2">
                <div style="border: black solid thin;">
                    <h4>My Character</h4>
                    <div class="StandardBox">
                        <div style="position: relative;">
                            <img id="spinner" class="spinner" style="display: none;" src="/images/ProgressIndicator2.gif">
                            <a title="<?= htmlspecialchars($_USER["username"]) ?>" onclick="return false" style="display:inline-block;height:352px;width:352px;">
                                <img id="avatarthumb" src="/Thumbs/Avatar.ashx?userId=<?= $_USER["id"] ?>" width="352" height="352" border="0" alt="<?= htmlspecialchars($_USER["username"]) ?>">
                            </a>
                            <p style="font-family: Verdana, Geneva, Tahoma, sans-serif;text-align:center;margin:0 auto;font-size:11px">Something wrong with your avatar? <a href="javascript:redraw();">Click here to re-draw it!</a></p>
                        </div>
                    </div>
                </div>
                <br>
                <script>
                var brickColors = {"1":"#F2F3F2","208":"#E5E4DE","194":"#A3A2A4","199":"#635F61","26":"#1B2A34","21":"#C4281B","24":"#F5CD2F","226":"#FDEA8C","23":"#0D69AB","107":"#008F9B","102":"#6E99C9","11":"#80BBDB","45":"#B4D2E3","135":"#74869C","106":"#DA8540","105":"#E29B3F","141":"#27462C","28":"#287F46","37":"#4B974A","119":"#A4BD46","29":"#A1C48B","151":"#789082","38":"#A05F34","192":"#694027","104":"#6B327B","9":"#E8BAC7","101":"#DA8679","5":"#D7C599","153":"#957976","217":"#7C5C45","18":"#CC8E69","125":"#EAB891"};
                </script>
                <center>
                <div style="border: black solid thin;">
                    <h4>Poses</h4>
                    <div id="PoseList" style="padding: 10px;">
                        <button class="Button" data-pose="normal"  onclick="setPose('normal')">Normal</button>
                        <button class="Button" data-pose="walking" onclick="setPose('walking')">Walking</button>
                        <button class="Button" data-pose="sitting" onclick="setPose('sitting')">Sitting</button>
                        <button class="Button" data-pose="overlord" onclick="setPose('overlord')">Overlord</button>
                        <div style="clear: both;height:5px"></div>
                        <button class="Button" data-pose="zombie" onclick="setPose('zombie')">Zombie</button>
                        <button class="Button" data-pose="crime" onclick="setPose('crime')">Crime</button>
                        <button class="Button" data-pose="pistol" onclick="setPose('pistol')">Bazooka</button>
                    </div>
                </div>
                <br>
                </center>
                <center>
                    <div style="border: black solid thin;">
                        <h4>Color Chooser</h4>
                        <div class="StandardBox">
                            <div>
                                <p>Click a body part to change its color:</p>
                                <div class="ColorChooserFrame" style="height:236px;width:176px;text-align:center;">
                                    <div style="position: relative; margin: 11px 11px; height: 200px;">
                                        <div style="position: absolute; left: 120px; top: 44px; cursor: pointer">
                                            <div id="LeftArmSelector" style="height:72px;width:32px; background-color: <?= $userBodyColors["LeftArm"] ?>; border: 1px solid #ccc;" onclick="togglepopup4();"></div>
                                        </div>
                                        <div style="position: absolute; left: 40px; top: 44px; cursor: pointer">
                                            <div id="TorsoSelector" style="height:72px;width:72px; background-color: <?= $userBodyColors["Torso"] ?>; border: 1px solid #ccc;" onclick="togglepopup3();"></div>
                                        </div>
                                        <div style="position: absolute; left: 0px; top: 44px; cursor: pointer">
                                            <div id="RightArmSelector" style="height:72px;width:32px; background-color: <?= $userBodyColors["RightArm"] ?>; border: 1px solid #ccc;" onclick="togglepopup5();"></div>
                                        </div>
                                        <div style="position: absolute; left: 58px; top: 0px; cursor: pointer">
                                            <div id="HeadSelector" style="height:36px;width:36px; background-color: <?= $userBodyColors["Head"] ?>; border: 1px solid #ccc;" onclick="togglepopup2();"></div>
                                        </div>
                                        <div style="position: absolute; left: 40px; top: 124px; cursor: pointer">
                                            <div id="RightLegSelector" style="height:72px;width:32px; background-color: <?= $userBodyColors["RightLeg"] ?>; border: 1px solid #ccc;" onclick="togglepopup1();"></div>
                                        </div>
                                        <div style="position: absolute; left: 80px; top: 124px; cursor: pointer">
                                            <div id="LeftLegSelector" style="height:72px;width:32px; background-color: <?= $userBodyColors["LeftLeg"] ?>; border: 1px solid #ccc;" onclick="togglepopup6();"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </center>
            </div>
            <div id="CustomizeCharacterContainer">
            <div class="AttireChooser" style="border: 1px solid;">
                <div class="AttireChooser">
                    <h4>My Wardrobe</h4>
                    <div class="HeaderPager">
                        <div class="AttireCategory">
                            <center>
                                <a id="cat_tshirts"
                                class="<?= selectedCategory($type, 2) ?>"
                                href="?type=2&page=1">
                                T-Shirts
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_shirts"
                                class="<?= selectedCategory($type, 11) ?>"
                                href="?type=11&page=1">
                                Shirts
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_pants"
                                class="<?= selectedCategory($type, 12) ?>"
                                href="?type=12&page=1">
                                Pants
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_hats"
                                class="<?= selectedCategory($type, 8) ?>"
                                href="?type=8&page=1">
                                Hats
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_heads"
                                class="<?= selectedCategory($type, 17) ?>"
                                href="?type=17&page=1">
                                Heads
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_faces"
                                class="<?= selectedCategory($type, 18) ?>"
                                href="?type=18&page=1">
                                Faces
                                </a>
                                &nbsp;|&nbsp;
                                <a id="cat_faces"
                                class="<?= selectedCategory($type, 19) ?>"
                                href="?type=19&page=1">
                                Gears
                                </a>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <br>
                                <a href="../Catalog.aspx?m=ForSale&c=<?= $type ?>&d=All">
                                    Shop
                                </a>
                                &nbsp;
                                <a href="ContentBuilder.aspx?ContentType=2">
                                    Create
                                </a>
                            </center>
                        </div>

                        
                        <div class="AttireContent" id="wardrobeItems">
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
                        </div>
                                                    </div>
                                                    <div class="FooterPager" style="border-top:1px solid">

<?php if ($page > 1): ?>
    <a href="?type=<?= $type ?>&page=1">First Previous</a>
<?php else: ?>
    <span class="disabled">First</span>
    <span class="disabled">Previous</span>
<?php endif; ?>

&nbsp;&nbsp;

<?php
$start = max(1, $page - 2);
$end = min($totalPages, $page + 2);

for ($i = $start; $i <= $end; $i++):
?>
    <?php if ($i == $page): ?>
        <?= $i ?>
    <?php else: ?>
        <a href="?type=<?= $type ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endif; ?>
<?php endfor; ?>

&nbsp;&nbsp;

<?php if ($page < $totalPages): ?>
    <a href="?type=<?= $type ?>&page=<?= $page + 1 ?>">Next</a>
    <a href="?type=<?= $type ?>&page=<?= $totalPages ?>">Last</a>
<?php else: ?>
    <span class="disabled">Next</span>
    <span class="disabled">Last</span>
<?php endif; ?>

</div>
                                            </div>
                </div>
                <div class="AttireChooser" style="margin-top: 8px;border: 1px solid">
                    <h4>Currently Wearing</h4>
                    <div class="HeaderPager">
                        <div class="AttireContent" id="wearingItems">
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
                        </div>
                    </div>
                </div>
            </div>
            <br clear="all">
            <div id="PopupRightLeg" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
                <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                        <div class="ColorPickerItem" onclick="changebcolor('1', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                                </div>
            </div>
            <div id="PopupHead" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('2', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                            </div>
        </div>
        <div id="PopupTorso" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('3', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                            </div>
        </div>
        <div id="PopupLeftArm" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('4', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                            </div>
        </div>
        <div id="PopupRightArm" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('5', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                            </div>
        </div>
        <div id="PopupLeftLeg" class="popupControl" style="top: 435px; right: 165px; visibility: hidden;">
            <div style="display: flex; flex-wrap: wrap; gap: 4px; width: 280px; background: white; padding: 10px;">
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '1');" style="background-color: #F2F3F2; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '208');" style="background-color: #E5E4DE; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '194');" style="background-color: #A3A2A4; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '199');" style="background-color: #635F61; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '26');" style="background-color: #1B2A34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '21');" style="background-color: #C4281B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '24');" style="background-color: #F5CD2F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '226');" style="background-color: #FDEA8C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '23');" style="background-color: #0D69AB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '107');" style="background-color: #008F9B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '102');" style="background-color: #6E99C9; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '11');" style="background-color: #80BBDB; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '45');" style="background-color: #B4D2E3; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '135');" style="background-color: #74869C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '106');" style="background-color: #DA8540; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '105');" style="background-color: #E29B3F; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '141');" style="background-color: #27462C; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '28');" style="background-color: #287F46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '37');" style="background-color: #4B974A; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '119');" style="background-color: #A4BD46; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '29');" style="background-color: #A1C48B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '151');" style="background-color: #789082; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '38');" style="background-color: #A05F34; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '192');" style="background-color: #694027; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '104');" style="background-color: #6B327B; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '9');" style="background-color: #E8BAC7; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '101');" style="background-color: #DA8679; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '5');" style="background-color: #D7C599; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '153');" style="background-color: #957976; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '217');" style="background-color: #7C5C45; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '18');" style="background-color: #CC8E69; width: 32px; height: 32px;"></div>
                                    <div class="ColorPickerItem" onclick="changebcolor('6', '125');" style="background-color: #EAB891; width: 32px; height: 32px;"></div>
                            </div>
        </div>
        </div>
        </div>
        <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
    </div>
<script type="text/javascript">
var currentPopup = null;

function createXHR() {

    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    }

    return new ActiveXObject(
        "Microsoft.XMLHTTP"
    );
}

function refreshWardrobe() {

    get(window.location.href, function(html) {

        var temp = document.createElement("div");
        temp.innerHTML = html;

        document.getElementById("CustomizeCharacterContainer").innerHTML =
            temp.querySelector("#CustomizeCharacterContainer").innerHTML;
    });
}

function post(url, body, callback) {

    var xhr = createXHR();

    xhr.onreadystatechange = function () {

        if (
            xhr.readyState == 4 &&
            xhr.status == 200
        ) {

            callback(xhr.responseText);

        }
    };

    xhr.open("POST", url, true);

    xhr.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
    );

    xhr.send(body);
}

function get(url, callback) {

    var xhr = createXHR();

    xhr.onreadystatechange = function () {

        if (
            xhr.readyState == 4 &&
            xhr.status == 200
        ) {

            callback(xhr.responseText);

        }
    };

    xhr.open("GET", url, true);

    xhr.send(null);
}

function updatePoseButtons(pose) {
    document.querySelectorAll("#PoseList .Button").forEach(btn => {
        btn.classList.toggle("Buttonselected", btn.dataset.pose === pose);
    });
}

function setPose(pose) {
    post(
        "/My/setpose.ashx",
        "pose=" + encodeURIComponent(pose),
        function (response) {
            let data;

            try {
                data = JSON.parse(response);
            } catch (e) {
                return;
            }

            if (data.success) {
                updatePoseButtons(pose);
                redraw();
            }
        }
    );
}

function changebcolor(part, color) {

    post(
        "/My/changebodycolor.ashx",
        "part=" +
        encodeURIComponent(part) +
        "&color=" +
        encodeURIComponent(color),

        function (response) {

            var data;

            try {

                data = eval("(" + response + ")");

            }
            catch (e) {

                return;

            }

            if (data.success) {

                switch (part) {

                    case "1":

                        document.getElementById(
                            "RightLegSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;

                    case "2":

                        document.getElementById(
                            "HeadSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;

                    case "3":

                        document.getElementById(
                            "TorsoSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;

                    case "4":

                        document.getElementById(
                            "LeftArmSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;

                    case "5":

                        document.getElementById(
                            "RightArmSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;

                    case "6":

                        document.getElementById(
                            "LeftLegSelector"
                        ).style.backgroundColor =
                            brickColors[color];

                        break;
                }

                redraw();
            }
        }
    );
}

function hideAllPopups() {

    var popups = [
        "PopupRightLeg",
        "PopupHead",
        "PopupTorso",
        "PopupLeftArm",
        "PopupRightArm",
        "PopupLeftLeg"
    ];

    var i;

    for (
        i = 0;
        i < popups.length;
        i++
    ) {

        document.getElementById(
            popups[i]
        ).style.visibility = "hidden";
    }

    currentPopup = null;
}

function redraw() {
    var userId = "<?= $_USER["id"] ?>";

    var spinner = document.getElementById("spinner");
    var avatar = document.getElementById("avatarthumb");

    spinner.style.display = "block";
    avatar.src = "/images/unavail.png";

    get(
        "/Thumbs/Renders/renderCharacter.ashx?userId=" + userId,
        function () {

            avatar.src =
                "/Thumbs/Avatar.ashx?userId=" +
                userId +
                "&t=" +
                Date.now();

            spinner.style.display = "none";
        }
    );
}

function wearItem(itemId) {

    post(
        "/My/wearitem.ashx",
        "itemId=" +
        encodeURIComponent(itemId),

        function () {
            refreshWardrobe();
            redraw();
        }
    );
}

function removeItem(itemId) {

    post(
        "/My/removeitem.ashx",
        "itemId=" +
        encodeURIComponent(itemId),

        function () {
            refreshWardrobe();
            redraw();
        }
    );
}

function togglePopup(id) {

    var popup =
        document.getElementById(id);

    if (
        currentPopup &&
        currentPopup != popup
    ) {

        currentPopup.style.visibility =
            "hidden";
    }

    if (
        popup.style.visibility ==
        "visible"
    ) {

        popup.style.visibility =
            "hidden";

        currentPopup = null;
    }
    else {

        popup.style.visibility =
            "visible";

        currentPopup = popup;
    }
}

function togglepopup1() {
    togglePopup("PopupRightLeg");
}

function togglepopup2() {
    togglePopup("PopupHead");
}

function togglepopup3() {
    togglePopup("PopupTorso");
}

function togglepopup4() {
    togglePopup("PopupLeftArm");
}

function togglepopup5() {
    togglePopup("PopupRightArm");
}

function togglepopup6() {
    togglePopup("PopupLeftLeg");
}

function addEvent(element,eventName,handler) {
    if ( element.addEventListener ) { element.addEventListener(eventName,handler,false); }
    else if ( element.attachEvent ) {
        element.attachEvent(
            "on" + eventName,
            handler
        );
    }
}

addEvent(document, "click", function (e) {
    e = e || window.event;
    var target = e.target || e.srcElement;

    if (target && target.id && (
        target.id.includes("Selector")
    )) {
        return;
    }

    while (target) {
        if (target.className &&
            target.className.indexOf("popupControl") > -1) {
            return;
        }
        target = target.parentNode;
    }

    hideAllPopups();
});
console.log("<?= $_USER["pose"] ?>");
updatePoseButtons("<?= $_USER["pose"] ?>");
</script>
</div>