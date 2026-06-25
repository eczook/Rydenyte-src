<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/badgeInfo.php";

$userid = $_GET["ID"] ?? $_GET["id"] ?? null;
$username = $_GET["UserName"] ?? $_GET["username"] ?? null;
$c = $_GET["c"] ?? 17;

$profile = null;
$public = true;

function isUserBanned(PDO $db, int $userId): bool
{
    $stmt = $db->prepare("SELECT * FROM bans WHERE user_id = ? ORDER BY issued_at DESC LIMIT 1");
    $stmt->execute([$userId]);

    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ban) {
        return false;
    }

    return true;
}

if (empty($userid) && empty($username)) {
    if (empty($_USER)) {
        header("Location: /Error/DoesntExist.aspx");
        exit;
    }
    $profile = $_USER;
    $public = false;
} else {

    if (!empty($username)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userid]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$profile) {
    http_response_code(404);
    header("Location: /Error/DoesntExist.aspx");
    exit;
}

$stmt = $db->prepare("SELECT SUM(visits) FROM games WHERE creator_id = ?");
$stmt->execute([$profile["id"]]);
$totalVisits = (int)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM games WHERE creator_id = ?");
$stmt->execute([$profile["id"]]);
$gameCount = (int)$stmt->fetchColumn();

$baseMaxGames = $maxGames ?? 0;

$isAdmin = !empty($_USER) && $_USER["role"] === "Admin";

if ($isAdmin) {
    $finalMaxGames = 99999;
} else {
    $stmt = $db->prepare("SELECT extra_places FROM memberships WHERE user_id = ?");
    $stmt->execute([$profile["id"]]);
    $extraPlaces = (int)($stmt->fetchColumn() ?? 0);

    $finalMaxGames = $baseMaxGames + $extraPlaces;
}

$gamesRemaining = max(0, $finalMaxGames - $gameCount);
$stmt = $db->prepare("SELECT 1 FROM memberships WHERE user_id = ? LIMIT 1");
$stmt->execute([$profile["id"]]);

$hasMembership = (bool)$stmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM games WHERE creator_id = ?");
$stmt->execute([$profile["id"]]);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.username, u.online
    FROM friends f
    JOIN users u ON (
        (f.user_id = ? AND u.id = f.friend_id)
        OR
        (f.friend_id = ? AND u.id = f.user_id)
    )
");
$stmt->execute([$profile["id"], $profile["id"]]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT oi.*, c.*
    FROM owned_items oi
    JOIN catalog c ON c.id = oi.item_id
    WHERE oi.user_id = ? AND c.category = ?
");
$stmt->execute([$profile["id"], $c]);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($totalVisits)) {
    $totalVisits = 0;
}

if (isUserBanned($db, $profile["id"])) {
    header("Location: /Error/DoesntExist.aspx");
}

$ad2 = getAd($db, "300x250");
?>
<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php";
$favoritesPerPage = 6; 
$page = max(1, (int)($_GET['favpage'] ?? 1)); 
$offset = ($page - 1) * $favoritesPerPage;

$stmt = $db->prepare(" SELECT COUNT(*) FROM favorites WHERE user_id = ? "); 
$stmt->execute([$profile["id"]]); 
$totalFavorites = (int)$stmt->fetchColumn();

$totalPages = max(1, ceil($totalFavorites / $favoritesPerPage));
$stmt = $db->prepare("SELECT f.*, g.name AS game_name, g.creator_id, u.username FROM favorites f LEFT JOIN games g ON g.id = f.item_id LEFT JOIN users u ON u.id = g.creator_id WHERE f.user_id = ? AND f.item_type = 'game' ORDER BY f.created_at DESC LIMIT ? OFFSET ? ");
$stmt->bindValue(1, $profile["id"], PDO::PARAM_INT); 
$stmt->bindValue(2, $favoritesPerPage, PDO::PARAM_INT); 
$stmt->bindValue(3, $offset, PDO::PARAM_INT); 
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<title><?= htmlspecialchars($profile["username"]) ?>'s RYDENYTE Home Page</title>
<meta name="description" content="
This is a RYDENYTE user.
Username: <?= htmlspecialchars($profile["username"]) ?>


<?= htmlspecialchars($profile["blurb"] ?? "No blurb.") ?>
">
<script src="/JS/Roblox.js?v=4" type="text/javascript"></script>
<script src="/JS/Player.js?v=0" type="text/javascript"> </script>
<meta property="og:image" content="https://www.ryblox.xyz/Thumbs/Avatar.ashx?userId=<?= $profile["id"] ?>">
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="UserContainer">
            <div id="LeftBank">
                <div id="ProfilePane">
                    <?php
                    $bgcolor = "lightsteelblue";
                    if ($theme === "dark") {
                        $bgcolor = "#383e46";
                    }
                    ?>
                    <table width="100%" style="background-color: <?= $bgcolor ?>;" cellpadding="6" cellspacing="0">
                        <tbody>
                            <tr style="text-align: center;">
                                <td>
                                    <?php if (!$public): ?>
                                    <span id="ctl00_cphRoblox_rbxUserPane_lUserName" class="Title">Hi, <?= htmlspecialchars($profile["username"]) ?>!</span>
                                    <?php else: ?>
                                        <span id="ctl00_cphRoblox_rbxUserPane_lUserName" class="Title"><?= htmlspecialchars($profile["username"]) ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <?php if ($public): ?>
                                        <?php if ($profile["online"] === 0): ?>
                                        <span id="ctl00_cphRoblox_rbxUserPane_lUserOnlineStatus" class="UserOfflineMessage">[ Offline ]</span>
                                        <?php else: ?>
                                        <?php 
                                        $status = "Website";
                                        $check = $db->prepare("SELECT port FROM players WHERE user_id = ? LIMIT 1");
                                        $check->execute([$profile["id"]]);
                                        $currentServer = $check->fetch(PDO::FETCH_ASSOC);

                                        if ($currentServer) {
                                            $stmt = $db->prepare("SELECT game_id FROM gameservers WHERE port = ?");
                                            $stmt->execute([$currentServer["port"]]);
                                            $gameid = $stmt->fetchColumn();

                                            $stmt = $db->prepare("SELECT name FROM games WHERE id = ?");
                                            $stmt->execute([$gameid]);
                                            $status = $stmt->fetchColumn();
;                                        }
                                        ?>
                                        <span id="ctl00_cphRoblox_rbxUserPane_lUserOnlineStatus" class="UserOnlineMessage">[ Online: <?= $status ?> ]</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <tr>
                                    <td style="text-align: center;font-size:11px">
                                        <?php if (!$public): ?>
                                        <span id="ctl00_cphRoblox_rbxUserPane_lUserRobloxURL">Your RYDENYTE:</span>
                                        <?php else: ?>
                                        <span id="ctl00_cphRoblox_rbxUserPane_lUserRobloxURL"><?= htmlspecialchars($profile["username"]) ?>'s RYDENYTE:</span>
                                        <?php endif; ?>
                                        <br>
                                        <a id="ctl00_cphRoblox_rbxUserPane_hlUserRobloxURL" href="/User.aspx?ID=<?= $profile["id"] ?>">http://ryblox.xyz/User.aspx?ID=<?= $profile["id"] ?></a>
                                        <br>
                                        <br>
                                        <div style="left: 0px; float: left; position: relative; top: 0px">
                                            <?php if ($public): ?>
                                            <a id="ctl00_cphRoblox_rbxUserPane_Image1" disabled="disabled" title="<?= htmlspecialchars($profile["username"]) ?>" onclick="return false" style="display:inline-block;"><img src="/Thumbs/Avatar.ashx?userId=<?= $profile["id"] ?>" style="margin-right: 50px;" width="180" border="0" alt="<?= htmlspecialchars($profile["username"]) ?>" blankurl="http://t7.roblox.com:80/blank-180x220.gif"></a>
                                            <?php else: ?>
                                            <a id="ctl00_cphRoblox_rbxUserPane_Image1" disabled="disabled" title="<?= htmlspecialchars($profile["username"]) ?>" onclick="return false" style="display:inline-block;"><img src="/Thumbs/Avatar.ashx?userId=<?= $profile["id"] ?>" style="margin-right: 50px;margin-top:80px" width="180" border="0" alt="<?= htmlspecialchars($profile["username"]) ?>" blankurl="http://t7.roblox.com:80/blank-180x220.gif"></a>
                                            <?php endif; ?>
                                            <br>
                                            <?php if (empty($_USER) || $_USER["id"] != $profile["id"]): ?>
                                            <div id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
                                                <span class="AbuseIcon"><a id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_ReportAbuseIconHyperLink" href="AbuseReport/UserProfile.aspx?userID=<?= $profile["id"] ?>&amp;ReturnUrl=http%3a%2f%2fwww.ryblox.xyz%2fUser.aspx%3fID%3d100614"><img src="/images/abuse.PNG" alt="Report Abuse" border="0"></a></span>
                                                <span class="AbuseButton"><a id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_ReportAbuseTextHyperLink" href="AbuseReport/UserProfile.aspx?userID=<?= $profile["id"] ?>&amp;ReturnUrl=http%3a%2f%2fwww.ryblox.xyz%2fUser.aspx%3fID%3d100614">Report Abuse</a></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!$public): ?>
                                        <div style="text-align: left;">
                                            <p><a href="/My/Inbox.aspx">Inbox</a></p>
                                            <p><a href="/My/Character.aspx">Change Character</a></p>
                                            <p><a href="/My/Profile.aspx">Edit Profile</a></p>
                                            <p><a href="/Upgrades/BuildersClub.aspx">Account Upgrades</a></p>
                                            <p><a href="/My/AccountBalance.aspx">Account Balance</a></p>
                                            <p><a href="/User.aspx?ID=<?= $profile["id"] ?>">View Public Profile</a></p>
                                            <div class="actions">
                                                <p>
                                                    <?php if ($gamesRemaining > 0): ?>
                                                        <a href="/My/PlaceUpload.aspx">Create New Place</a>
                                                    <?php else: ?>
                                                        <span style="color: #999;">Create New Place</span>
                                                    <?php endif; ?>
                                                    <br>
                                                    (<?= $gamesRemaining ?> Remaining)
                                                </p>

                                                <p><a disabled style="color: gray;">Share RYDENYTE</a></p>
                                                <p><a disabled style="color: gray;">Buy RYBUX</a></p>
                                                <p><a href="/Marketplace/TradeCurrency.aspx">Trade Currency</a></p>
                                                <p><a href="/My/AdInventory.aspx">Ad Inventory</a></p>
                                                <p><a href="/My/AdInventory.aspx">Terms, Conditions, and Rules</a></p>
                                                <p><a href="/Groups.aspx">Groups</a></p>
                                                <p><a href="/My/TeamCreate.aspx">Team Create</a></p>
                                            </div>
                                        </div>
                                        <style>
                                            .actions {
                                                padding-left: 230px;
                                            }
                                        </style>
                                        <?php endif; ?>
                                        <?php if (isset($_USER) && $_USER["id"] != $profile["id"]): ?>
                                        <p><a href="/My/PrivateMessage.aspx?RecipientID=<?= $profile["id"] ?>">Send Message</a></p><p><a href="/My/FriendInvitation.aspx?RecipientID=<?= $profile["id"] ?>">Send Friend Request</a></p>
                                        <?php else: ?>
                                        <p></p><p></p>
                                        <?php endif; ?>
                                        <?php if ($public): ?>
                                        <p><span id="ctl00_cphRoblox_rbxUserPane_rbxPublicUser_lBlurb"><?= htmlspecialchars($profile["blurb"] ?? "", ENT_QUOTES, 'UTF-8') ?></span></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tr>
                        </tbody>
                    </table>
                    <?php
                    if ($hasMembership !== false):
                    ?>
                    <div id="InfoContainer">
                        <h2 id="bcDate"></h2>
                    </div>
                    <script type="text/javascript">
                    var today = new Date();
                    var month = today.getMonth() + 1;
                    var day = today.getDate();
                    var year = today.getFullYear();

                    if (month < 10) {month = "0" + month;}
                    if (day < 10) {day = "0" + day;}

                    document.getElementById("bcDate").innerHTML = "Builders Club Member until " + month + "/" + day + "/" + year;
                    </script>
                    <?php endif; ?>
                </div>
                <div id="UserPageLargeRectangleAd" style="margin-top: 10px;">
                        <div id="RobloxLargeRectangleAd">
                            <center>
                                <?= renderAd($ad2) ?>
                            </center>
                        </div>
                    </div>
                <div id="UserBadgesPane">
                    <div id="UserBadges">
                        <h4><a id="ctl00_cphRoblox_rbxUserBadgesPane_hlHeader" href="/Badges.aspx">Badges</a></h4>
                        <table id="ctl00_cphRoblox_rbxUserBadgesPane_dlBadges" cellspacing="0" align="Center" border="0">
                            <tbody>
                                <?php
                                $stmt = $db->prepare("SELECT * FROM badges WHERE owned_by = ?");
                                $stmt->execute([$profile["id"]]);
                                $userBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $count = 0;

                                function getDiscordUsername($discordId, $botToken) {
                                    $url = "https://discord.com/api/v10/users/" . $discordId;

                                    $ch = curl_init($url);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        "Authorization: Bot " . $botToken,
                                        "Content-Type: application/json"
                                    ]);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                    $response = curl_exec($ch);

                                    if (!$response) return null;

                                    $data = json_decode($response, true);

                                    if (!isset($data['username'])) return null;

                                    if (isset($data['discriminator']) && $data['discriminator'] !== "0") {
                                        return $data['username'] . "#" . $data['discriminator'];
                                    }

                                    return $data['username'];
                                }

                                $discordUsername = getDiscordUsername($profile["discord_id"], "...");

                                foreach ($userBadges as $badge):
                                if ($count % 4 == 0) {
                                    echo "<tr>";
                                }

                                if ($badge["name"] === "Verified") {
                                    $displayName = "Verified as @" . htmlspecialchars($discordUsername ?? "unknown");
                                    $image = "/images/Badges/DiscordVerified.png";

                                } else {

                                    $info = $badgeinfo[$badge["name"]];
                                    $displayName = htmlspecialchars($badge["name"]);
                                    $image = htmlspecialchars($info["Image"]);
                                }
                                ?>
                                <td>
                                    <div class="Badge" style="font-size: 11px;">
                                        <div class="BadgeImage"><a id="ctl00_cphRoblox_rbxUserBadgesPane_dlBadges_ctl00_hlHeader" href="/Badges.aspx"><img id="ctl00_cphRoblox_rbxUserBadgesPane_dlBadges_ctl00_iBadge" src="<?= $image ?>" alt="This badge is given to any player who has proven his or her combat abilities by accumulating 10 victories in battle. Players who have this badge are not complete newbies and probably know how to handle their weapons." height="75" border="0"></a></div>
                                        <div class="BadgeLabel"><a id="ctl00_cphRoblox_rbxUserBadgesPane_dlBadges_ctl00_HyperLink1" href="/Badges.aspx"><?= $displayName ?></a></div>
                                    </div>
                                </td>
                                <?php
                                $count++;
                                if ($count % 4 == 0) {
                                    echo "</tr>";
                                }
                                endforeach;
                                if ($count % 4 != 0) {
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="UserStatisticsPane">
                    <div id="UserStatistics" style="height: 115px;">
                        <h4>Statistics</h4>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of this user's friends.">Friends</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lFriendsStatistics">0</span></div>
                        </div>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of posts this user has made to the RYDENYTE forum.">Forum Posts</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lForumPostsStatistics">0</span></div>
                        </div>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of times this user's profile has been viewed.">Profile Views</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lProfileViewsStatistics"><?= number_format($profile["profile_views"]) ?> (0 last week)</span></div>
                        </div>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of times this user's place has been visited.">Place Visits</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lPlaceVisitsStatistics"><?= number_format($totalVisits) ?> (0 last week)</span></div>
                        </div>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of times this user's character has destroyed another user's character in-game.">Knockouts</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lKillsStatistics"><?= number_format($profile["knockouts"]) ?> (0 last week)</span></div>
                        </div>
                        <?php if (!$public): ?>
                        <div class="Statistic">
                            <div class="Label"><acronym title="The number of times this user's character has died in-game.">Wipeouts</acronym>:</div>
                            <div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lKillsStatistics"><?= number_format($profile["wipeouts"]) ?> (0 last week)</span></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="RightBank">
                <div id="UserPlacesPane">
                    <div id="UserPlaces">
                        <h4>Showcase</h4>
                        <div id="ctl00_cphRoblox_rbxUserPlacesPane_ShowcasePlacesAccordion">
                            <?php foreach($games as $game): ?>
                            <div class="AccordionHeader"><?= htmlspecialchars($game["name"]) ?></div>
                            <div id="game_<?= $game["id"] ?>" style="display:none;">
                                <div class="Place">
                                    <div class="PlayStatus">
                                        <span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_FriendsOnlyLocked" style="display: none"><img id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_iFriendsOnly_Locked" src="/images/locked.png" alt="Locked" border="0">&nbsp;Friends-only</span>
                                        <span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_FriendsOnlyUnlocked" style="display: none"><img id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_iFriendsOnly_Unlocked" src="/images/unlocked.png" alt="Unlocked" border="0">&nbsp;Friends-only: You have access</span>
                                        <span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_Public" style="display:inline;"><img id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceAccessIndicator_iPublic" src="/images/public.png" alt="Public" border="0">&nbsp;Public</span>
                                    </div>
                                    <div class="PlayOptions">
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
                                        <br>
                                        <div id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_VisitMPButton" style="display:inline">
                                            <input type="image" name="ctl00$cphRoblox$VisitButtons$MultiplayerVisitButton" id="ctl00_cphRoblox_VisitButtons_MultiplayerVisitButton" class="ImageButton" style="border: none;" onclick="LaunchGame(<?= $game["id"] ?>)" src="/images/Play.png" alt="Visit Online" border="3">
                                            <?php if ($game["creator_id"] === $_USER["id"]): ?>
                                            <input type="image" class="ImageButton" src="/images/BuildSolo.png" alt="Build Solo">
                                            <?php endif; ?>
                                        </div>
                                        <div id="ctl00_cphRoblox_PlayGames" class="PlayGames">
                                            <div id="ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1<?= $game["id"] ?>" class="modalPopup" style="display: none;">
                    
                                                <div style="margin: 1.5em;color:green">
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
                                                        <input id="Cancel<?= $game["id"] ?>" type="button" class="Button" value="Cancel"></div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="Statistics">
                                        <span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_lStatistics">Visited <?= number_format($game["visits"]) ?> times</span>
                                    </div>
                                    <div class="Thumbnail">
                                        <a id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceThumbnail" disabled="disabled" title="<?= htmlspecialchars($game["name"]) ?>" href="/Place.aspx?ID=<?= $game["id"] ?>" style="display:inline-block;"><img src="/Thumbs/Place.ashx?placeId=<?= $game["id"] ?>" border="0" alt="Waterfall! New boat tool!Yto start engine,h&amp;j2turn"></a>
                                    </div>
                                    <div id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_pDescription">
                                        <div class="Description">
                                            <span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_lDescription"><?= htmlspecialchars($game["description"]) ?></span>
                                        </div>
                                    </div>
                                    <?php if (!$public): ?>
                                    <div class="Configuration">
                                        <a id="ctl00_cphRoblox_rbxUserPlacesPane_ctl05_rbxPlatform_hlConfigurePlace" href="My/Place.aspx?ID=<?= $game["id"] ?>">Configure this Place</a>
                                    </div>
                                    <div class="Configuration">
                                        <a id="ctl00_cphRoblox_rbxUserPlacesPane_ctl05_rbxPlatform_hlConfigurePlace" href="My/Ads/CreateAd.aspx?targetId=<?= $game["id"] ?>&type=place">Advertise this Place</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <script type="text/javascript">
                        document.attachEvent
                            ? document.attachEvent("onreadystatechange", initAccordion)
                            : document.addEventListener("DOMContentLoaded", initAccordion, false);

                        function initAccordion() {

                            var headers = document.getElementsByTagName("*");
                            var accordionHeaders = [];
                            var i;

                            for (i = 0; i < headers.length; i++) {
                                if ((" " + headers[i].className + " ").indexOf(" AccordionHeader ") > -1) {
                                    accordionHeaders.push(headers[i]);
                                }
                            }

                            var duration = 300;

                            function setNaturalHeight(el) {
                                el.style.overflow = "";
                                el.style.height = "auto";
                                el.style.display = "block";
                            }

                            function nextElement(el) {
                                do {
                                    el = el.nextSibling;
                                } while (el && el.nodeType !== 1);
                                return el;
                            }

                            for (i = 0; i < accordionHeaders.length; i++) {

                                var content = nextElement(accordionHeaders[i]);
                                if (!content) continue;

                                var isOpen = content.style.display !== "none";

                                if (!isOpen) {
                                    content.style.display = "none";
                                    content.style.height = "0px";
                                } else {
                                    setNaturalHeight(content);
                                }
                            }

                            for (i = 0; i < accordionHeaders.length; i++) {

                                accordionHeaders[i].onclick = function () {

                                    var content = nextElement(this);
                                    if (!content) return;

                                    for (var j = 0; j < accordionHeaders.length; j++) {
                                        var c = nextElement(accordionHeaders[j]);
                                        if (c && c !== content && c.style.display !== "none") {
                                            slideUp(c);
                                        }
                                    }

                                    if (
                                        content.style.display === "none" ||
                                        content.style.height === "0px"
                                    ) {
                                        slideDown(content);
                                    } else {
                                        slideUp(content);
                                    }
                                };
                            }

                            function easeInOutSine(t) {
                                return -(Math.cos(Math.PI * t) - 1) / 2;
                            }

                            function slideDown(el) {

                                el.style.display = "block";
                                el.style.overflow = "hidden";

                                var fullHeight = el.scrollHeight;

                                el.style.height = "0px";

                                var startTime = new Date().getTime();

                                var timer = setInterval(function () {

                                    var elapsed = new Date().getTime() - startTime;
                                    var progress = elapsed / duration;

                                    if (progress > 1) {
                                        progress = 1;
                                    }

                                    var eased = easeInOutSine(progress);

                                    el.style.height = Math.floor(fullHeight * eased) + "px";

                                    if (progress >= 1) {
                                        clearInterval(timer);
                                        el.style.height = "auto";
                                        el.style.overflow = "";
                                    }

                                }, 15);
                            }

                            function slideUp(el) {

                                el.style.overflow = "hidden";

                                var fullHeight = el.offsetHeight;

                                var startTime = new Date().getTime();

                                var timer = setInterval(function () {

                                    var elapsed = new Date().getTime() - startTime;
                                    var progress = elapsed / duration;

                                    if (progress > 1) {
                                        progress = 1;
                                    }

                                    var eased = easeInOutSine(progress);

                                    el.style.height = Math.floor(fullHeight * (1 - eased)) + "px";

                                    if (progress >= 1) {
                                        clearInterval(timer);
                                        el.style.height = "0px";
                                        el.style.display = "none";
                                    }

                                }, 15);
                            }
                        }
                        </script>
                        </div>
                    </div>
                </div>
                <div id="ctl00_cphROBLOX_pFriends">
                    <div id="FriendsPane">
                        <div id="Friends">
                            <h4>
                                <?php if ($public): ?>
                                <?= htmlspecialchars($profile["username"]) ?>'s friends 
                                <?php else: ?>
                                My friends
                                <?php endif; ?>
                                <a href="Friends.aspx?UserID=<?= $profile["id"] ?>">See all <?= count($friends) ?></a>
                                
                                <?php if (!$public): ?>
                                    (<a href="/My/EditFriends.aspx">Edit</a>)
                                <?php endif; ?>
                            </h4>
                            <table id="ctl00_cphROBLOX_rbxFriendsPane_dlFriends" cellspacing="0" align="Center" border="0" style="border-collapse:collapse;">
                                <tbody>
                                    <tr style="font-size: 11px;">
                                    <?php
                                    $i = 0;
                                    $friendsT = array_slice($friends, 0, 6);
                                    foreach ($friendsT as $friend):
                                        if ($i > 0 && $i % 3 === 0) {
                                            echo "</tr><tr style='font-size: 11px;'>";
                                        }
                                    ?>
                                        <td>
                                            <div class="Friend">
                                                <div class="Avatar">
                                                    <a href="/User.aspx?ID=<?= $friend["id"] ?>" 
                                                    style="display:inline-block;height:100px;width:100px;cursor:pointer;">
                                                        <img src="/Thumbs/Avatar.ashx?userId=<?= $friend["id"] ?>" width="100" height="100" alt="<?= htmlspecialchars($friend["username"]) ?>">
                                                    </a>
                                                </div>

                                                <div class="Summary">
                                                    <span class="OnlineStatus">
                                                        <?php if ($friend["online"]): ?>
                                                            <img src="/images/OnlineStatusIndicator_IsOnline.gif" alt="Online">
                                                        <?php else: ?>
                                                            <img src="/images/OnlineStatusIndicator_IsOffline.gif" alt="Offline">
                                                        <?php endif; ?>
                                                    </span>

                                                    <span class="Name">
                                                        <a href="/User.aspx?ID=<?= $friend["id"] ?>">
                                                            <?= htmlspecialchars($friend["username"]) ?>
                                                        </a>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tr>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="FavoritesPane">
			    <div id="ctl00_cphRoblox_rbxFavoritesPane_FavoritesPane">
                    <div id="Favorites">
                        <h4>Favorites</h4>
                        <div id="FavoritesContent">
                            <div class="HeaderPager"> 
                                <span>Page <?= $page ?> of <?= $totalPages ?></span> 
                                <?php if ($page < $totalPages): ?> 
                                    <a href="?id=<?= $profile["id"] ?>&favpage=<?= $page + 1 ?>"> Next 
                                    <span class="NavigationIndicators">&gt;&gt;</span> 
                                </a> 
                                <?php endif; ?> 
                            </div>
                            <table cellspacing="0" border="0">
                            <tbody>

                            <?php
                            $chunks = array_chunk($favorites, 3);

                            foreach ($chunks as $row):
                            ?>
                                <tr>

                                <?php foreach ($row as $favorite): ?>

                                    <td class="Asset" valign="top">
                                        <div style="padding:5px">

                                            <div class="AssetThumbnail">
                                                <a
                                                    title="<?= htmlspecialchars($favorite["game_name"]) ?>"
                                                    href="/Place.aspx?ID=<?= $favorite["item_id"] ?>"
                                                    style="display:inline-block;cursor:pointer;"
                                                >
                                                    <img
                                                        src="/Thumbs/Place.ashx?placeId=<?= $favorite["item_id"] ?>"
                                                        width="110"
                                                        height="110"
                                                        border="0"
                                                        alt="<?= htmlspecialchars($favorite["game_name"]) ?>"
                                                    >
                                                </a>
                                            </div>

                                            <div class="AssetDetails">

                                                <div class="AssetName">
                                                    <a href="/games/<?= $favorite["item_id"] ?>/">
                                                        <?= htmlspecialchars($favorite["game_name"]) ?>
                                                    </a>
                                                </div>

                                                <div class="AssetCreator">
                                                    <span class="Label">Creator:</span>

                                                    <span class="Detail">
                                                        <a href="/User.aspx?ID=<?= $favorite["creator_id"] ?>">
                                                            <?= htmlspecialchars($favorite["username"]) ?>
                                                        </a>
                                                    </span>
                                                </div>

                                            </div>

                                        </div>
                                    </td>

                                <?php endforeach; ?>

                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>

                            <div id="ctl00_cphRoblox_rbxFavoritesPane_FooterPagerPanel" class="FooterPager">
                                <span>Page <?= $page ?> of <?= $totalPages ?></span> 
                                <?php if ($page < $totalPages): ?> 
                                    <a href="?id=<?= $profile["id"] ?>&favpage=<?= $page + 1 ?>"> Next 
                                    <span class="NavigationIndicators">&gt;&gt;</span> 
                                </a> 
                                <?php endif; ?> 
                            </div>
                        </div>
                        <div class="PanelFooter">
                            Category:&nbsp;
                            <select name="ctl00$cphRoblox$rbxFavoritesPane$AssetCategoryDropDownList" id="ctl00_cphRoblox_rbxFavoritesPane_AssetCategoryDropDownList">
                    <option value="2">T-Shirts</option>
                    <option value="11">Shirts</option>
                    <option value="12">Pants</option>
                    <option value="8">Hats</option>
                    <option value="13">Decals</option>
                    <option value="10">Models</option>
                    <option selected="selected" value="9">Places</option>

                </select>
                        </div>
                    </div>
                </div>
			</div>
            </div>
            <div style="clear: both;"></div>
            <div id="ctl00_cphROBLOX_pUserAssets">
	
                    <div id="UserAssetsPane">
                        <div id="ctl00_cphROBLOX_rbxUserAssetsPane_upUserAssetsPane">
                
                        <div id="UserAssets">
                        <h4>Stuff</h4>
                        <div id="AssetsMenu">
                            <?php
                            $categories = [
                                18 => "Faces",
                                17 => "Heads",
                                8  => "Hats",
                                2  => "T-Shirts",
                                11 => "Shirts",
                                12 => "Pants",
                                13 => "Decals",
                                10 => "Models"
                            ];

                            foreach ($categories as $id => $name):
                                $selected = ($c == $id);
                            ?>
                            <div class="<?= $selected ? 'AssetsMenuItem_Selected' : 'AssetsMenuItem' ?>">
                                <a
                                    class="<?= $selected ? 'AssetsMenuButton_Selected' : 'AssetsMenuButton' ?>"
                                    href="?ID=<?= $profile['id'] ?>&c=<?= $id ?>"
                                >
                                    <?= $name ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <div id="AssetsContent">
                        
                        <table id="ctl00_cphROBLOX_rbxUserAssetsPane_UserAssetsDataList" cellspacing="0" border="0" style="border-collapse:collapse;">
                    <tbody>
                        <?php
                        $count = 0;

                        foreach ($assets as $asset):
                            if ($count % 5 == 0) {
                            echo "<tr>";
                            $creator = getCreator($db, $asset["creator_id"]);
                        }
                        ?>
                        <td class="Asset" valign="top">
                                <div style="padding:5px">
                                <div class="AssetThumbnail">
                                    <a id="ctl00_cphROBLOX_rbxUserAssetsPane_UserAssetsDataList_ctl05_AssetThumbnailHyperLink" title="<?= htmlspecialchars($asset["name"]) ?>" href="/Item.aspx?ID=<?= $asset["id"] ?>" style="display:inline-block;cursor:pointer;"><img src="/Thumbs/Item.ashx?id=<?= $asset["id"] ?>&x=110&y=110" border="0" id="img" alt="<?= htmlspecialchars($asset["name"]) ?>"></a>
                                </div>
                                <div class="AssetDetails">
                                    <div class="AssetName"><a id="ctl00_cphROBLOX_rbxUserAssetsPane_UserAssetsDataList_ctl05_AssetNameHyperLink" href="/Item.aspx?ID=<?= $asset["id"] ?>"><?= htmlspecialchars($asset["name"]) ?></a></div>
                                    <div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a id="ctl00_cphROBLOX_rbxUserAssetsPane_UserAssetsDataList_ctl05_GameCreatorHyperLink" href="/User.aspx?ID=<?= $creator["id"] ?>"><?= htmlspecialchars($creator["username"]) ?></a></span></div>
                                    
                                    
                                </div>
                        </div>
                        </td>
                    <?php
                        $count++;
                        endforeach;
                    ?>
                    </tr>
                </tbody></table>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            
            </div>
                    </div>
                
        </div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>
