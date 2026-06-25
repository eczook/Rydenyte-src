<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_USER["id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$rows = 4;
$columns = 6;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_friend"])) {
    $friendId = (int)$_POST["friend_id"];
    
    $stmt = $db->prepare("
        DELETE FROM friends
        WHERE (user_id = ? AND friend_id = ?)
           OR (user_id = ? AND friend_id = ?)
    ");

    $stmt->execute([
        $_USER["id"],
        $friendId,
        $friendId,
        $_USER["id"]
    ]);

    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit;
}

$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.username, u.online
    FROM friends f
    JOIN users u ON (
        (f.user_id = ? AND u.id = f.friend_id)
        OR
        (f.friend_id = ? AND u.id = f.user_id)
    )
");
$stmt->execute([$_USER["id"], $_USER["id"]]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="FriendsContainer">
            <div id="Friends">
                <h4>My Friends (<?= count($friends) ?>)</h4>
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
                                            <img src="/images/<?=$friend["online"] ? 'OnlineStatusIndicator_IsOnline.gif' : 'OnlineStatusIndicator_IsOffline.gif'?>" alt="<?= htmlspecialchars($friend["username"]) ?>" border="0">
                                        </span>
                                        <span class="Name">
                                            <a href="/User.aspx?ID=<?= $friend["id"] ?>"><?= htmlspecialchars($friend["username"]) ?></a>
                                        </span>
                                    </div>
                                    <div class="Options">
                                        <form method="POST">
                                            <input type="hidden" name="friend_id" value="<?= $friend["id"] ?>">
                                            <button type="submit" name="delete_friend">
                                                Delete
                                            </button>
                                        </form>
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