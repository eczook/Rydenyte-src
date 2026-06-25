<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$userid = $_GET["UserID"] ?? null;

if (empty($userid)) {
    die("nah");
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$thumbnail = "/Thumbs/Avatar.ashx?userId=".$userid;
$rows = 4;
$columns = 6;

$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.username, u.online
    FROM friends f
    JOIN users u ON (
        (f.user_id = ? AND u.id = f.friend_id)
        OR
        (f.friend_id = ? AND u.id = f.user_id)
    )
");
$stmt->execute([$userid, $userid]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="FriendsContainer">
            <div id="Friends">
                <h4><?= htmlspecialchars($user["username"]) ?>'s Friends (<?= count($friends) ?>)</h4>
                <div id="ctl00_cphRoblox_rbxFriendsPane_Pager1_PanelPages" align="center">
                    Pages:
                </div>
                <table id="ctl00_cphRoblox_rbxFriendsPane_dlFriends" cellspacing="0" align="Center" border="0">
                    <tbody>
                    <?php
                    $chunks = array_chunk($friends, 6);
                    foreach ($chunks as $row):
                    ?>
                        <tr>
                        <?php foreach ($row as $friend): ?>
                            <td>
                                <div class="Friend">
                                    <div class="Avatar">
                                        <a title="<?= htmlspecialchars($friend["username"]) ?>" href="/User.aspx?ID=<?= $friend["id"] ?>" style="display:inline-block;cursor:pointer;">
                                            <img src="/Thumbs/Avatar.ashx?userId=<?= $friend["id"] ?>" border="0" id="img" width="100" height="100" alt="<?= htmlspecialchars($friend["username"]) ?>">
                                        </a>
                                    </div>
                                    <div class="Summary">
                                        <span class="OnlineStatus">
                                            <img
                                                src="/images/<?=
                                                    $friend["online"]
                                                        ? 'OnlineStatusIndicator_IsOnline.gif'
                                                        : 'OnlineStatusIndicator_IsOffline.gif'
                                                ?>"
                                                alt="<?= htmlspecialchars($friend["username"]) ?>"
                                                border="0"
                                            >
                                        </span>
                                        <span class="Name">
                                            <a href="/User.aspx?ID=<?= $friend["id"] ?>">
                                                <?= htmlspecialchars($friend["username"]) ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <?php
                        $remaining = 6 - count($row);
                        for ($i = 0; $i < $remaining; $i++):
                        ?>
                            <td></td>
                        <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>