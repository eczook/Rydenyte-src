<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php"; 

$itemid = $_GET["ID"] ?? $_GET["id"];

if (empty($itemid)) {
    http_response_code(404);
    header("Location: /Error/DoesntExist.aspx");
    exit;
}

$stmt = $db->prepare("SELECT * FROM catalog WHERE id = ?");
$stmt->execute([$itemid]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    http_response_code(404);
    header("Location: /Error/DoesntExist.aspx");
    exit;
}
$creator = getCreator($db, $item["creator_id"]);
$creatorName = $creator["username"] ?? "Unknown";

$userId = $_USER["id"];
$ownStmt = $db->prepare("
    SELECT 1 
    FROM owned_items 
    WHERE user_id = ? AND item_id = ? 
    LIMIT 1
");
$ownStmt->execute([$userId, $item["id"]]);

$ownsItem = $ownStmt->fetchColumn() ? true : false;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$commentStmt = $db->prepare("
    SELECT c.*, u.username
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.item_id = ? AND c.type = 'item'
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
");
$commentStmt->bindValue(1, $item["id"], PDO::PARAM_INT);
$commentStmt->bindValue(2, $limit, PDO::PARAM_INT);
$commentStmt->bindValue(3, $offset, PDO::PARAM_INT);
$commentStmt->execute();

$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE item_id = ? AND type = 'item'");
$countStmt->execute([$item["id"]]);
$totalComments = (int)$countStmt->fetchColumn();

$totalPages = ceil($totalComments / $limit);

$map = [
    2 => "T-Shirt",
    13 => "Decal",
    17 => "Head",
    18 => "Face",
    19 => "Gear",
    8 => "Hat",
    11 => "Shirt",
    12 => "Pants",
];

$title = $map[$item["category"]];
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<link rel="stylesheet" href="/CSS/Tabs.css">
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="ItemContainer">
    <div id="Item">
        <h2><?= htmlspecialchars($item["name"]) ?></h2>
        
        <div id="Details">
            <div id="Thumbnail">
                <style>
                        .tshirt {
                            background: url("/images/tshirt.png") center/contain no-repeat;
                            width: 250px;
                            height: 251px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                    </style>
                <a id="ctl00_cphRoblox_AssetThumbnailImage" class="<?= (int)$item['category'] === 2 ? ' tshirt' : '' ?>" disabled="disabled" title="<?= htmlspecialchars($item["name"]) ?>" onclick="return false" style="display:inline-block;">
                    <?php if ($item["category"] === 2): ?>
                    <img src="/Thumbs/Item.ashx?id=<?= $item["id"] ?>&x=170&y=171" style="margin-left:40px;margin-top:45px" alt="<?= htmlspecialchars($item["name"]) ?>" border="0">
                    <?php else: ?>
                    <img src="/Thumbs/Item.ashx?id=<?= $item["id"] ?>&x=250&y=251" alt="<?= htmlspecialchars($item["name"]) ?>" border="0">
                    <?php endif; ?>
                </a>
                <div id="Actions">
		                    <a id="ctl00_cphRoblox_FavoriteThisButton" disabled="disabled">Favorite</a>
		                </div>
            </div>
            <div id="Summary">
                <h3>ROBLOX <?= htmlspecialchars($title) ?></h3>
                <?php if (isset($_USER) && $_USER["role"] === "Admin"): ?>
                    <a class="Button" href="/Thumbs/Renders/renderItem.ashx?itemId=<?= $item["id"] ?>">Re-render</a>
                    <form method="POST"
                        action="/Data/deleteitem.ashx"
                        style="display:inline;">
                        <input type="hidden" name="item_id" value="<?= (int)$item["id"] ?>">

                        <button type="submit"
                                class="Button"
                                onclick="return confirm('Delete this item?');">
                            Delete
                        </button>
                    </form>
                <?php endif; ?>
                <?php if ($item["for_sale"] !== 0): ?>
                <?php if (!$ownsItem): ?>
                <div id="ctl00_cphRoblox_TicketsPurchasePanel">

                    <?php if ($item["price_tix"] == 0 && $item["price_robux"] == 0): ?>
                        <div id="RobuxPurchase">
                            <div id="PriceInRobux">Free</div>

                            <div id="BuyWithRobux">
                                <a class="Button" href="/Data/buyitem.aspx?id=<?= $item["id"] ?>&currency=free">
                                    Take One!
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($item["price_tix"] > 0): ?>
                        <div id="TicketsPurchase">
                            <div id="PriceInTickets">Tx: <?= $item["price_tix"] ?></div>

                            <div id="BuyWithTickets">
                                <a class="Button" href="/Data/buyitem.aspx?id=<?= $item["id"] ?>&currency=tix">
                                    Buy with Tx
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($item["price_robux"] > 0): ?>
                        <div id="RobuxPurchase">
                            <div id="PriceInRobux">R$: <?= $item["price_robux"] ?></div>

                            <div id="BuyWithRobux">
                                <a class="Button" href="/Data/buyitem.aspx?id=<?= $item["id"] ?>&currency=robux">
                                    Buy with R$
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
                <?php endif; ?>
                <?php endif; ?>

                <div id="Creator" class="Creator">
                   <div class="Avatar">
                        <a id="ctl00_cphRoblox_AvatarImage" title=" " href="/User.aspx?ID=<?= $creator["id"] ?>" style="display:inline-block;cursor:pointer;">
                            <img style="height:100px;" src="/Thumbs/Avatar.ashx?userId=<?= $creator["id"] ?>" border="0" alt="<?= htmlspecialchars($creatorName) ?>" blankurl="/images/unavail.png">
                        </a>
                    </div>
                    Creator: <a id="ctl00_cphRoblox_CreatorHyperLink" href="/User.aspx?ID=<?= $creator["id"] ?>"><?= htmlspecialchars($creatorName) ?></a>
                </div>
                
                <div id="LastUpdate">Updated: <?= timeAgo($item["updated_at"]) ?></div>
                <div id="Favorited">Favorited: <?= number_format($item["favorites"]) ?> times</div>
                                <div id="ctl00_cphRoblox_DescriptionPanel">
                    <?php if (!empty($item["description"])): ?>
                    <div id="DescriptionLabel">Description:</div>
                    <div id="Description"><?= htmlspecialchars($item["description"]) ?></div>
                    <?php endif; ?>
                </div>
                                <div id="ReportAbuse">
                    <div id="ctl00_cphRoblox_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
                        <span class="AbuseIcon">
                            <a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseIconHyperLink" href="AbuseReport/AssetVersion.aspx?ID=85&amp;ReturnUrl=http%3a%2f%2fwww.y.cloudpub.ru%2fItem.aspx%3fID%3d85">
                                <img src="/images/abuse.PNG" alt="Report Abuse" border="0">
                            </a>
                        </span>
                        <span class="AbuseButton">
                            <a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseTextHyperLink" href="AbuseReport/AssetVersion.aspx?ID=85&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fItem.aspx%3fID%85">
                                Report Abuse
                            </a>
                    </span>
                    </div>
                </div>
                
                
                <div style="clear: both;"></div>
               
                
            </div>
            <?php if ($item["creator_id"] === $_USER["id"]): ?>
                    <div id="Configuration" style="border-top: dashed 1px #555;">
                        <a href="/My/Item.aspx?ID=<?= $item["id"] ?>">Configure this <?= htmlspecialchars($title) ?></a>
                    </div>
                    <div id="Configuration" style="border-top: dashed 1px #555;margin-top:-1px">
                        <a href="/My/Ads/CreateAd.aspx?targetId=<?= $item["id"] ?>&type=item">Advertise this <?= htmlspecialchars($title) ?></a>
                    </div>
                    <?php endif; ?>
            <?php if ($ownsItem): ?>
            <div id="ctl00_cphRoblox_ItemOwnershipPanel" style="margin-top: 10px;">
	
				<div id="Ownership">
					
					<a id="ctl00_cphRoblox_RemoveFromInventoryButton" class="Button" href="">Delete from My Stuff</a>
				</div>
			
            </div>
            <?php endif; ?>
        
                         <div style="clear: both;"></div>
            <br>
        </div>
        <br>
        <br>
        <br>
        <br>
        
        <div style="width: 703px;margin-left:10px;margin-bottom:10px">
        <div class="ajax__tab_xp ajax__tab_container ajax__tab_default" id="TabbedInfo">

        <div id="TabbedInfo_header" class="ajax__tab_header" style="height:21px;">



        <span id="tab22" class="ajax__tab ajax__tab_active">
        <span class="ajax__tab_outer">
        <span class="ajax__tab_inner">
        <a class="ajax__tab_tab ajax__tab" id="__tab_TabbedInfo_CommentaryTab" onclick="activateTab('tab22','tab11'); getComments(1, 80);" style="cursor: pointer;">
        <h3>Commentary</h3>
        </a>
        </span>
        </span>
        </span>

        </div>

        <div id="TabbedInfo_body" class="ajax__tab_body">
        <div id="TabbedInfo_CommentTab">
            <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab" style="display:block;">
			
			                <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsUpdatePanel">
				
                <div class="CommentsContainer">
                    <h3>Comments (<?= number_format($totalComments) ?>)</h3>
                    <div class="HeaderPager">
                        <span>
                            Page <?= $page ?> of <?= $totalPages ?>
                        </span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?ID=<?= $item["id"] ?>&page=<?= $page + 1 ?>">Next<span class="NavigationIndicators">&gt;&gt;</span></a>
                        <?php endif; ?>
                    </div>
		            <div class="Comments">
                    <?php if (empty($comments)): ?>
                        <p style="padding:0px; text-align:left; margin-left:10px;">No comments yet. Be the first!</p>

                    <?php else: ?>

                        <?php foreach ($comments as $index => $comment): ?>
                            <div class="<?= $index % 2 === 0 ? 'Comment' : 'AlternateComment' ?>">
                                <div class="Commenter">
                                    <div class="Avatar">
                                        <a href="/User.aspx?ID=<?= $comment["user_id"] ?>">
                                            <img style="height:64px;width:64px;" 
                                                src="/Thumbs/Avatar.ashx?userId=<?= $comment["user_id"] ?>">
                                        </a>
                                    </div>
                                </div>

                                <div class="Post">
                                    <div class="Audit">
                                        Posted <?= htmlspecialchars(timeAgo($comment["created_at"])) ?>
                                        by
                                        <a href="/User.aspx?ID=<?= $comment["user_id"] ?>">
                                            <?= htmlspecialchars($comment["username"]) ?>
                                        </a>
                                    </div>

                                    <div class="Content">
                                        <?= nl2br(htmlspecialchars($comment["content"])) ?>
                                    </div>
                                </div>

                                <div style="clear: both;"></div>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
                <div class="FooterPager">
                    <span>
                        Page <?= $page ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?ID=<?= $item["id"] ?>&page=<?= $page + 1 ?>">
                            Next <span class="NavigationIndicators">&gt;&gt;</span>
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (isset($_USER["id"])): ?>
                <br>
                <div id="PostComment">
                    <form method="POST" action="/Data/postcomment.ashx">
                        <input type="hidden" name="item_id" value="<?= (int)$item["id"] ?>">
                        <input type="hidden" name="type" value="item">

                        <textarea name="content" maxlength="2000" rows="3" cols="40" class="MultilineTextBox" required
                                style="width:400px; height:80px;"></textarea>

                        <br><br>

                        <button type="submit" class="Button">Post Comment</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    
			</div>     
			            
		</div>
        </div>
        </div>

        </div>
        </div>
        </div>
        <div class="Ads_WideSkyscraper">
            <a href="#" onclick="return false;">
                <img width="160" height="600" border="0">
            </a>
            </div>
        </div>
        <div style="clear: both;"></div>    
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>