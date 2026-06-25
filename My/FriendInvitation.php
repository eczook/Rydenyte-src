<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$recipientId = $_GET["RecipientID"] ?? null;

if (empty($recipientId)) {
    die("no recipient");
}

if ($recipientId == $_USER["id"]) {
    die("you cannot friend yourself");
}

$stmt = $db->prepare("
    SELECT * 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$recipientId]);

$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipient) {
    die("user not found");
}

$stmt = $db->prepare("
    SELECT id 
    FROM friends
    WHERE user_id = ?
    AND friend_id = ?
");

$stmt->execute([
    $_USER["id"],
    $recipientId
]);

if ($stmt->fetch()) {
    die("you are already friends");
}

$stmt = $db->prepare("
    SELECT id
    FROM messages
    WHERE sender_id = ?
    AND recipient_id = ?
    AND is_friend_request = 1
");

$stmt->execute([
    $_USER["id"],
    $recipientId
]);

if ($stmt->fetch()) {
    die("friend request already sent");
}

$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $body = trim($_POST["body"] ?? "");
    $subject = trim($_POST["subject"] ?? "");

    if (empty($body)) {
        die("cannot be empty");
    }

    if (empty($subject)) {
        die("cannot be empty");
    }

    $stmt = $db->prepare("
        INSERT INTO messages
        (
            sender_id,
            recipient_id,
            subject,
            body,
            is_friend_request
        )
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $_USER["id"],
        $recipientId,
        $subject,
        $body
    ]);

    $success = true;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<title>Send Friend Request - RYDENYTE</title>
<div id="Container">

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">

        <div class="MessageContainer">

            <div id="MessagePane">
                <?php if ($success): ?> 
                <div id="ctl00_cphRoblox_pConfirmation"> 
                    <div id="Confirmation"> 
                        <h3>Friend Request Sent</h3> 
                        <div id="Message"> 
                            <span id="ctl00_cphRoblox_lConfirmationMessage"> Your friend request has been sent to <?= htmlspecialchars($recipient["username"]) ?>. </span> 
                        </div> 
                        <div class="Buttons"> 
                            <a class="Button" href="/Default.aspx">Continue</a> 
                        </div> 
                    </div> 
                </div> 
                <?php else: ?>
                <div id="ctl00_cphRoblox_pPrivateMessage">

                    <form method="post">

                        <h3>Send Friend Request</h3>

                        <div id="MessageEditorContainer">

                            <div class="MessageEditor">

                                <table width="100%" style="font-size:11px;">
                                    <tr valign="top">
                                        <td style="width:12em">

                                            <div id="From">
                                                <span class="Label">
                                                    From:
                                                </span>

                                                <span class="Field">
                                                    <?= htmlspecialchars($_USER["username"]) ?>
                                                </span>
                                            </div>

                                            <div id="To">
                                                <span class="Label">
                                                    Send To:
                                                </span>

                                                <span class="Field">
                                                    <?= htmlspecialchars($recipient["username"]) ?>
                                                </span>
                                            </div>

                                        </td>
                                        <td style="padding:0 24px 6px 12px">

                                            <div id="Subject">

                                                <div class="Label">
                                                    Subject:
                                                </div>

                                                <div class="Field">

                                                    <input
                                                        type="text"
                                                        name="subject"
                                                        class="TextBox"
                                                        style="width:100%;"
                                                    >

                                                </div>

                                            </div>

                                            <div class="Body">

                                                <div class="Label">
                                                    Message:
                                                </div>

                                                <textarea
                                                    name="body"
                                                    rows="8"
                                                    cols="20"
                                                    class="MultilineTextBox"
                                                    style="width:100%;"
                                                ></textarea>

                                            </div>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                            <div style="clear:both"></div>

                        </div>

                        <div class="Buttons">

                            <button
                                class="Button"
                                type="submit"
                            >
                                Send Friend Request
                            </button>

                        </div>

                    </form>

                </div>
                <?php endif; ?>
            </div>

            <div style="clear: both;"></div>

        </div>

    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>

</div>