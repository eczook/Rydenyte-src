<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php";

$thread_id = isset($_GET['PostID']) ? (int)$_GET['PostID'] : 0;
if ($thread_id <= 0) {
    die("Invalid thread.");
}

$stmt = $db->prepare("
    SELECT t.*, f.name AS forum_name, f.id AS forum_id,
           c.name AS category_name, c.id AS category_id
    FROM threads t
    JOIN forums f ON t.forum_id = f.id
    JOIN forum_categories c ON f.category_id = c.id
    WHERE t.id = ?
");
$stmt->execute([$thread_id]);
$thread = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thread) {
    die("Thread not found.");
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$thread['posted_by']]);
$op = $stmt->fetch(PDO::FETCH_ASSOC);

$db->prepare("UPDATE threads SET views = views + 1 WHERE id = ?")
   ->execute([$thread_id]);

$stmt = $db->prepare("
    SELECT p.*, u.id, u.username, u.created_at AS user_created_at
    FROM posts p
    JOIN users u ON p.posted_by = u.id
    WHERE p.thread_id = ?
    ORDER BY p.created_at ASC
");
$stmt->execute([$thread_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getUser($db, $id) {
    static $cache = [];
    if (isset($cache[$id])) return $cache[$id];

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $cache[$id] = $stmt->fetch(PDO::FETCH_ASSOC);

    return $cache[$id];
}
?>

<link rel="stylesheet" href="/Forum/skins/default/style/default.css" type="text/css">
<title><?= htmlspecialchars($thread['subject']) ?></title>

<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tbody><tr>
					<td>
						</td>
				</tr>
				<tr valign="bottom">
					<td>
						<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
							<tbody><tr valign="top">
								<td>&nbsp; &nbsp; &nbsp;</td>
								<td id="ctl00_cphRoblox_CenterColumn" width="95%" class="CenterColumn">
									<br>
									<span id="ctl00_cphRoblox_Navigationmenu1">
<table width="100%" cellspacing="1" cellpadding="0">
	<tbody><tr>
		<td align="right" valign="middle">
			<a id="ctl00_cphRoblox_Navigationmenu1_ctl00_HomeMenu" class="menuTextLink" href="/web/20080729234457/http://www.roblox.com/Forum/Default.aspx"><img src="/Forum/skins/default/images/icon_mini_home.gif" border="0">Home &nbsp;</a>
			<a id="ctl00_cphRoblox_Navigationmenu1_ctl00_SearchMenu" class="menuTextLink" href="/web/20080729234457/http://www.roblox.com/Forum/Search/default.aspx"><img src="/Forum/skins/default/images/icon_mini_search.gif" border="0">Search &nbsp;</a>
		</td>
	</tr>
</tbody></table>
</span>
									<span id="ctl00_cphRoblox_PostView1">
<table cellpadding="0" width="100%">
  <tbody><tr>
    <td align="left" colspan="2"><span id="ctl00_cphRoblox_PostView1_ctl00_Whereami1" name="Whereami1">
<table cellpadding="0" cellspacing="0" width="100%">
    <tbody><tr>
        <td valign="top" align="left" width="1px">
            <nobr>
                
            </nobr>
        </td>
        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_LinkForumGroup" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/ShowForumGroup.aspx?ForumGroupID=1">ROBLOX</a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_LinkForum" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/ShowForum.aspx?ForumID=13">General Discussion</a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_PostSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_LinkPost" class="linkMenuSink" href="/Forum/ShowPost.aspx?PostID=<?= $thread["id"] ?>"><?= htmlspecialchars($thread["subject"]) ?></a>
            </nobr>
        </td>

        <td valign="top" align="left" width="*">&nbsp;</td>
    </tr>
</tbody></table>

<span id="ctl00_cphRoblox_PostView1_ctl00_Whereami1_ctl00_MenuScript"></span></span></td>
  </tr>
  <tr>
    <td align="left" colspan="2">&nbsp;
    </td>
  </tr>
  <tr>
    <td valign="top" align="left">
	<span class="normalTextSmallBold"></span>
    </td>
    <td valign="bottom" align="right"><span class="normalTextSmallBold">Display using: </span><select name="ctl00$cphRoblox$PostView1$ctl00$DisplayMode" id="ctl00_cphRoblox_PostView1_ctl00_DisplayMode">
	<option selected="selected" value="Flat">Flat View</option>
	<option value="Threaded">Threaded View</option>

</select>&nbsp;<select name="ctl00$cphRoblox$PostView1$ctl00$SortOrder" id="ctl00_cphRoblox_PostView1_ctl00_SortOrder">
	<option selected="selected" value="0">Oldest to newest</option>
	<option value="1">Newest to oldest</option>

</select>
    </td>
  </tr>
  <tr>
    <td colspan="2">
    <table id="ctl00_cphRoblox_PostView1_ctl00_PostList" class="tableBorder" cellspacing="1" cellpadding="0" border="0" width="100%">
	<tbody><tr>
		<td class="forumHeaderBackgroundAlternate" colspan="2" height="20"><table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tbody><tr>
				<td align="left"></td><td align="right"><a id="ctl00_cphRoblox_PostView1_ctl00_PostList_ctl00_PreviousThread" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$PostView1$ctl00$PostList$ctl00$PreviousThread','')">Previous Thread</a>&nbsp;<span class="normalTextSmallBold">::</span>&nbsp;<a id="ctl00_cphRoblox_PostView1_ctl00_PostList_ctl00_NextThread" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$PostView1$ctl00$PostList$ctl00$NextThread','')">Next Thread</a>&nbsp;</td>
			</tr>
		</tbody></table></td>
	</tr><tr>
		<th class="tableHeaderText" align="left" height="25" width="100">&nbsp;Author</th><th class="tableHeaderText" align="left" width="85%">&nbsp;Thread: <?= htmlspecialchars($thread["subject"]) ?></th>
	</tr>
    <tr>
		<td class="forumRow" valign="top" width="150" nowrap="nowrap"><table border="0">
			<tbody><tr>
				<td><img src="/images/OnlineStatusIndicator_IsOffline.gif" alt="<?= htmlspecialchars($op["username"]) ?> is not online. Last active: 7/29/2008 2:54:52 PM" border="0">&nbsp;<a class="normalTextSmallBold" href="/User.aspx?UserName=<?= htmlspecialchars($op["username"]) ?>"><?= htmlspecialchars($op["username"]) ?></a><br></td>
			</tr><tr>
				<td><a href="/User.aspx?UserName=<?= htmlspecialchars($op["username"]) ?>"><img src="/Thumbs/Avatar.ashx?x=64&y=64&userId=<?= $op["id"] ?>" border="0"></a></td>
			</tr><tr>
				<td><span class="normalTextSmaller"><b>Joined:</b>idk</span></td>
			</tr><tr>
				<td><span class="normalTextSmaller"><b>Total Posts: </b>idk</span></td>
			</tr><tr>
				<td>&nbsp;</td>
			</tr>
		</tbody></table></td><td class="forumRow" valign="top"><table cellspacing="0" cellpadding="3" border="0" width="100%">
			<tbody><tr>
				<td class="forumRowHighlight"><span class="normalTextSmallBold"><?= htmlspecialchars($thread["subject"]) ?><a name="1988370"></a></span><a name="1988370"><br><span class="normalTextSmaller"> Posted: </span><span class="normalTextSmaller">25 Jul 2008 12:04 PM</span></a></td>
			</tr><tr>
				<td colspan="2"><span class="normalTextSmall"><?= htmlspecialchars($thread["content"]) ?></span></td>
			</tr><tr>
				<td colspan="2"><span class="normalTextSmaller"></span></td>
			</tr><tr>
				<td height="2"></td>
			</tr><tr>
				<td colspan="2">
                    <a href="/Forum/AddPost.aspx?PostID=<?= $thread["id"] ?>&amp;mode=flat"><img border="0" src="/Forum/skins/default/images/newpost.gif"></a>
                    <a href="/AbuseReport/ForumPost.aspx?PostID=<?= $thread["id"] ?>&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fForum%2fShowPost.aspx%3fPostID%3d1988370">Report Abuse</a>
                </td>
			</tr>
		</tbody></table></td>
	</tr>
    <?php foreach ($posts as $post): ?>
    <?php
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$post["posted_by"]]);
    $originalPoster = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <tr>
		<td class="forumAlternate" valign="top" nowrap="nowrap"><table border="0">
			<tbody><tr>
				<td><img src="/images/OnlineStatusIndicator_IsOffline.gif" alt="Randomist is not online. Last active: 7/29/2008 9:14:36 AM" border="0">&nbsp;<a class="normalTextSmallBold" href="/User.aspx?UserName=<?= htmlspecialchars($originalPoster["username"]) ?>"><?= htmlspecialchars($originalPoster["username"]) ?></a><br></td>
			</tr><tr>
				<td><a href="/User.aspx?UserName=<?= htmlspecialchars($originalPoster["username"]) ?>"><img src="/Thumbs/Avatar.ashx?x=64&y=64&userId=<?= $originalPoster["id"] ?>" border="0"></a></td>
			</tr><tr>
				<td><span class="normalTextSmaller"><b>Joined:</b> idk</span></td>
			</tr><tr>
				<td><span class="normalTextSmaller"><b>Total Posts: </b>idk</span></td>
			</tr><tr>
				<td>&nbsp;</td>
			</tr>
		</tbody></table></td><td class="forumAlternate" valign="top"><table cellspacing="0" cellpadding="3" border="0" width="100%">
			<tbody><tr>
				<td class="forumRowHighlight"><span class="normalTextSmallBold">Re: <?= htmlspecialchars($thread["subject"]) ?><a name="2047294"></a></span><a name="2047294"><br><span class="normalTextSmaller"> Posted: </span><span class="normalTextSmaller">29 Jul 2008 06:05 AM</span></a></td>
			</tr><tr>
				<td colspan="2"><span class="normalTextSmall"><?= htmlspecialchars($post["content"]) ?></span></td>
			</tr><tr>
				<td colspan="2"><span class="normalTextSmaller"></span></td>
			</tr><tr>
				<td height="2"></td>
			</tr><tr>
				<td colspan="2"><a href="/Forum/AddPost.aspx?PostID=<?= $thread["id"] ?>&mode=flat"><img border="0" src="/Forum/skins/default/images/newpost.gif"></a><a href="/AbuseReport/ForumPost.aspx?PostID=<?= $thread["id"] ?>&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fForum%2fShowPost.aspx%3fPostID%3d2047283">Report Abuse</a></td>
			</tr>
		</tbody></table></td>
	</tr>
    <?php endforeach; ?>
    <tr>
		<td class="forumHeaderBackgroundAlternate" colspan="2" height="20"><table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tbody><tr>
				<td align="left"></td><td align="right"><a id="ctl00_cphRoblox_PostView1_ctl00_PostList_ctl02_PreviousThread" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$PostView1$ctl00$PostList$ctl02$PreviousThread','')">Previous Thread</a>&nbsp;<span class="normalTextSmallBold">::</span>&nbsp;<a id="ctl00_cphRoblox_PostView1_ctl00_PostList_ctl02_NextThread" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$PostView1$ctl00$PostList$ctl02$NextThread','')">Next Thread</a>&nbsp;</td>
			</tr>
		</tbody></table></td>
	</tr>
</tbody></table><span id="ctl00_cphRoblox_PostView1_ctl00_Pager"><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tbody><tr>
		<td><span class="normalTextSmallBold">Page 1 of 1</span></td>
	</tr>
</tbody></table></span></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" colspan="2">

    </td>
  </tr>
  <tr>
    <td align="left" colspan="2">
      <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami2" name="Whereami2">
<table cellpadding="0" cellspacing="0" width="100%">
    <tbody><tr>
        <td valign="top" align="left" width="1px">
            <nobr>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_LinkHome" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/Default.aspx">ROBLOX Forum</a>
            </nobr>
        </td>
        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_ForumGroupSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_LinkForumGroup" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/ShowForumGroup.aspx?ForumGroupID=1">ROBLOX</a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_LinkForum" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/ShowForum.aspx?ForumID=13">General Discussion</a>
            </nobr>
        </td>

        <td id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
            <nobr>
                <span id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_PostSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
                <a id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_LinkPost" class="linkMenuSink" href="/web/20080729234457/http://www.roblox.com/Forum/ShowPost.aspx?PostID=1988370">Reminder: Trading is not allowed.</a>
            </nobr>
        </td>

        <td valign="top" align="left" width="*">&nbsp;</td>
    </tr>
</tbody></table>

<span id="ctl00_cphRoblox_PostView1_ctl00_Whereami2_ctl00_MenuScript"></span></span>
    </td>
  </tr>
</tbody></table>
</span>
								</td>

								<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
								<!-- right margin -->
								<td class="RightColumn">&nbsp;&nbsp;&nbsp;</td>
								
							</tr>
						</tbody></table>
					</td>
				</tr>
			</tbody></table>
    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>