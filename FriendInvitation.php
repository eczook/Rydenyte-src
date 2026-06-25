<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$messageId = $_GET["MessageID"] ?? null;

if (empty($messageId)) {
    die("not found");
}

$stmt = $db->prepare("
    SELECT m.*, u.username AS sender_name
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.id = ?
    AND m.recipient_id = ?
    AND m.is_friend_request = 1
");

$stmt->execute([
    $messageId,
    $_USER["id"]
]);

$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    die("not found");
}

$action = $_GET["action"] ?? null;

if ($action) {

    $senderId = $msg["sender_id"];

    if ($action === "accept") {
        $check = $db->prepare("
            SELECT id FROM friends
            WHERE user_id = ? AND friend_id = ?
        ");

        $check->execute([$_USER["id"], $senderId]);

        if (!$check->fetch()) {
            $db->prepare("
                INSERT INTO friends (user_id, friend_id)
                VALUES (?, ?)
            ")->execute([$_USER["id"], $senderId]);

            $db->prepare("
                INSERT INTO friends (user_id, friend_id)
                VALUES (?, ?)
            ")->execute([$senderId, $_USER["id"]]);
        }
    }

    if ($action === "decline" || $action === "accept") {
        $db->prepare("
            DELETE FROM messages
            WHERE id = ?
            AND recipient_id = ?
        ")->execute([$msg["id"], $_USER["id"]]);
    }

    header("Location: /User.aspx");
    exit;
}

$username = htmlspecialchars($msg["sender_name"]);
$userId = (int)$msg["sender_id"];

$message = $msg["body"];
$dateSent = !empty($msg["created_at"]) ? date("n/j/Y g:i:s A", strtotime($msg["created_at"])) : date("n/j/Y g:i:s A");
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<title>Friend Request - RYDENYTE</title>

<div id="Container">

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">

        <div id="InvitationContainer">

            <div id="InvitationPane">

                <div id="ctl00_cphRoblox_pFriendInvitation">

                    <div id="ctl00_cphRoblox_pMessageReader">

                        <h3>Friend Request</h3>

                        <div class="MessageReaderContainer">

                            <div id="Message">

                                <table width="100%">

                                    <tr valign="top">
                                        <td style="width: 10em">
                                            <div id="DateSent">
                                                <?= $dateSent ?>
                                            </div>
                                            <div id="Author">
                                                <a href="/User.aspx?ID=<?= $userId ?>">
                                                    <img src="/Thumbs/Avatar.ashx?userId=<?= $userId ?>" width="64" height="64">
                                                </a>
                                                <br>
                                                <a href="/User.aspx?ID=<?= $userId ?>">
                                                    <?= $username ?>
                                                </a>
                                            </div>
                                            <div id="Subject">
                                                Friend Request
                                            </div>
                                        </td>
                                        <td style="padding: 0 10px 0 10px">

                                            <div class="Body">

                                                <div class="MultilineTextBox" style="height:250px;overflow-y:scroll;">
                                                    <?= $message ?>
                                                </div>

                                            </div>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                            <div style="clear:both"></div>

                        </div>

                    </div>

                    <!-- BUTTONS -->
                    <div id="ctl00_cphRoblox_pSubmit_ExistingInvitation">

                        <div class="Buttons">

                            <a class="Button"
                               href="?MessageID=<?= $msg["id"] ?>&action=accept">
                                Accept
                            </a>

                            <a class="Button"
                               href="?MessageID=<?= $msg["id"] ?>&action=decline">
                                Decline
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <div style="clear: both;"></div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>