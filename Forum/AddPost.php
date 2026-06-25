<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$replyID = isset($_GET["PostID"]) ? intval($_GET["PostID"]) : 0;

if($replyID <= 0){
    die("Invalid reply.");
}

$post = null;
$threadID = null;

$stmt = $db->prepare("
    SELECT
        t.id,
        t.subject AS title,
        t.content AS body,
        t.posted_by AS user_id,
        t.created_at,
        u.username
    FROM threads t
    LEFT JOIN users u ON u.id = t.posted_by
    WHERE t.id = ?
");
$stmt->execute([$replyID]);

$post = $stmt->fetch();

if($post){
    $threadID = $post["id"];
}

if(!$post){
    $stmt = $db->prepare("
        SELECT
            p.id,
            p.thread_id,
            p.content AS body,
            p.posted_by AS user_id,
            p.created_at,
            t.subject AS title,
            u.username
        FROM posts p
        LEFT JOIN threads t ON t.id = p.thread_id
        LEFT JOIN users u ON u.id = p.posted_by
        WHERE p.id = ?
    ");
    $stmt->execute([$replyID]);

    $post = $stmt->fetch();

    if ($post) {
        $threadID = $post["thread_id"];
    }
}

if(!$post){
    die("Post not found.");
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $content = trim($_POST["content"] ?? "");
    if($content !== ""){
        $stmt = $db->prepare("
            INSERT INTO posts
            (
                thread_id,
                posted_by,
                content,
                created_at
            )
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([
            $threadID,
            $_USER["id"],
            $content
        ]);

        header("Location: /Forum/ShowPost.aspx?PostID=".$threadID);
        exit;
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<div id="Container">
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

<div id="Body">

<form method="post">

<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tbody>

<tr><td></td></tr>

<tr valign="bottom">
<td>

<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
<tbody>

<tr valign="top">

<td>&nbsp;&nbsp;&nbsp;</td>

<td width="95%" class="CenterColumn">

<br>

<table cellpadding="0" width="100%">
<tbody>

<tr></tr>
<tr><td>&nbsp;</td></tr>

<tr>
<td valign="top" colspan="2">

<table class="tableBorder" cellspacing="1" cellpadding="3" width="100%" align="left">
<tbody>

<tr>
<th class="tableHeaderText" align="left" height="25">
&nbsp;Post a New Message
</th>
</tr>

<tr>

<td class="forumRow">

<table cellspacing="1" cellpadding="3">

<tbody>

<tr>
<td colspan="2">
<span class="normalTextSmall">
The message you are replying to:
</span>
</td>
</tr>

<tr>

<td valign="top" nowrap="" align="right">
<span class="normalTextSmallBold">Posted By: </span>
</td>

<td valign="top" align="left">

<a id="ReplyPostedBy" class="normalTextSmall">

<?=htmlspecialchars($post["username"]) ?>

</a>

<a id="ReplyPostedByDate" class="normalTextSmall">

<?=date("d F Y H:i", strtotime($post["created_at"]))?>

</a>

</td>

</tr>

<tr>

<td valign="top" align="right">
<span class="normalTextSmallBold">Subject: </span>
</td>

<td valign="top" align="left">

<a id="ReplySubject" class="normalTextSmall" href="/Forum/ShowPost.php?ThreadID=<?=$threadID?>">

<?=htmlspecialchars($post["title"]) ?>

</a>

</td>

</tr>

<tr>

<td valign="top" align="right">
<span class="normalTextSmallBold">Message: </span>
</td>

<td valign="top" align="left">

<span class="normalTextSmall">

<label id="ReplyBody">

<?=nl2br(htmlspecialchars($post["body"]))?>

</label>

</span>

</td>

</tr>

</tbody>

</table>

</td>

</tr>

<tr>
<td class="forumAlternate">&nbsp;</td>
</tr>

<tr>

<td class="forumRow">

<table cellspacing="1" cellpadding="3">

<tbody>

<tr>

<td valign="top" nowrap="" align="right">
<span class="normalTextSmallBold">Author: </span>
</td>

<td valign="top" align="left" colspan="2">

<span class="normalTextSmall">

<span id="PostAuthor">

<?=htmlspecialchars($_USER["username"]) ?>

</span>

</span>

</td>

</tr>

<tr>

<td valign="top" nowrap="" align="right">

<span class="normalTextSmallBold">Message: </span>

</td>

<td valign="top" align="left">

<textarea name="content" id="content" cols="72" rows="2" style="height: 200px;"></textarea>

</td>

</tr>

<tr>

<td valign="top" align="right" colspan="2">

<a onclick="history.back(-1);">

<input type="submit" name="Cancel" id="Cancel" value=" Cancel ">

</a>

</td>

</tr>

<tr>

<td valign="top" align="right" colspan="2">

<input id="finish" type="submit" value=" Post ">

</td>

</tr>

</tbody>

</table>

</td>

</tr>

</tbody>
</table>

</td>
</tr>

</tbody>
</table>

</td>

<td>&nbsp;&nbsp;&nbsp;</td>
<td>&nbsp;&nbsp;&nbsp;</td>

</tr>

</tbody>
</table>

</form>

</div>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>

</div>
<style>
a.linkSmallBold, a.linkMenuSink
{
    font-weight: bold;
}

a.linkSmall, a.LinkSmallBold, a.linkMenuSink
{
    color: navy;
    font-size: 10px;
}


a.linkSmallBold:visited, a.linkMenuSink:visited
{
    color: #013DA4;
}

a.linkSmallBold:Hover, a.linkMenuSink:Hover
{
    color: #DD6900;
}
.userOnlineLinkBold, a.userOnlineLinkBold, a.userOnlineLinkBold:Visited, a.userOnlineLinkBold:Hover, a.userOnlineLinkBold:Link
{
    font-weight: bold;
    color: #0055E7;
}

.moderatorOnlineLinkBold, a.moderatorOnlineLinkBold, a.moderatorOnlineLinkBold:Visited, a.moderatorOnlineLinkBold:Hover, a.moderatorOnlineLinkBold:Link
{
    font-weight: bold;
    color: darkblue;
}

.adminOnlineLinkBold, a.adminOnlineLinkBold, a.adminOnlineLinkBold:Visited, a.adminOnlineLinkBold:Hover, a.adminOnlineLinkBold:Link
{
    font-weight: bold;
    color: black;
}
.menuTitle
{
    font-weight: bold;
    font-size: 20px;
    font: normal 8pt/normal Verdana, sans-serif;
    FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    color: navy;
}

.menuText
{
    font-size: 0.9em;
    font-weight: bold;
    font: normal 8pt/normal Verdana, sans-serif;
    color: #FFFFFF;
}

a.menuTextLink:visited, a.menuTextLink:link
{
    font-size: 0.9em;
    text-decoration: none; 
    font: normal 8pt/normal Verdana, sans-serif;
    color: #013DA4;
}

a.menuTextLink:Hover
{
    color: #000000;
}

.searchPager
{
    font-size : 0.9em;
    font-weight: bold;
}

.searchItem
{
    background-color: #DDEEFF; 
}

.searchAlternatingItem
{
    background-color: #FFFFFF;
}


/*****************************************************
Default separator style for PostList
*****************************************************/
td.flatViewSpacing
{
    height: 2px;
    background-color: #80B7FF;
}

/*****************************************************
Table Header and cell definitions
*****************************************************/
th
{
    background-image: url(/images/forumHeaderBackground.gif);
    background-color: #4455aa
}

td.forumHeaderBackgroundAlternate
{
    background-image: url(/images/forumHeaderBackgroundAlternate.gif);
    background-color: #EBEDF6;
}

/*****************************************************
Body
*****************************************************/
body 
{
    FONT-SIZE: 8pt;
    font: normal 8pt/normal Verdana, sans-serif;
    scrollbar-face-color: #DEE3E7;
    scrollbar-highlight-color: #FFFFFF;
    scrollbar-shadow-color: #DEE3E7;
    scrollbar-3dlight-color: #D1D7DC;
    scrollbar-arrow-color:  #006699;
    scrollbar-track-color: #EFEFEF;
    scrollbar-darkshadow-color: #98AAB1;
}


/*****************************************************
Validation Text
*****************************************************/
.validationWarningSmall
{
    color: Red;
    font-size : 0.9em;
}

/*****************************************************
General Text
*****************************************************/
.normalTextSmall 
{ 
    font-size : 11px;
}

.normalTextSmallBold
{ 
    font-size : 11px;
    font-weight: bold;
}

.normalTextSmaller
{
    font-size: 10px;
}

.normalTextSmall, .normalTextSmallBold, .normalTextSmaller
{ 
    FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
}

/*****************************************************
Text used on tables with a background
*****************************************************/
.tableHeaderText
{
    color: white;
    font-size: 10px;
    font-weight:bold;
    font: normal 8pt/normal Verdana, sans-serif;
}

/*****************************************************
Border used around tables
*****************************************************/
.tableBorder
{
    border: 1px #013DA4 solid; 
    background-color: #FFFFFF;
}

/*****************************************************
Main forum colors
*****************************************************/
td.forumRow
{
    background-color: #DDEEFF;
}


td.forumAlternate
{
    background-color: #DAE7FD;
}

/*****************************************************
Background color and text used in threaded view
*****************************************************/
td.threadTitle
{
    background-color: #D4D9EC;
}

.threadDetailTextSmall
{
    color: #0055E7;
    font-size: 0.9em;
}

.threadDetailTextSmallBold
{
    color: #0055E7;
    font-size: 0.9em;
    font-weight: bold;
    font: normal 8pt/normal Verdana, sans-serif;
}

td.forumRowHighlight
{
    background-color: #D4D9EC;
}

/*****************************************************
Text and links used in ForumGroupRepeater and ForumRepeater
*****************************************************/
.forumTitle
{
    font-size: 1.0px;
    font-weight: bold;
    font: normal 8pt/normal Verdana, sans-serif;
    color: #013DA4;
}


a.forumTitle:visited, a.forumTitle:link
{
    font-size: 1.0em;
    font-weight: bold;
    color: #013DA4;
}

a.forumTitle:hover
{
    color: #DD6900;
}

.forumName
{
    font-weight: bold;
    FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    font-size: 16px; 
    text-decoration: none; 
    color: navy;
}

a.forumName:hover
{
    color: #DD6900;
    text-decoration: underline;
}


/*****************************************************
Form Elements
*****************************************************/
select
{   FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    font-size: 0.9em;
    font-weight: bold;
    background-color: #DAE7FD;
    border-color: Black;
}

textarea
{
    font-size: 0.9em;
    FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    background-color: White;
    border-color: Black;
}

/*****************************************************
Menu Controls
*****************************************************/
A.linkMenuSink
{
    font-size: 0.9em;
    FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    position: relative;
}

TD.popupMenuSink
{
    position: relative;
}

DIV.popupMenu
{
    border: 1px solid blue;
}

DIV.popupTitle
{
  FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    color: white;
    font-weight: bold;
    background-color: #4455AA;
}

DIV.popupItem
{
    font-size: 1.0em;
    font-weight: bold;
  FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;
    background-color: #DDEEFF;
}
</style>