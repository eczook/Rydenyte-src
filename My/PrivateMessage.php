<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$reply = isset($_GET["reply"]) && $_GET["reply"] == "1";
$recipientId = $_GET["RecipientID"] ?? null;
$messageId = $_GET["MessageID"] ?? null;

if (empty($recipientId)) {
    die("no recipient");
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$recipientId]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

$replySubject = "";
$replyBody = "";

if ($reply && !empty($messageId)) {

    $stmt = $db->prepare("
        SELECT m.*, u.username
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.id = ?
        AND m.recipient_id = ?
    ");

    $stmt->execute([
        $messageId,
        $_USER["id"]
    ]);

    $originalMessage = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($originalMessage) {

        $replyDate = date(
            "n/j/Y \\a\\t g:i A",
            strtotime($originalMessage["created_at"])
        );

        $replySubject = "RE: " . $originalMessage["subject"];

        $replyBody =
            "\n\n------------------------------\n" .
            "On {$replyDate} {$originalMessage["username"]} wrote:\n\n" .
            $originalMessage["body"];
    }
}

if (!$recipient) {
    die("user not found");
}

$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject = trim($_POST["subject"] ?? "");
    $body = trim($_POST["body"] ?? "");

    if ($subject === "" || $body === "") {
        die("missing fields");
    }

    $stmt = $db->prepare("
        INSERT INTO messages (sender_id, recipient_id, subject, body)
        VALUES (?, ?, ?, ?)
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
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div class="MessageContainer">
            <div id="MessagePane">
                <?php if ($success): ?>
                <div id="ctl00_cphRoblox_pConfirmation">
                    <div id="Confirmation">
                        <h3>Message Sent</h3>
                        <div id="Message"><span id="ctl00_cphRoblox_lConfirmationMessage">Your message has been sent to <?= htmlspecialchars($recipient["username"]) ?>.</span></div>
                        <div class="Buttons"><a id="ctl00_cphRoblox_lbContinue" class="Button" href="/Default.aspx">Continue</a></div>
                    </div>
                </div>
                <?php else: ?>
                <div id="ctl00_cphRoblox_pPrivateMessage">	
                    <form id="ctl00_cphRoblox_pPrivateMessageEditor" method="post">
            
                        <h3>Your Message</h3>
                        <div id="MessageEditorContainer">
                            
                        <div class="MessageEditor">
                            <table width="100%" style="font-size: 11px;">
                                <tbody><tr valign="top">
                                <td style="width:12em">
                                        <div id="From">
                                            <span class="Label">
                                                <span id="ctl00_cphRoblox_rbxMessageEditor_lblFrom">From:</span></span> <span class="Field">
                                                <span id="ctl00_cphRoblox_rbxMessageEditor_lblAuthor">
                                                    <?= htmlspecialchars($_USER["username"]) ?>
                                                </span>
                                        </div>
                                        <div id="To">
                                            <span class="Label">
                                                <span id="ctl00_cphRoblox_rbxMessageEditor_lblTo">Send To:</span></span> <span class="Field">
                                                <span id="ctl00_cphRoblox_rbxMessageEditor_lblRecipient">
                                                <?= htmlspecialchars($recipient["username"]) ?>
                                                </span>
                                        </div>
                                        
                                    </td>
                                    <td style="padding:0 24px 6px 12px">
                                        <div id="Subject">
                                            <div class="Label">
                                                <label for="ctl00_cphRoblox_rbxMessageEditor_txtSubject" id="ctl00_cphRoblox_rbxMessageEditor_lblSubject">Subject:</label></div>
                                            <div class="Field">
                                                <input name="subject" type="text" class="TextBox"
                                                value="<?= htmlspecialchars($replySubject) ?>"
                                                style="width:100%;">
                                        </div>
                                        <div class="Body">
                                            <div class="Label">
                                                <label for="ctl00_cphRoblox_rbxMessageEditor_txtBody" id="ctl00_cphRoblox_rbxMessageEditor_lblBody">Message:</label></div>
                                                <textarea name="body"
                                                rows="2"
                                                cols="20"
                                                id="ctl00_cphRoblox_rbxMessageEditor_txtBody"
                                                class="MultilineTextBox"
                                                style="width:100%;"><?= htmlspecialchars($replyBody) ?></textarea>
                                        </div>
                                        
                                    </td>
                                </tr>
                            </tbody></table>
                        </div>

                            <div style="clear:both"></div>
                        </div>
                        <div class="Buttons">
                            <button id="ctl00_cphRoblox_lbSend" class="Button" type="submit">Send</button>
                            
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