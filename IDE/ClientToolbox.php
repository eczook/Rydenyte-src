<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";

$category = $_GET["Category"] ?? "Bricks";
$category = urldecode($category);

$perPage = 24;
$page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
$offset = ($page - 1) * $perPage;

$search = $_GET["search"] ?? "";
$search = trim($search);

$sql = "SELECT * FROM models WHERE type = ?";

$params = [$category];

if ($search !== "") {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $search . "%";
}

$sql .= " LIMIT ? OFFSET ?";

$stmt = $db->prepare($sql);

$stmt->bindValue(1, $category, PDO::PARAM_STR);

$bindIndex = 2;

if ($search !== "") {
    $stmt->bindValue($bindIndex, "%" . $search . "%", PDO::PARAM_STR);
    $bindIndex++;
}

$stmt->bindValue($bindIndex++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "SELECT COUNT(*) FROM models WHERE type = ?";
$countParams = [$category];

if ($search !== "") {
    $countSql .= " AND name LIKE ?";
    $countParams[] = "%" . $search . "%";
}

$countStmt = $db->prepare($countSql);
$countStmt->execute($countParams);

$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);

$categories = [
    "Bricks",
    "Robots",
    "Chassis",
    "Tools",
    "Furniture",
    "Roads",
    "Skyboxes",
    "Billboards",
    "Game Objects",
    "My Decals",
    "Free Decals",
    "My Models",
    "Free Models"
];
?>

<html>
    <head>
        <title>ur not supposed to be here</title>
        <link rel="stylesheet" href="/IDE/Toolbox.css">
    </head>
    <body oncontextmenu="return false" style="cursor:auto;">
        <table style="height:100%; display:block;" height="100%">
            <tbody>
                <tr>
                    <td style="height:15px;">
                        <select id="selection" style="border: solid 2px Window;" onchange="changeCategory(this.value)">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="height:15px;">
                        <div id="search">
                            <form method="GET" style="display:inline;">
                                <input type="hidden" name="Category" value="<?= htmlspecialchars($category) ?>">
                                Search:
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>">
                                <button type="submit">Go</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height:100%;">
                        <div id="objects" style="width:100%; height:100%;">

                            <?php foreach ($items as $item): ?>
                                <div class="asset" title="<?= htmlspecialchars($item["name"]) ?>">

                                    <div class="a" onclick="insertItem('<?= $item["id"] ?>', '<?= $item["type"] ?>')">

                                        <table width="56px" height="56px">
                                            <tr>
                                                <th>
                                                    <img class="objimg" ondragstart="startDrag('<?= $item["id"] ?>', '<?= $item["type"] ?>');" alt="<?= htmlspecialchars($item["name"]) ?>" src="/Thumbs/Model.ashx?id=<?= $item["id"] ?>">
                                                </th>
                                            </tr>
                                        </table>

                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height:15px;">
                        <div id="pages" align="center">
                            <?php if ($page > 1): ?>
                                <a href="?Category=<?= urlencode($category) ?>&page=<?= $page - 1 ?>">&lt; Previous</a>
                            <?php endif; ?>

                            Page <?= $page ?> of <?= $totalPages ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?Category=<?= urlencode($category) ?>&page=<?= $page + 1 ?>">Next &gt;</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </body>
</html>

<script>
var brickItems = <?php echo json_encode($items); ?>;

function insertItem(item, type)
{
    if(type !== "moderated")
    {
        var nocache = Math.floor(Math.random() * 1000000000);

        window.external.Insert(
            "http://www.ryblox.xyz/IDE/asset/" +
            item +
            "?noCachePlz=" +
            nocache
        );
    }
    else
    {
        alert("Could not insert item");
    }
}

function startDrag()
{

}

function changeCategory(value) {
    window.location.href = "?Category=" + encodeURIComponent(value);
}
</script>