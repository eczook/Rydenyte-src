<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$perPage = 15;

$page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $perPage;

$mode = $_GET['m'] ?? 'MostPopular';
$timespan = $_GET['t'] ?? 'Now';

$where = [];
$params = [];

switch ($timespan) {
    case 'PastDay':
        $where[] = "updated_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        break;

    case 'PastWeek':
        $where[] = "updated_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        break;

    case 'PastMonth':
        $where[] = "updated_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;

    case 'AllTime':
    case 'Now':
    default:
        break;
}

$orderBy = "visits DESC";

switch ($mode) {
    case 'MostPopular':
        $orderBy = "visits DESC";
        break;

    case 'TopFavorites':
        $orderBy = "favorites DESC";
        break;

    case 'RecentlyUpdated':
        $orderBy = "updated_at DESC";
        break;

    case 'Featured':
        $where[] = "1=1";
        $orderBy = "updated_at DESC";
        break;

    default:
        $orderBy = "visits DESC";
        break;
}


$whereSQL = "";

if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

$sql = "
    SELECT 
        games.*,
        COALESCE(SUM(gameservers.players), 0) AS online_players
    FROM games
    LEFT JOIN gameservers 
        ON games.id = gameservers.game_id
    $whereSQL
    GROUP BY games.id
    ORDER BY 
        CASE 
            WHEN COALESCE(SUM(gameservers.players), 0) > 0 THEN 0
            ELSE 1
        END,
        online_players DESC,
        $orderBy
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
$modeLabels = [
    'MostPopular' => 'Most Popular',
    'TopFavorites' => 'Top Favorites',
    'RecentlyUpdated' => 'Recently Updated',
    'Featured' => 'Featured Games',
];

$timeLabels = [
    'Now' => 'Now',
    'PastDay' => 'Past Day',
    'PastWeek' => 'Past Week',
    'PastMonth' => 'Past Month',
    'AllTime' => 'All-time',
];

$modeText = $modeLabels[$mode] ?? 'Most Popular';
$timeText = $timeLabels[$timespan] ?? 'Now';

$countSql = "
    SELECT COUNT(*)
    FROM games
    $whereSQL
";

$countStmt = $db->prepare($countSql);

foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();

$totalGames = $countStmt->fetchColumn();

$totalPages = ceil($totalGames / $perPage);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="GamesContainer">
        
        <div id="ctl00_cphRoblox_rbxGames_GamesContainerPanel">
            
            <div class="DisplayFilters">
                <h2>Games&nbsp;<a id="ctl00_cphRoblox_rbxGames_hlNewsFeed" href="/Games.aspx?feed=rss"><img src="/images/feed-icons/feed-icon-14x14.png" alt="RSS" border="0"></a></h2>
                <div id="BrowseMode">
                    <h4>Browse</h4>
                    <ul>
                        <li><img id="ctl00_cphRoblox_rbxGames_MostPopularBullet" class="GamesBullet" src="/images/games_bullet.png" alt="Bullet" border="0"><a id="ctl00_cphRoblox_rbxGames_hlMostPopular" href="Games.aspx?m=MostPopular&amp;t=Now"><b>Most Popular</b></a></li>
                        <li><a id="ctl00_cphRoblox_rbxGames_hlTopFavorites" href="Games.aspx?m=TopFavorites&amp;t=AllTime">Top Favorites</a></li>
                        <li><a id="ctl00_cphRoblox_rbxGames_hlRecentlyUpdated" href="Games.aspx?m=RecentlyUpdated">Recently Updated</a></li>
                        <li><a id="ctl00_cphRoblox_rbxGames_hlFeatured" href="User.aspx?id=1">Featured Games</a></li>
                    </ul>
                </div>
                <div id="ctl00_cphRoblox_rbxGames_pTimespan">
                
                    <div id="Timespan">
                        <h4>Time</h4>
                        <ul>
                            <li><img id="ctl00_cphRoblox_rbxGames_TimespanNowBullet" class="GamesBullet" src="/images/games_bullet.png" alt="Bullet" border="0"><a id="ctl00_cphRoblox_rbxGames_hlTimespanNow" href="Games.aspx?m=MostPopular&amp;t=Now"><b>Now</b></a></li>
                            <li><a id="ctl00_cphRoblox_rbxGames_hlTimespanPastDay" href="Games.aspx?m=MostPopular&amp;t=PastDay">Past Day</a></li>
                            <li><a id="ctl00_cphRoblox_rbxGames_hlTimespanPastWeek" href="Games.aspx?m=MostPopular&amp;t=PastWeek">Past Week</a></li>
                        <!-- <li><a id="ctl00_cphRoblox_rbxGames_hlTimespanPastMonth" href="Games.aspx?m=MostPopular&amp;t=PastMonth">Past Month</a></li> -->
                            <li><a id="ctl00_cphRoblox_rbxGames_hlTimespanAllTime" href="Games.aspx?m=MostPopular&amp;t=AllTime">All-time</a></li>
                        </ul>
                    </div>
                
            </div>
            </div>
            <div id="Games">
                <span id="ctl00_cphRoblox_rbxGames_lGamesDisplaySet" class="GamesDisplaySet"><?= htmlspecialchars("$modeText ($timeText)") ?></span>
                <div class="HeaderPager">
                    <span>
                        Page <?= $page ?> of <?= $totalPages ?>:
                    </span>
                    <?php if ($page > 1): ?>
                        <a href="/Games.aspx?p=<?= $page - 1 ?>">Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="/Games.aspx?p=<?= $page + 1 ?>">
                            Next <span class="NavigationIndicators">&gt;&gt;</span>
                        </a>
                    <?php endif; ?>
                </div>
                <table id="ctl00_cphRoblox_rbxGames_dlGames" cellspacing="0" align="Center" border="0" width="550">
                <tbody>
                <?php
                $count = 0;
                foreach($games as $game):
                    if ($count % 3 == 0) {
                        echo "<tr>";
                    }
                    $creator = getCreator($db,$game["creator_id"]);
                    $onlinePlayers = (int)$game["online_players"];
                ?>
                <td class="Game" valign="top">
                    <div style="padding-bottom:5px">
                        <div class="GameThumbnail">
                            <a id="ctl00_cphRoblox_rbxGames_dlGames_ctl00_ciGame" title="<?= htmlspecialchars($game["name"]) ?>" href="/Place.aspx?ID=<?= $game["id"] ?>" style="display:inline-block;cursor:pointer;"><img src="/Thumbs/Place.ashx?placeId=<?= $game["id"] ?>&x=160&y=100" border="0" alt="<?= htmlspecialchars($game["name"]) ?>"></a>
                        </div>
                        <div class="GameDetails">
                            <div class="GameName"><a id="ctl00_cphRoblox_rbxGames_dlGames_ctl00_hlGameName" href="/Place.aspx?ID=<?= $game["id"] ?>"><?= htmlspecialchars($game["name"]) ?></a></div>
                            <div class="GameLastUpdate"><span class="Label">Updated:</span> <span class="Detail"><?= timeAgo($game["updated_at"]) ?></span></div>
                            <div class="GameCreator"><span class="Label">Creator:</span> <span class="Detail"><a id="ctl00_cphRoblox_rbxGames_dlGames_ctl00_hlGameCreator" href="/User.aspx?ID=<?= $game["creator_id"] ?>"><?= htmlspecialchars($creator["username"]) ?></a></span></div>
                            <div class="AssetFavorites"><span class="Label">Favorited:</span> <span class="Detail">0 times</span></div>
                            <div class="GamePlays"><span class="Label">Played:</span> <span class="Detail"><?= number_format($game["visits"]) ?> times</span></div>
                            <div id="ctl00_cphRoblox_rbxGames_dlGames_ctl00_pGameCurrentPlayers">
                            <?php if ($onlinePlayers != 0): ?>
                            <div class="GameCurrentPlayers"><span class="DetailHighlighted"><?= number_format($onlinePlayers) ?> players online</span></div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>
                <?php
                $count++;
                if ($count % 3 == 0) {
                    echo "</tr>";
                }
                endforeach;
                if ($count % 3 != 0) {
                    echo "</tr>";
                }
                ?>
                </tr>
            </tbody></table>
                <div class="HeaderPager">
                    <span>
                        Page <?= $page ?> of <?= $totalPages ?>:
                    </span>
                    <?php if ($page > 1): ?>
                        <a href="/Games.aspx?p=<?= $page - 1 ?>">Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="/Games.aspx?p=<?= $page + 1 ?>">
                            Next <span class="NavigationIndicators">&gt;&gt;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
                
        <div class="Ads_WideSkyscraper">
            <img width="160" height="600" src="/images/UserAds/160x600.png">
        </div>

        <div style="clear: both;"></div>
    </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>