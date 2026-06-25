<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$page = isset($_GET["p"]) ? (int)$_GET["p"] : 1;
$perPage = 10;
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $perPage;

$params = [];

$sql = "SELECT * FROM users";

if ($search !== "") {
    $sql .= " WHERE username LIKE ?";
    $params[] = "%" . $search . "%";
}

$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "SELECT COUNT(*) as count FROM users";
$countParams = [];

if ($search !== "") {
    $countSql .= " WHERE username LIKE ?";
    $countParams[] = "%" . $search . "%";
}

$countStmt = $db->prepare($countSql);
$countStmt->execute($countParams);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)["count"];

$totalPages = ceil($total / $perPage);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
  <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/Header.php"; ?>
  <div id="Body">
    <div id="ctl00_cphRoblox_Panel1">
        <div id="BrowseContainer" style="font-family: Verdana, Sans-Serif; text-align: center;">
            <form id="SearchBar" class="SearchBar">
                <span class="SearchBox"><input name="search" type="text" maxlength="100" id="User_Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></span>
                <span class="SearchButton"><button type="submit">Search</button></span>
                <span class="SearchLinks"><sup><a id="ctl00_cphRoblox_ResetSearchButton" href="javascript:WebForm_DoPostBackWithOptions(new WebForm_PostBackOptions(&quot;ctl00$cphRoblox$ResetSearchButton&quot;, &quot;&quot;, true, &quot;&quot;, &quot;&quot;, false, true))">Reset</a>&nbsp;|&nbsp;</sup><a href="#"><sup>Tips</sup>
                    <span>Exact Phrase: "red brick"<br>
                          Find ALL Terms: red and brick =OR=  red + brick<br>
                          Find ANY Term: red or brick =OR= red | brick<br>
                          Wildcard Suffix: tel* (Finds teleport, telamon, telephone, etc.)<br>
                          Terms Near each other: red near brick =OR= red ~ brick<br>
                          Excluding Terms: red and not brick =OR= red - brick<br>
                          Grouping operations: brick and (red or blue) =OR= brick + (red | blue)<br>
                          Combinations: "red brick" and not (tele* or tower) =OR= "red brick" - (tele* | tower)<br>
                          Wildcard Prefix is NOT supported: *port will not find teleport, airport, etc.
                    </span></a>
                </span>
            </form>
            <br>
            <br>
            <div style="margin-left: 45px;">
                <table class="Grid" cellspacing="0" cellpadding="4" id="ctl00_cphRoblox_gvUsersBrowsed">
                    <tbody style="font-size: 12px;">
                        <tr class="GridHeader">
                            <th scope="col">Avatar</th><th scope="col"><a href="javascript:__doPostBack('ctl00$cphRoblox$gvUsersBrowsed','Sort$userName')">Name</a></th><th scope="col">Status</th><th scope="col"><a href="javascript:__doPostBack('ctl00$cphRoblox$gvUsersBrowsed','Sort$lastActivity')">Location / Last Seen</a></th>
                        </tr>
                        <?php foreach ($users as $user): ?>
                        <tr class="GridItem" style="text-align: center;">
                            <td>
                                <a title="<?= htmlspecialchars($user['username']) ?>" 
                                href="/User.aspx?ID=<?= (int)$user['id'] ?>" 
                                style="display:inline-block;cursor:pointer;">
                                
                                    <img src="/Thumbs/Avatar.ashx?userId=<?= (int)$user['id'] ?>" width="48" height="48" 
                                        alt="<?= htmlspecialchars($user['username']) ?>">
                                </a>
                            </td>

                            <td>
                                <a href="User.aspx?ID=<?= (int)$user['id'] ?>">
                                    <?= htmlspecialchars($user['username']) ?>
                                </a><br>

                                <span>
                                    <?= htmlspecialchars($user['blurb'] ?? "") ?>
                                </span>
                            </td>

                            <td>
                                <span>
                                    <?php if ($user["online"] === 0): ?>
                                    Offline
                                    <?php else: ?>
                                    Online
                                    <?php endif; ?>
                                </span><br>
                            </td>

                            <td>
                                <span>
                                    <?= htmlspecialchars($user['last_seen']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php
                        $page = max(1, (int)($page ?? 1));
                        $totalPages = max(1, (int)($totalPages ?? 1));

                        $maxVisible = 10;
                        ?>

                        <tr class="GridPager">
                            <td colspan="4">
                                <table border="0">
                                    <tbody>
                                        <tr>
                                            <?php
                                            $endPage = min($maxVisible, $totalPages);

                                            for ($i = 1; $i <= $endPage; $i++):
                                            ?>
                                                <td>
                                                    <?php if ($i === $page): ?>
                                                        <span style="font-weight: bold; color: white; font-size: 12px"><?= $i ?></span>
                                                    <?php else: ?>
                                                        <a href="?p=<?= $i ?>&search=<?= urlencode($search) ?>" style="font-size: 12px; font-weight: bold;"><?= $i ?></a>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>

                                            <?php if ($totalPages > $maxVisible): ?>
                                                <td>
                                                    <a href="?p=<?= $maxVisible + 1 ?>&search=<?= urlencode($search) ?>">...</a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
  <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/Footer.php"; ?>
</div>