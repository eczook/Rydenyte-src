<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$forumID = $_GET["ForumID"] ?? null;

if (empty($forumID)) {
    die("invalid forum");
}

$stmt = $db->prepare("SELECT * FROM forums WHERE id = ?");
$stmt->execute([$forumID]);
$forum = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$forum) {
    die("forum not found");
}

$stmt = $db->prepare("SELECT t.*, (SELECT COUNT(*) FROM posts p WHERE p.thread_id = t.id) AS replies FROM threads t WHERE t.forum_id = ? ORDER BY t.pinned DESC, t.created_at DESC
");
$stmt->execute([$forumID]);
$threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>


<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<link rel="stylesheet" href="/Forum/skins/default/style/default.css" type="text/css">

<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
            <tr>
                <td></td>
            </tr>

            <tr valign="bottom">
                <td>
                    <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr valign="top">

                            <td>&nbsp; &nbsp; &nbsp;</td>

                            <td id="ctl00_cphRoblox_CenterColumn" width="95%" class="CenterColumn">
                                <br>
                                <span id="ctl00_cphRoblox_Navigationmenu1">
                                <table width="100%" cellspacing="1" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        <td align="right" valign="middle" style="font-size: 11px;">
                                            <a id="ctl00_cphRoblox_Navigationmenu1_ctl00_HomeMenu" class="menuTextLink" href="/Forum/Default.aspx">
                                                <img src="/Forum/skins/default/images/icon_mini_home.gif" border="0">Home &nbsp;
                                            </a>

                                            <a id="ctl00_cphRoblox_Navigationmenu1_ctl00_SearchMenu" class="menuTextLink" href="#">
                                                <img src="/Forum/skins/default/images/icon_mini_search.gif" border="0">Search &nbsp;
                                            </a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                </span>

                                <span id="ctl00_cphRoblox_ThreadView1">
                                <table cellpadding="0" width="100%">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" align="left"><span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1" name="Whereami1">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tbody><tr>
                                            <td valign="top" align="left" width="1px">
                                                <nobr>
                                                    
                                                </nobr>
                                            </td>
                                            <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
                                                <nobr>
                                                    
                                                    <a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_LinkForumGroup" class="linkMenuSink" href="/Forum/Default.aspx">RYDENYTE</a>
                                                </nobr>
                                            </td>

                                            <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
                                                <nobr>
                                                    <span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                                                    <a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_LinkForum" class="linkMenuSink" href="/Forum/ShowForum.aspx?ForumID=<?= $forumID ?>"><?= $forum["name"] ?></a>
                                                </nobr>
                                            </td>

                                            <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
                                                <nobr>
                                                    
                                                    
                                                </nobr>
                                            </td>

                                            <td valign="top" align="left" width="*">&nbsp;</td>
                                        </tr>
                                    </tbody></table>

                                    <span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_MenuScript"></span></span></td>
                                        </tr>
                                    <tr>
                                        <td>
                                            &nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="bottom" align="left">
                                            <a id="ctl00_cphRoblox_ThreadView1_ctl00_NewThreadLinkTop"
                                               href="/Forum/AddThread.aspx?ForumID=<?= $forumID ?>">
                                                <img id="ctl00_cphRoblox_ThreadView1_ctl00_NewThreadImageTop"
                                                     src="/Forum/skins/default/images/newtopic.gif" border="0">
                                            </a>
                                        </td>

                                        <td align="right">
                                            <span class="normalTextSmallBold">Search this forum: </span>
                                            <input type="text">
                                            <input type="submit" value=" Go ">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td valign="top" colspan="2">
                                            <table id="ctl00_cphRoblox_ThreadView1_ctl00_ThreadList"
                                                   class="tableBorder"
                                                   cellspacing="1"
                                                   cellpadding="3"
                                                   border="0"
                                                   width="100%">

                                                <tr style="font-size: 11px;height:27px">
                                                    <th class="tableHeaderText" align="left" colspan="2">&nbsp;Thread&nbsp;</th>
                                                    <th class="tableHeaderText" align="center">&nbsp;Started By&nbsp;</th>
                                                    <th class="tableHeaderText" align="center">&nbsp;Replies&nbsp;</th>
                                                    <th class="tableHeaderText" align="center">&nbsp;Views&nbsp;</th>
                                                    <th class="tableHeaderText" align="center">&nbsp;Last Post&nbsp;</th>
                                                </tr>

                                                <?php foreach ($threads as $t): ?>

                                                <?php
                                                    $userStmt = $db->prepare("SELECT username FROM users WHERE id = ?");
                                                    $userStmt->execute([$t["posted_by"]]);
                                                    $user = $userStmt->fetchColumn();

                                                    $isPinned = (int)$t["pinned"] === 1;
                                                ?>

                                                <tr style="font-size: 11px;">
                                                    <td class="forumRow" align="center" width="25">
                                                        <img src="/Forum/skins/default/images/<?= $isPinned ? "topic-popular.gif" : "topic_notread.gif" ?>">
                                                    </td>

                                                    <td class="forumRow">
                                                        <a class="linkSmallBold"
                                                           href="/Forum/ShowPost.aspx?PostID=<?= $t["id"] ?>">
                                                            <?= htmlspecialchars($t["subject"]) ?>
                                                        </a>
                                                    </td>

                                                    <td class="forumRowHighlight">
                                                        &nbsp;<?= htmlspecialchars($user ?? "Unknown") ?>
                                                    </td>

                                                    <td class="forumRowHighlight" align="center">
                                                        <?= (int)$t["replies"] ?>
                                                    </td>

                                                    <td class="forumRowHighlight" align="center">
                                                        <?= number_format($t["views"]) ?>
                                                    </td>

                                                    <td class="forumRowHighlight" style="text-align: center;">
                                                        <span class="normalTextSmaller">
                                                            <b><?= $isPinned ? "Pinned Post" : date("M d, Y", strtotime($t["created_at"])) ?></b>
                                                            <br>
                                                            by
                                                        </span>
                                                        <a class="linkSmall" href="/User.aspx?UserName=<?= htmlspecialchars($user) ?>"><?= htmlspecialchars($user) ?></a>
                                                    </td>
                                                </tr>

                                                <?php endforeach; ?>
                                                <tr>
                                                    <td class="forumHeaderBackgroundAlternate" colspan="6">&nbsp;</td>
                                                </tr>
                                            </table>
                                            <span id="ctl00_cphRoblox_ThreadView1_ctl00_Pager"><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tbody><tr>
		<td><span class="normalTextSmallBold">Page 1 of 2,884</span></td><td align="right"><span><span class="normalTextSmallBold">Goto to page: </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page0" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page0','')">1</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page1" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page1','')">2</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page2" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page2','')">3</a><span class="normalTextSmallBold"> ... </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page2882" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page2882','')">2,883</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page2883" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page2883','')">2,884</a><span class="normalTextSmallBold">&nbsp;</span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Next" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Next','')">Next</a></span></td>
	</tr>
</tbody></table></span>
<tr>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
    <tr>
		<td align="left" valign="top">
			<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2" name="Whereami2">
<table cellpadding="0" cellspacing="0" width="100%">
    <tbody><tr>
        <td valign="top" align="left" width="1px">
            <nobr>
                <a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkHome" class="linkMenuSink" href="/Forum/Default.aspx">ROBLOX Forum</a>
            </nobr>
        </td>
        <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumGroupSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForumGroup" class="linkMenuSink" href="/Forum/Default.aspx">RYDENYTE</a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForum" class="linkMenuSink" href="/Forum/ShowForum.aspx?ForumID=<?= $forumID ?>"><?= $forum["name"] ?></a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                
                
            </nobr>
        </td>

        <td valign="top" align="left" width="*">&nbsp;</td>
    </tr>
</tbody></table>

<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_MenuScript"></span></span>
			
		</td>
		<td align="right">
			<span class="normalTextSmallBold">Display threads for: </span><select name="ctl00$cphRoblox$ThreadView1$ctl00$DisplayByDays" id="ctl00_cphRoblox_ThreadView1_ctl00_DisplayByDays">
	<option selected="selected" value="0">All Days</option>
	<option value="1">Today</option>
	<option value="3">Past 3 Days</option>
	<option value="7">Past Week</option>
	<option value="14">Past 2 Weeks</option>
	<option value="30">Past Month</option>
	<option value="90">Past 3 Months</option>
	<option value="180">Past 6 Months</option>
	<option value="360">Past Year</option>

</select>
			<br>
			<a id="ctl00_cphRoblox_ThreadView1_ctl00_MarkAllRead" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$MarkAllRead','')">Mark all threads as read</a>
			<br>
			<span class="normalTextSmallBold">
				
			</span>
		</td>
	</tr>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                                </span>

                            </td>

                            <td class="RightColumn">&nbsp;&nbsp;&nbsp;</td>

                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>