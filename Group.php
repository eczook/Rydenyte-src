<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$id = $_GET["id"] ?? $_GET["ID"] ?? null;

if (empty($id)) {
    die("nah");
}

$stmt = $db->prepare("SELECT * FROM groups WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

$creator = getCreator($db,$group["creator_id"]);

$stmt = $db->prepare("SELECT * FROM group_members WHERE group_id = ?");
$stmt->execute([$id]);
$groupMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$userId = $_SESSION["user_id"] ?? null;

$isMember = false;

if ($userId) {
    $stmt = $db->prepare("SELECT uid FROM group_members WHERE group_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$id, $userId]);

    $isMember = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = $_POST["action"] ?? null;
        if ($action === "joinGroup") {
            if ($_USER["id"] === $group["creator_id"]) {
                die("go fuck yourself you stupid hackerf");
            }
            if ($isMember) {
                $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
                $stmt->execute([$id, $userId]);
            } else {
                $stmt = $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $stmt->execute([$id, $userId]);
            }
        } else if ($action === "changeIcon") {
            if ($_USER["id"] !== $group["creator_id"]) {
                die("go fuck yourself you stupid hackerf");
            }

            $tmp = $_FILES["file"]["tmp_name"];
            $name = $_FILES["file"]["name"];

            if (empty($name)) {
                die("Invalid file.");
            }

            if (!move_uploaded_file($tmp,$_SERVER["DOCUMENT_ROOT"]."/Thumbs/GroupIcons/$id.png")) {
                die("Error.");
            }
        } else if ($action === "deleteGroup") {
            if ($_USER["id"] !== $group["creator_id"]) {
                die("go fuck yourself you stupid hackerf");
            }

            $stmt = $db->prepare("DELETE FROM groups WHERE id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM group_members WHERE group_id = ?");
            $stmt->execute([$id]);
        } else if ($action == "groupShout") {
            $newshout = $_POST["newshout"] ?? null;
            if ($_USER["id"] !== $group["creator_id"]) {
                die("go fuck yourself you stupid hackerf");
            }

            $stmt = $db->prepare("UPDATE groups SET group_shout = ? WHERE id = ?");
            $stmt->execute([$newshout, $id]);
        }

        if ($action !== "deleteGroup") {
            header("Location: /Group.aspx?id=" . $id);
        } else {
            header("Location: /Groups.aspx");
        }
        exit;
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE - Groups</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <?php if ($_USER["id"] === $group["creator_id"]): ?>
        <div id="ControlPanel" style="border: 1px solid;padding-top:10px;padding-bottom:10px;">
            <form method="post" enctype="multipart/form-data" style="text-align: center;">
                <input type="hidden" name="action" value="changeIcon">
                <input type="file" name="file">
                <br><br>
                <button type="submit" class="Button">Change Icon</button>
            </form>
            <form method="post" style="padding: 10px;">
                <input type="hidden" name="action" value="groupShout">
                <input name="newshout">
                <br><br>
                <button type="submit" class="Button">Shoutout</button>
            </form>
        </div>
        <?php endif; ?>
        <div id="GroupShowcase">
            <img src="/Thumbs/GroupIcon.ashx?id=<?= $group["id"] ?>&x=70&y=70" title="<?= htmlspecialchars($group["description"]) ?>" alt="">
            <h2><?= htmlspecialchars($group["name"]) ?></h2>
            <div style="clear:both;"></div>
        </div>
        <p>By <a href="/User.aspx?ID=<?= $group["creator_id"] ?>"><?= htmlspecialchars($creator["username"]) ?></a></p>
        <p><?= htmlspecialchars($group["description"]) ?></p>
        <?php if ($_USER["id"] !== $group["creator_id"]): ?>
        <?php if ($userId): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" value="joinGroup" name="action">
            <button type="submit" class="Button">
                <?= $isMember ? "Leave Group" : "Join Group" ?>
            </button>
        </form>
        <?php endif; ?>
        <?php else: ?>
        <form method="POST" style="display:inline;" onsubmit="return confirmDelete();">
            <input type="hidden" name="action" value="deleteGroup">
            <input type="submit" class="Button" value="Delete Group" style="color:red;border-color:red;">
        </form>
        <script type="text/javascript">
        function confirmDelete()
        {
            if (!confirm("Are you sure you want to delete this group?"))
            {
                return false;
            }

            if (!confirm("Are you REALLY sure? This cannot be undone."))
            {
                return false;
            }

            if (!confirm("Are you SUPER DUPER sure? You wont get a refund."))
            {
                return false;
            }

            var groupName = "<?= addslashes($group["name"]) ?>";
            var typed = prompt("FINE, type the group name.","");

            if (typed == null)
            {
                return false;
            }

            if (typed != groupName)
            {
                alert("you failed that simple captcha lol");
                return false;
            }

            return true;
        }
        </script>
        <?php endif; ?>
        <div id="GroupShout">
            <?php if (empty($group["group_shout"])): ?>
            <p>There isnt a group shoutout yet.</p>
            <?php else: ?>
            <a href="/User.aspx?ID=<?= $group["creator_id"] ?>"><img src="/Thumbs/Avatar.ashx?userId=<?= $group["creator_id"] ?>&format=Png&x=48&y=48" title="<?= htmlspecialchars($creator["username"]) ?>" alt="<?= htmlspecialchars($creator["username"]) ?>"></a>
            <i>"<?= htmlspecialchars($group["group_shout"]) ?>"</i>
            <?php endif; ?>
        </div>
        <h2>Members</h2>
        <div id="GroupMembers">
            <table>
                <?php
                $count = 0;
                foreach ($groupMembers as $groupMember):
                    if ($count % 7 == 0) {
                        echo "<tr>";
                    }

                    $memberInfo = getCreator($db, $groupMember["user_id"]);
                ?>
                    <td align="center" style="padding:10px;">
                        <div id="groupuser<?= $groupMember["user_id"] ?>">
                            <img src="/Thumbs/Avatar.ashx?userId=<?= $groupMember["user_id"] ?>&format=Png&x=48&y=48">
                            <br>
                            <a href="/User.aspx?ID=<?= $groupMember["user_id"] ?>">
                                <?= htmlspecialchars($memberInfo["username"]) ?>
                            </a>
                        </div>
                    </td>
                <?php
                    $count++;

                    if ($count % 7 == 0) {
                        echo "</tr>";
                    }
                endforeach;

                if ($count % 7 != 0) {
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>
<style>
#GroupShout {
    border: 1px solid #000000;
    padding: 10px;
    margin-top: 10px;
}

#GroupMembers {
    border: 1px solid #000000;
    padding: 10px;
    margin-top: 10px;
}

#GroupMembers table {
    width: 100%;
    border-collapse: collapse;
}

#GroupMembers td {
    width: 14.28%;
    vertical-align: top;
    text-align: center;
}

#GroupShout img,
#GroupShout i {
    vertical-align: middle;
}

#GroupShout img {
    border: 1px solid #000;
    margin-right: 8px;
    background: #FFF;
    padding: 2px;
}

#GroupShowcase img {
    float: left;
    margin-right: 10px;
}

#GroupShowcase h2 {
    float: left;
    margin: 20px 0 0 0;
}
</style>