<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$placeId = $_GET["ID"] ?? $_GET["id"];

if (empty($placeId)) {
    http_response_code(404);
    header("Location: /Error/DoesntExist.aspx");
    exit;
}

$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$placeId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($game)) {
    header("Location: /Error/DoesntExist.aspx");
    exit;
}

$creator = getCreator($db, $game["creator_id"]);
$isFavorited = false;

if (!empty($_USER)) {
    $stmt = $db->prepare("
        SELECT id FROM favorites
        WHERE user_id = ?
        AND item_type = 'game'
        AND item_id = ?
        LIMIT 1
    ");
    $stmt->execute([$_USER["id"], $game["id"]]);
    $isFavorited = $stmt->fetchColumn() ? true : false;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$commentStmt = $db->prepare("
    SELECT c.*, u.username
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.item_id = ? AND c.type = 'place'
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
");
$commentStmt->bindValue(1, $game["id"], PDO::PARAM_INT);
$commentStmt->bindValue(2, $limit, PDO::PARAM_INT);
$commentStmt->bindValue(3, $offset, PDO::PARAM_INT);
$commentStmt->execute();

$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE item_id = ? AND type = 'place'");
$countStmt->execute([$game["id"]]);
$totalComments = (int)$countStmt->fetchColumn();

$totalPages = ceil($totalComments / $limit);

$ad2 = getAd($db, "160x600");
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/Tabs.css">
<title><?= htmlspecialchars($game["name"]) ?> by <?= htmlspecialchars($creator["username"]) ?> - RYDENYTE Places</title>
<script src="/JS/Roblox.js?v=4"></script>
<meta name="description" content="<?= htmlspecialchars($game["description"]) ?>">
<meta property="og:image" content="https://www.ryblox.xyz/Thumbs/Place.ashx?placeId=<?= $game["id"] ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:type" content="website">
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="ItemContainer">
            <div id="Item">
                <h2><?= htmlspecialchars($game["name"]) ?></h2>
                <style>
                .modalPopup {
                    position: fixed;
                    z-index: 10000;
                    left: 0px;
                    top: 0px;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
                    background-color: rgba(100, 100, 100, 0.25);
                    display: none;
                    align-items: center;
                    justify-content: center;
                }

                .modalPopup > div {
                    width: 27em;
                    background-color: #FFFFDD;
                    padding: 20px;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translateX(-50%) translateY(-50%);
                    border-style: solid;
                    border-color: Gray;
                    border-width: 3px;
                }
                </style>
                <div id="Details">
                    <div id="Summary">
                        <h3>RYDENYTE Place</h3>
                        <?php if (isset($_USER) && $_USER["role"] === "Admin"): ?>
                        <a class="Button" href="/Thumbs/Renders/renderPlace.ashx?placeId=<?= $game["id"] ?>">Re-render</a>
                        <?php endif; ?>

                        <div id="Creator" class="Creator">
                            <div class="Avatar">
                                <a id="ctl00_cphRoblox_AvatarImage" title="<?= htmlspecialchars($creator["username"]) ?>" href="/User.aspx?ID=<?= $creator["id"] ?>" style="display:inline-block;cursor:pointer;"><img style="height:100px;" src="/Thumbs/Avatar.ashx?userId=<?= $creator["id"] ?>" border="0" alt="" blankurl="http://t6.roblox.com:80/blank-100x100.gif"></a>
                            </div>
                            Creator: <a id="ctl00_cphRoblox_CreatorHyperLink" href="User.aspx?ID=<?= $creator["id"] ?>"><?= htmlspecialchars($creator["username"]) ?></a>
                        </div>
                        <div id="LastUpdate">Updated: <?= timeAgo($game["updated_at"]) ?></div>
                        <div id="Favorited">Favorited: 0 times</div>
                        <div id="ctl00_cphRoblox_VisitedPanel" class="Visited">Visited: <?= number_format($game["visits"]) ?> times</div>
                        <div id="ctl00_cphRoblox_DescriptionPanel">
                        <?php if (!empty($game["description"])): ?>
                        <div id="DescriptionLabel">Description:</div>
					    <div id="Description"><?= htmlspecialchars($game["description"]) ?></div>
                        <?php endif; ?>
                        </div>              
                        <div id="ReportAbuse"><div id="ctl00_cphRoblox_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
                            <span class="AbuseIcon"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseIconHyperLink" href="AbuseReport/AssetVersion.aspx?ID=2309099&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fItem.aspx%3fID%3d278445"><img src="/images/abuse.PNG" alt="Report Abuse" border="0"></a></span>
                            <span class="AbuseButton"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseTextHyperLink" href="AbuseReport/AssetVersion.aspx?ID=2309099&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fItem.aspx%3fID%3d278445">Report Abuse</a></span>
                        </div></div>
                    </div>
                    <div id="Thumbnail_Place">
				        <a id="ctl00_cphRoblox_AssetThumbnailImage_Place" disabled="disabled" title="<?= htmlspecialchars($game["name"]) ?>" onclick="return false" style="display:inline-block;"><img src="/Thumbs/Place.ashx?placeId=<?= htmlspecialchars($game["id"]) ?>" border="0" alt="<?= htmlspecialchars($game["name"]) ?>" blankurl="http://t1.roblox.com:80/blank-420x230.gif"></a>
			        </div>
                    <div id="Actions_Place">
			            <a href="javascript:void(0)" id="favoriteBtn"><?= $isFavorited ? "Unfavorite" : "Favorite" ?></a>
			        </div>
                    <div id="ctl00_cphRoblox_PlayGames" class="PlayGames">
		                    <div style="text-align: center; margin: 1em 5px;">
                                <span id="ctl00_cphRoblox_PlaceAccessIndicator_FriendsOnlyUnlocked" style="display: none"><img id="ctl00_cphRoblox_PlaceAccessIndicator_iFriendsOnly_Unlocked" src="/images/unlocked.png" alt="Unlocked" border="0">&nbsp;Friends-only: You have access</span>
                                <span id="ctl00_cphRoblox_PlaceAccessIndicator_Public" style="display:inline;"><img id="ctl00_cphRoblox_PlaceAccessIndicator_iPublic" src="/images/public.png" alt="Public" border="0">&nbsp;Public</span>

                                <img id="ctl00_cphRoblox_CopyLockedIcon" src="/images/CopyLocked.png" alt="CopyLocked" border="0">
                                Copy Protection: CopyLocked
                            </div>
                            <div id="ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1" class="modalPopup" style="display: none;">
	
                                <div style="margin: 1.5em">
                                    <div id="Spinner" style="float: left; margin: 0px 1em 1em 0px; display: block;">
                                        <img id="ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Image1" src="/images/ProgressIndicator2.gif?rand=508" alt="Progress" border="0"></div>
                                    <div id="Requesting" style="display: inline;">
                                        Requesting a server</div>
                                    <div id="Waiting" style="display: none;">
                                        Waiting for a server</div>
                                    <div id="Loading" style="display: none;">
                                        A server is loading the game</div>
                                    <div id="Joining" style="display: none;">
                                        The server is ready. Joining the game...</div>
                                    <div id="Error" style="display: none">
                                        An error occured. Please try again later</div>
                                    <div id="Expired" style="display: none">
                                        There are no game servers available at this time. Please try again later</div>
                                    <div id="GameEnded" style="display: none">
                                        The game you requested has ended</div>
                                    <div id="GameFull" style="display: none">
                                        The game you requested is full. Please try again later</div>
                                    <div style="text-align: center; margin-top: 1em">
                                        <input id="Cancel" type="button" class="Button" value="Cancel"></div>
                                </div>

                            </div>
		                    <div id="ctl00_cphRoblox_VisitButtons_VisitMPButton" style="display:inline">
                                <input type="image" name="ctl00$cphRoblox$VisitButtons$MultiplayerVisitButton" id="ctl00_cphRoblox_VisitButtons_MultiplayerVisitButton" class="ImageButton" style="border: none;" onclick="LaunchGame(<?= $game["id"] ?>)" src="/images/Play.png" alt="Visit Online" border="3">
                                <input type="hidden" name="ctl00$cphRoblox$VisitButtons$rbxPlaceLauncher$HiddenField1" id="ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_HiddenField1">
                                    </div>
                                <div id="ctl00_cphRoblox_VisitButtons_VisitButton" style="display:inline">
                                    &nbsp;&nbsp;&nbsp;
                                </div>
		                </div>
                    <div style="clear: both;"></div>
                </div>
                <div style=" width: 703px;margin-left:10px;margin-bottom:10px">
                    <div class="ajax__tab_xp ajax__tab_container ajax__tab_default" id="TabbedInfo">

                    <div id="TabbedInfo_header" class="ajax__tab_header" style="height:21px;">

                    <span id="tab11" class="ajax__tab ajax__tab_active">
                    <span class="ajax__tab_outer">
                    <span class="ajax__tab_inner">
                    <a class="ajax__tab_tab ajax__tab ajax__tab_active" id="__tab_TabbedInfo_GamesTab" onclick="activateTab('tab11','tab22');" style="cursor: pointer;">
                    <h3>Games</h3>
                    </a>
                    </span>
                    </span>
                    </span>

                    <span id="tab22" class="ajax__tab">
                    <span class="ajax__tab_outer">
                    <span class="ajax__tab_inner">
                    <a class="ajax__tab_tab ajax__tab" id="__tab_TabbedInfo_CommentaryTab" onclick="activateTab('tab22','tab11');" style="cursor: pointer;">
                    <h3>Commentary</h3>
                    </a>
                    </span>
                    </span>
                    </span>

                    </div>

                    <div id="TabbedInfo_body" class="ajax__tab_body">
                        <div id="TabbedInfo_GamesTab">
                            <?php
                            $stmt = $db->prepare("SELECT * FROM gameservers WHERE game_id = ?");
                            $stmt->execute([$game["id"]]);
                            $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if (!$servers): ?>
                            <div style="text-align:center;padding:20px;">There are no running games for this place.</div>
                            <?php else: ?>
                            <div id="ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesUpdatePanel">
                                <table id="ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesDataList" cellspacing="0" border="0" width="100%">
					                <tbody>
                                        <?php foreach ($servers as $server): ?>
                                        <?php
                                        $stmt = $db->prepare("SELECT * FROM players WHERE port = ?");
                                        $stmt->execute([$server["port"]]);
                                        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <tr>
						                    <td>
                                                <div class="GameInstance" style="margin: 3px 0">
                                                    <div style="float: right;">
                                                        <?php foreach ($players as $player): ?>
                                                        <?php
                                                        $playerInfo = getCreator($db,$player["user_id"]);
                                                        ?>
                                                        <a id="ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesDataList_ctl00_PlayersRepeater_ctl00_PlayerImage" title="<?= htmlspecialchars($playerInfo["username"]) ?>" href="/User.aspx?id=<?= $playerInfo["id"] ?>" style="display:inline-block;"><img src="/Thumbs/Avatar.ashx?userId=<?= $playerInfo["id"] ?>" width="48" height="48" border="0" alt="<?= htmlspecialchars($playerInfo["username"]) ?>"></a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div style="text-align: left;">
                                                        <?= $server["players"] ?> players of <?= $server["maxplayers"] ?> max<br>
                                                        &nbsp;
                                                    </div>
                                                </div>
                                            </td>
					                    </tr>
                                        <?php endforeach; ?>
				                </tbody>
                            </table>
                            </div>
                            <?php endif; ?>
                            <center><input type="button" class="Button" value="Refresh" onclick="window.location.reload()"></center>
                        </div>
                        <div id="TabbedInfo_CommentaryTab">
                            <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab" style="display:block;">
                            
                                            <div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsUpdatePanel">
                                
                        <div class="CommentsContainer">
                            <h3>Comments (<?= number_format($totalComments) ?>)</h3>
                            <div class="HeaderPager">
                                <span>
                                    Page <?= $page ?> of <?= $totalPages ?>
                                </span>
                                <?php if ($page < $totalPages): ?>
                                    <a href="?ID=<?= $game["id"] ?>&page=<?= $page + 1 ?>">Next<span class="NavigationIndicators">&gt;&gt;</span></a>
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
                                <a href="?ID=<?= $game["id"] ?>&page=<?= $page + 1 ?>">
                                    Next <span class="NavigationIndicators">&gt;&gt;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                                <?php if (isset($_USER)): ?>
                                <div id="PostComment">
                                    <form method="POST" action="/Data/postcomment.ashx">
                                        <input type="hidden" name="item_id" value="<?= $placeId ?>">
                                        <input type="hidden" name="type" value="place">
                                        <textarea name="content" maxlength="2000" rows="3" cols="40" class="MultilineTextBox" required="" style="width:400px; height:80px;"></textarea>
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
                <?= renderAd($ad2) ?>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <script type="text/javascript">
    function activateTab(activeTabId, inactiveTabId) {
        var activeTab = document.getElementById(activeTabId);
        var inactiveTab = document.getElementById(inactiveTabId);

        if (!activeTab || !inactiveTab) {
            return;
        }

        inactiveTab.className = "ajax__tab";

        var inactiveLinks = inactiveTab.getElementsByTagName("a");
        for (var i = 0; i < inactiveLinks.length; i++) {
            inactiveLinks[i].className = "ajax__tab_tab ajax__tab";
        }

        activeTab.className = "ajax__tab ajax__tab_active";

        var activeLinks = activeTab.getElementsByTagName("a");
        for (var j = 0; j < activeLinks.length; j++) {
            activeLinks[j].className = "ajax__tab_tab ajax__tab ajax__tab_active";
        }

        var gamesTab = document.getElementById("TabbedInfo_GamesTab");
        var commentaryTab = document.getElementById("TabbedInfo_CommentaryTab");

        if (activeTabId == "tab11") {
            if (gamesTab) {
                gamesTab.style.display = "block";
            }

            if (commentaryTab) {
                commentaryTab.style.display = "none";
            }
        }
        else if (activeTabId == "tab22") {
            if (gamesTab) {
                gamesTab.style.display = "none";
            }

            if (commentaryTab) {
                commentaryTab.style.display = "block";
            }
        }
    }

    function initTabs() {
        var commentaryTab = document.getElementById("TabbedInfo_CommentaryTab");

        if (commentaryTab) {
            commentaryTab.style.display = "none";
        }
    }

    if (window.attachEvent) {
        window.attachEvent("onload", initTabs);
    } else {
        window.onload = initTabs;
    }
    </script>
    <script type="text/javascript">
    var placeId = <?= (int)$game["id"] ?>;

    function favoritePlace() {

        var xhr = null;

        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }

        if (!xhr) {
            alert("AJAX not supported");
            return;
        }

        xhr.onreadystatechange = function () {

            if (xhr.readyState == 4) {

                if (xhr.status == 200) {

                    var response = xhr.responseText;

                    var btn = document.getElementById("favoriteBtn");

                    if (response == "OK") {
                        btn.innerHTML = "Unfavorite";
                    } else if (response == "NO") {
                        btn.innerHTML = "Favorite";
                    } else {
                        alert("Unexpected response: " + response);
                    }

                } else {
                    alert("Request failed");
                }
            }
        };

        xhr.open("GET", "/Data/FavoriteItem.php?id=" + placeId + "&type=game", true);
        xhr.send(null);
    }

    document.getElementById("favoriteBtn").onclick = favoritePlace;
    </script>
    <script src="/JS/Player.js?v=0" type="text/javascript"> </script>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>