<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$countStmt = $db->prepare("SELECT COUNT(*) FROM groups");
$countStmt->execute();

$totalGroups = $countStmt->fetchColumn();
$totalPages = ceil($totalGroups / $limit);

$stmt = $db->prepare("SELECT * FROM groups ORDER BY id DESC LIMIT :limit OFFSET :offset");

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$groups = $stmt->fetchAll();
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE - Groups</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">
        <h2>Groups</h2>
        <button onclick="window.location = 'https://www.ryblox.xyz/My/CreateGroup.aspx'" class="Button">Create a Group!</button>

        <div align="center" class="Pagination">
            Pages:

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <strong><?= $i ?></strong>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

        <div id="GroupList">
            <?php foreach ($groups as $group): ?>
                <div id="groupid<?= $group["id"] ?>" class="GroupDiv">
                    <a class="GroupLink" href="/Group.aspx?ID=<?= $group["id"] ?>">
                        <img src="/Thumbs/GroupIcon.ashx?id=<?= $group["id"] ?>&x=40&y=40" alt="">
                        <span><?= htmlspecialchars($group["name"]) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>

<style>
#GroupList {
    border: 1px solid #000;
    padding: 10px;
}

.GroupDiv {
    padding: 5px 0;
}

.GroupLink img,
.GroupLink span {
    vertical-align: middle;
}

.GroupLink img {
    margin-right: 10px;
    border: 0;
}

.Pagination {
    margin-bottom: 15px;
}

.Pagination a {
    margin: 0 5px;
}

.Pagination strong {
    margin: 0 5px;
}
</style>