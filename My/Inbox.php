<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ?");
$stmt->execute([$_USER["id"]]);
$totalMessages = (int)$stmt->fetchColumn();
$totalPages = max(1, ceil($totalMessages / $perPage));

$stmt = $db->prepare("
    SELECT m.*, u.username 
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.recipient_id = ?
    ORDER BY m.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute([$_USER["id"]]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="InboxContainer">
	    <div id="InboxPane">
            <h2>Inbox</h2>
		    <div id="Inbox">
			    
			    <div>
            <table cellspacing="0" cellpadding="3" border="0" id="ctl00_cphRoblox_InboxGridView" style="width:726px;border-collapse:collapse;">
                <tbody style="font-size: 12px;"><tr class="InboxHeader" style="font-size: 12px;">
                    <th align="left" scope="col">
                                        <input id="ctl00_cphRoblox_InboxGridView_ctl01_SelectAllCheckBox" type="checkbox" name="ctl00$cphRoblox$InboxGridView$ctl01$SelectAllCheckBox" onclick="javascript:setTimeout('__doPostBack(\'ctl00$cphRoblox$InboxGridView$ctl01$SelectAllCheckBox\',\'\')', 0)">
                                    </th><th align="left" scope="col"><a href="javascript:__doPostBack('ctl00$cphRoblox$InboxGridView','Sort$m.[Subject]')">Subject</a></th><th align="left" scope="col"><a href="javascript:__doPostBack('ctl00$cphRoblox$InboxGridView','Sort$u.[userName]')">From</a></th><th align="left" scope="col"><a href="javascript:__doPostBack('ctl00$cphRoblox$InboxGridView','Sort$m.[Created]')">Date</a></th>
                </tr>
                <?php foreach ($messages as $msg): ?>
                <?php $sender = getCreator($db, $msg["sender_id"]) ?>
                <tr class="InboxRow">
                    <td>
                                        <span style="display:inline-block;width:25px;"><input id="ctl00_cphRoblox_InboxGridView_ctl02_DeleteCheckbox" type="checkbox" name="ctl00$cphRoblox$InboxGridView$ctl02$DeleteCheckbox"></span>
                                    </td><td align="left">
                                        <?php if ($msg["is_friend_request"] === 0): ?>
                                        <a href="/PrivateMessage.aspx?MessageID=<?= $msg["id"] ?>" style="display:inline-block;width:325px;"><?= htmlspecialchars($msg["subject"]) ?></a></td><td align="left">
                                        <?php else: ?>
                                        <a href="/FriendInvitation.aspx?MessageID=<?= $msg["id"] ?>" style="display:inline-block;width:325px;"><?= htmlspecialchars($msg["subject"]) ?></a></td><td align="left">
                                        <?php endif; ?>
                                        <a id="ctl00_cphRoblox_InboxGridView_ctl02_hlAuthor" title="Visit <?= htmlspecialchars($sender["username"]) ?>'s Home Page" href="/User.aspx?ID=<?= $msg["sender_id"] ?>" style="display:inline-block;width:175px;"><?= htmlspecialchars($sender["username"]) ?></a>
                                    </td><td align="left"><?= date("n/j/Y g:i A", strtotime($msg["created_at"])) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="InboxPager">
                    <td colspan="4">
                        <table border="0">
                            <tr>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <td>
                                    <?php if ($i == $page): ?>
                                        <span><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>

                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody></table>
        </div>
                    </div>
                    <div class="Buttons">
                        <a id="ctl00_cphRoblox_DeleteButton" class="Button" href="javascript:__doPostBack('ctl00$cphRoblox$DeleteButton','')">Delete</a>
                        <a id="ctl00_cphRoblox_CancelHyperLink" class="Button" href="../User.aspx">Cancel</a>
                    </div>
                </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>