<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (empty($_USER) || $_USER["role"] !== "Admin") {
    die("not allowed");
}

if (isset($_POST["generate_key"])) {

    function generateLicenseKey($length = 30)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random = '';

        for ($i = 0; $i < $length; $i++) {
            $random .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return 'RYDENYTE-' . $random;
    }

    $key = generateLicenseKey();

    $stmt = $db->prepare("INSERT INTO invite_keys (`key`) VALUES (?)");
    $stmt->execute([$key]);

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

$stmt = $db->prepare("SELECT * FROM invite_keys ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt = $db->query("SELECT COUNT(*) FROM invite_keys");
$totalKeys = $totalStmt->fetchColumn();

$totalPages = ceil($totalKeys / $limit);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Key Management</title>
<h1>Key Management</h1>

<form method="POST">
    <button type="submit" name="generate_key" class="generate-btn">
        Generate Key
    </button>
</form>
<center>
    <?php foreach ($keys as $row): ?>
        <p style="color:<?= $row["used"] ? 'red' : 'green' ?>"><?= htmlspecialchars($row["key"]) ?> - Used By: <?= $row["used_by"] ?></p>
    <?php endforeach; ?>
    <div style="margin-top:20px;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="margin:0 5px; <?= $i == $page ? 'font-weight:bold;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>
</center>