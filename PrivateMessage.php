<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";
$messageId = $_GET["MessageID"] ?? null;

if (empty($messageId)) {
    die("not found");
}

$stmt = $db->prepare("
    SELECT m.*, u.username 
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.id = ? AND m.recipient_id = ?
");
$stmt->execute([$messageId, $_USER["id"]]);

$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    die("not found");
}

if (!$message["is_read"]) {
    $stmt = $db->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE id = ? AND recipient_id = ?
    ");
    $stmt->execute([$messageId, $_USER["id"]]);
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_USER["id"]]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

$action = $_GET["acftion"] ?? null;

if ($action === "delete") {
    $stmt = $db->prepare("
        DELETE FROM messages 
        WHERE id = ? AND recipient_id = ?
    ");
    $stmt->execute([$messageId, $_USER["id"]]);

    header("Location: /My/Inbox.aspx");
    exit;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<link rel="stylesheet" href="/CSS/Tabs.css">
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div class="MessageContainer">
        <div id="MessagePane">
			<div id="ctl00_cphRoblox_pPrivateMessage">
	
				<div id="ctl00_cphRoblox_pPrivateMessageReader">
		
					<h3>Private Message</h3>
					<div class="MessageReaderContainer" style="font-size: 12px">
					    

<div id="Message">
    <table width="100%">
        <tr valign="top">
            <td style="width: 10em">
                <div id="DateSent"><?= date("n/j/Y g:i:s A", strtotime($message["created_at"])) ?></div>
                <div id="Author">
                    
                    <a id="ctl00_cphRoblox_rbxMessageReader_Avatar" disabled="disabled" title="<?= htmlspecialchars($message["username"]) ?>" onclick="return false" style="display:inline-block;height:64px;width:64px;"><img src="/Thumbs/Avatar.ashx?userId=<?= $message["sender_id"] ?>" border="0" id="img" alt="<?= htmlspecialchars($message["username"]) ?>" width="65"></a><br />
                    <a id="ctl00_cphRoblox_rbxMessageReader_AuthorHyperLink" title="Visit <?= htmlspecialchars($message["username"]) ?>'s Home Page" href="/User.aspx?ID=29680"><?= htmlspecialchars($message["username"]) ?></a>
                </div>
                <div id="Subject">
                    <?= htmlspecialchars($message["subject"]) ?><br />
                    <br />
                    <div id="ctl00_cphRoblox_rbxMessageReader_AbuseReportButton_AbuseReportPanel" class="ReportAbusePanel">
			
    <span class="AbuseIcon"><a id="ctl00_cphRoblox_rbxMessageReader_AbuseReportButton_ReportAbuseIconHyperLink" href="../AbuseReport/Message.aspx?ID=2140858&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fMy%2fPrivateMessage.aspx%3fMessageID%3d2140858"><img src="../images/abuse.PNG" alt="Report Abuse" style="border-width:0px;" /></a></span>
    <span class="AbuseButton"><a id="ctl00_cphRoblox_rbxMessageReader_AbuseReportButton_ReportAbuseTextHyperLink" href="../AbuseReport/Message.aspx?ID=2140858&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fMy%2fPrivateMessage.aspx%3fMessageID%3d2140858">Report Abuse</a></span>

		</div>
                </div>
            </td>
            <td style="padding: 0 10px 0 10px">
                <div class="Body">
                    <div id="ctl00_cphRoblox_rbxMessageReader_pBody" class="MultilineTextBox" style="height:250px;overflow-y:scroll;width:450px">
                    <?= nl2br(htmlspecialchars($message["body"])) ?>
		            </div>
                </div>
                
            </td>
        </tr>
    </table>
</div>
					    <div style="clear:both"></div>
					</div>
					<div class="Buttons">
						<a id="ctl00_cphRoblox_lbCancel" class="Button" href="/My/Inbox.aspx">Cancel</a>
						<a class="Button" href="?MessageID=<?= $message["id"] ?>&action=delete">Delete</a>
						<a class="Button" href="/My/PrivateMessage.aspx?RecipientID=<?= $message["sender_id"] ?>&MessageID=<?= $message["id"] ?>&reply=1">
                            Reply
                        </a>
					</div>
					<div style="clear:both"></div>
				
	</div>
			
</div>
			
		</div>
		<div style="clear: both;"></div>
	</div>

				</div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>