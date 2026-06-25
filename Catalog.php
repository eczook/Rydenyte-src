<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$m = $_GET["m"] ?? "TopFavorites";
$t = $_GET["t"] ?? "PastWeek";
$c = (int)($_GET["c"] ?? 8);
$q = trim($_GET["q"] ?? "");
$p = max(1, (int)($_GET["p"] ?? 1)); 
$perPage = 25; 
$offset = ($p - 1) * $perPage;

$sortMap = [
    "TopFavorites" => "favorites",
    "BestSelling" => "sold",
    "RecentlyUpdated" => "updated_at"
];

$timeMap = [
    "PastHour" => 3600,
    "PastDay" => 86400,
    "PastWeek" => 604800,
    "PastMonth" => 2592000
];

$titleMap = [
    "PastHour" => "Past Hour",
    "PastDay" => "Past Day",
    "PastWeek" => "Past Week",
    "PastMonth" => "Past Month",
    "AllTime" => "All Time",
];

$categoryMap = [
    2 => "T-Shirts",
    11 => "Shirts",
    12 => "Pants",
    8 => "Hats",
    18 => "Faces",
    19 => "Gears",
    17 => "Heads",
    13 => "Decals",
    10 => "Models",
];

$sortBy = $sortMap[$m] ?? "favorites";
$itemTitle = $categoryMap[$c] ?? "Unknown";
$timeTitle = $titleMap[$t];

$table = "catalog";
$where = "WHERE category = :category";

$params = [
    ":category" => $c
];

if ($c == 10) {
    $table = "models";
    $where = "WHERE type = 'Free Models'";
    unset($params[":category"]);
}

$sql = "FROM {$table} {$where}";

function buildSearchClause(string $search, array &$params): string
{
    $search = trim($search);

    if ($search === '') {
        return '';
    }

    if (preg_match('/^"(.*)"$/', $search, $m)) {
        $params[':search_phrase'] = '%' . $m[1] . '%';
        return ' AND name LIKE :search_phrase';
    }

    if (strpos($search, '|') !== false) {
        $parts = array_filter(array_map('trim', explode('|', $search)));

        $conditions = [];

        foreach ($parts as $i => $part) {
            $key = ":or{$i}";
            $params[$key] = '%' . $part . '%';
            $conditions[] = "name LIKE {$key}";
        }

        return ' AND (' . implode(' OR ', $conditions) . ')';
    }

    if (strpos($search, '+') !== false) {
        $parts = array_filter(array_map('trim', explode('+', $search)));

        $conditions = [];

        foreach ($parts as $i => $part) {
            $key = ":and{$i}";
            $params[$key] = '%' . $part . '%';
            $conditions[] = "name LIKE {$key}";
        }

        return ' AND (' . implode(' AND ', $conditions) . ')';
    }

    if (strpos($search, '-') !== false) {
        [$include, $exclude] = array_map('trim', explode('-', $search, 2));

        $params[':include'] = "%{$include}%";
        $params[':exclude'] = "%{$exclude}%";

        return ' AND name LIKE :include AND name NOT LIKE :exclude';
    }

    if (str_ends_with($search, '*')) {
        $params[':wildcard'] = substr($search, 0, -1) . '%';
        return ' AND name LIKE :wildcard';
    }

    $params[':search'] = "%{$search}%";
    return ' AND name LIKE :search';
}

if (!empty($q)) {
    $sql .= buildSearchClause($q, $params);
    $params[":search"] = "%" . $q . "%";
}

if (isset($timeMap[$t])) {
    $sql .= " AND created_at >= :time";
    $params[":time"] = time() - $timeMap[$t];
}

$countStmt = $db->prepare("SELECT COUNT(*) " . $sql);
$countStmt->execute($params);

$totalItems = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalItems / $perPage));

$query = "SELECT * " . $sql . " ORDER BY $sortBy DESC LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$sql .= " ORDER BY $sortBy DESC";

$stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Rydenyte - Catalog</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="CatalogContainer">
            <form id="SearchBar" class="SearchBar" method="get">
                <input type="hidden" name="m" value="<?= htmlspecialchars($m) ?>">
                <input type="hidden" name="c" value="<?= (int)$c ?>">
                <input type="hidden" name="t" value="<?= htmlspecialchars($t) ?>">
                <span class="SearchBox"><input name="q" type="text" maxlength="100" id="ctl00_cphRoblox_rbxCatalog_SearchTextBox" class="TextBox"></span>
                <span class="SearchButton"><input type="submit" id="ctl00_cphRoblox_rbxCatalog_SearchButton"></span>
                <span class="SearchLinks">
                    <sup><a id="ctl00_cphRoblox_rbx_ResetSearchButton" href="/Catalog.aspx">Reset</a>&nbsp;|&nbsp;</sup>
                    <a href="#"><sup>Tips</sup>
                        <span>Exact Phrase: "red brick"<br>
                        Find ALL Terms: red and brick =OR=  red + brick<br>
                        Find ANY Term: red or brick =OR= red | brick<br>
                        Wildcard Suffix: tel* (Finds teleport, telamon, telephone, etc.)<br>
                        Terms Near each other: red near brick =OR= red ~ brick<br>
                        Excluding Terms: red and not brick =OR= red - brick<br>
                        Grouping operations: brick and (red or blue) =OR= brick + (red | blue)<br>
                        Combinations: "red brick" and not (tele* or tower) =OR= "red brick" - (tele* | tower)            Wildcard Prefix is NOT supported: *port will not find teleport, airport, etc.    </span>
                    </a>
                </span>
            </form>
            <div class="DisplayFilters">
                <h2>Catalog</h2>
                <div id="BrowseMode">
                    <h4>
                        <a onclick="alert('nah this led to a cafepress link')" target="_blank">
                            Buy RYDENYTE Stuff!
                        </a>
                    </h4>

                    <h4>Browse</h4>
                    <ul>
                        <?php
                        $browseModes = [
                            "TopFavorites" => "Top Favorites",
                            "BestSelling" => "Best Selling",
                            "RecentlyUpdated" => "Recently Updated",
                            "ForSale" => "For Sale",
                            "PublicDomain" => "Public Domain"
                        ];

                        foreach ($browseModes as $key => $label):
                            $active = ($m === $key);
                        ?>
                            <?php 
                            $color = "blue";
                            if ($theme === "dark") {
                                $color = "rgb(97, 218, 255)";
                            }
                            ?>
                            <?php if ($theme === "dark"): ?>
                            <?php endif; ?>
                            <li style="color: <?= $color ?>;">
                                <?php if ($active): ?>
                                    <img class="GamesBullet" src="/images/games_bullet.png">
                                    <b><?= htmlspecialchars($label) ?></b>
                                <?php else: ?>
                                    <a href="Catalog.aspx?m=<?= $key ?>&c=<?= $c ?>&t=<?= $t ?>&q=<?= urlencode($q) ?>" style="color: <?= $color ?>;">
                                        <?= htmlspecialchars($label) ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="Category">
                    <h4>Category</h4>
                    <ul>
                        <?php
                        $categories = [
                            17 => "Heads",
                            19 => "Gears",
                            18 => "Faces",
                            8 => "Hats",
                            2 => "T-Shirts",
                            11 => "Shirts",
                            12 => "Pants",
                            13 => "Decals",
                            10 => "Models"
                        ];

                        foreach ($categories as $id => $name):
                        ?>
                            <li style="color: <?= $color ?>;">
                                <?php if ($c == $id): ?>
                                    <img class="GamesBullet" src="/images/games_bullet.png">
                                    <b><?= htmlspecialchars($name) ?></b>
                                <?php else: ?>
                                    <a href="Catalog.aspx?m=<?= $m ?>&c=<?= $id ?>&t=<?= $t ?>">
                                        <?= htmlspecialchars($name) ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="ctl00_cphRoblox_rbxCatalog_Timespan">
                    <h4>Time</h4>
                    <ul>
                        <?php
                        $times = [
                            "PastHour" => "Past Hour",
                            "PastDay" => "Past Day",
                            "PastWeek" => "Past Week",
                            "PastMonth" => "Past Month",
                            "AllTime" => "All-time"
                        ];

                        foreach ($times as $key => $label):
                        ?>
                            <li style="color: <?= $color ?>;">
                                <?php if ($t === $key): ?>
                                    <img class="GamesBullet" src="/images/games_bullet.png">
                                    <b><?= $label ?></b>
                                <?php else: ?>
                                    <a href="Catalog.aspx?m=<?= $m ?>&c=<?= $c ?>&t=<?= $key ?>&q=<?= urlencode($q) ?>">
                                        <?= $label ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="Assets">
                <span id="ctl00_cphRoblox_rbxCatalog_AssetsDisplaySetLabel" class="AssetsDisplaySet"><?= htmlspecialchars($itemTitle) ?>, <?= htmlspecialchars($timeTitle) ?></span>
                <div id="ctl00_cphRoblox_rbxCatalog_HeaderPagerPanel" class="HeaderPager">

                    <span id="ctl00_cphRoblox_rbxCatalog_HeaderPagerLabel">
                        Page <?= $p ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($p > 1): ?>
                        <a href="Catalog.aspx?m=<?= urlencode($m) ?>&c=<?= $c ?>&t=<?= urlencode($t) ?>&q=<?= urlencode($q) ?>&p=<?= $p - 1 ?>">
                            <span class="NavigationIndicators">&lt;&lt;</span> Prev
                        </a>
                    <?php endif; ?>

                    <?php if ($p < $totalPages): ?>
                        <a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Next"
                        href="Catalog.aspx?m=<?= urlencode($m) ?>&c=<?= $c ?>&t=<?= urlencode($t) ?>&q=<?= urlencode($q) ?>&p=<?= $p + 1 ?>">
                            Next <span class="NavigationIndicators">&gt;&gt;</span>
                        </a>
                    <?php endif; ?>

                </div>
                <table id="ctl00_cphRoblox_rbxCatalog_AssetsDataList" cellspacing="0" align="Center" border="0" width="735">

                    <tbody>

                    <?php
                    $i = 0;
                    echo "<tr>";
                    foreach ($items as $item):
                        if ($i > 0 && $i % 5 === 0) {
                            echo "</tr><tr>";
                        }
                        $creator = getCreator($db, $item["creator_id"]);
                    ?>
                    <style>
                        .tshirt {
                            background: url("/images/tshirt.png") center/contain no-repeat;
                            width: 100px;
                            height: 100px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                    </style>
                        <td valign="top">
                            <div class="Asset">
                                <div class="AssetThumbnail<?= (int)$item['category'] === 2 ? ' tshirt' : '' ?>">
                                    <?php if ($item["category"] === 10): ?>
                                    <a href="/Model.aspx?ID=<?= (int)$item["id"] ?>">
                                    <?php else: ?>
                                    <a href="/Item.aspx?ID=<?= (int)$item["id"] ?>">
                                    <?php endif; ?>
                                        <?php if ($item["category"] === 2): ?>
                                        <img src="/Thumbs/Item.ashx?id=<?= $item["id"] ?>&x=80&y=80" alt="<?= htmlspecialchars($item["name"]) ?>">
                                        <?php elseif ($item["category"] === 10): ?>
                                        <img src="/Thumbs/Model.ashx?id=<?= $item["id"] ?>&x=120&y=120" alt="<?= htmlspecialchars($item["name"]) ?>">
                                        <?php else: ?>
                                        <img src="/Thumbs/Item.ashx?id=<?= $item["id"] ?>&x=120&y=120" alt="<?= htmlspecialchars($item["name"]) ?>">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="AssetDetails">
                                    <div class="AssetName">
                                        <?php if ($item["category"] === 10): ?>
                                        <a href="/Model.aspx?ID=<?= (int)$item["id"] ?>">
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </a>
                                        <?php else: ?>
                                        <a href="/Item.aspx?ID=<?= (int)$item["id"] ?>">
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="AssetLastUpdate">
                                        <span class="Label">Updated:</span>
                                        <span class="Detail">
                                            <?= timeAgo($item["updated_at"]) ?>
                                        </span>
                                    </div>
                                    <div class="AssetCreator">
                                        <span class="Label">Creator:</span>
                                        <span class="Detail">
                                            <a href="/User.aspx?ID=<?= $item["creator_id"] ?>"><?= htmlspecialchars($creator["username"]) ?></a>
                                        </span>
                                    </div>
                                    <div class="AssetsSold">
                                        <span class="Label">Number Sold:</span>
                                        <span class="Detail">
                                            <?= (int)$item["sold"] ?>
                                        </span>
                                    </div>
                                    <div class="AssetFavorites">
                                        <span class="Label">Favorited:</span>
                                        <span class="Detail">
                                            <?= (int)$item["favorites"] ?> times
                                        </span>
                                    </div>
                                    <?php if ($item["for_sale"] !== 0): ?>
                                        <?php if ($item["price_robux"] !== 0): ?>
                                        <div class="AssetPrice">
                                            <span class="PriceInRobux">
                                                R$: <?= (int)$item["price_robux"] ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($item["price_tix"] !== 0): ?>
                                        <div class="AssetPrice">
                                            <span class="PriceInTickets">
                                                Tx: <?= (int)$item["price_tix"] ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="AssetPrice">
                                            <span class="PriceInRobux" style="color: gray;">
                                                Offsale
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>

                        </td>

                    <?php
                    $i++;
                    endforeach;

                    echo "</tr>";
                    ?>

                    </tbody>
                </table>
                <div id="ctl00_cphRoblox_rbxCatalog_HeaderPagerPanel" class="HeaderPager">
                    <span id="ctl00_cphRoblox_rbxCatalog_HeaderPagerLabel">
                        Page <?= $p ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($p > 1): ?>
                        <a href="Catalog.aspx?m=<?= urlencode($m) ?>&c=<?= $c ?>&t=<?= urlencode($t) ?>&q=<?= urlencode($q) ?>&p=<?= $p - 1 ?>">
                            <span class="NavigationIndicators">&lt;&lt;</span> Prev
                        </a>
                    <?php endif; ?>

                    <?php if ($p < $totalPages): ?>
                        <a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Next"
                        href="Catalog.aspx?m=<?= urlencode($m) ?>&c=<?= $c ?>&t=<?= urlencode($t) ?>&q=<?= urlencode($q) ?>&p=<?= $p + 1 ?>">
                            Next <span class="NavigationIndicators">&gt;&gt;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>