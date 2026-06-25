<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";
if(!isset($_USER)){
    header("Location: /login");
    exit;
}

$forumID = isset($_GET['ForumID']) ? intval($_GET['ForumID']) : 0;

$check = $db->prepare("SELECT id FROM forums WHERE id = ?");
$check->execute([$forumID]);

if(!$check->fetch()){
    die("Invalid forum.");
}

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if($title !== "" && $content !== ""){

        $stmt = $db->prepare("INSERT INTO threads (forum_id, posted_by, subject, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$forumID,$_USER["id"],$title,$content]);
        $threadID = $db->lastInsertId();

        header("Location: /Forum/ShowPost.aspx?PostID=".$threadID);
        exit;
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>


<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<link rel="stylesheet" href="/Forum/skins/default/style/default.css" type="text/css">
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <form id="forumform" action="" method="POST">
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

        <span></span>

        <span>

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

        <td valign="top" nowrap="" align="right">

        <span class="normalTextSmallBold">
        Author:
        </span>

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

        <td valign="center" nowrap="" align="right">

        <span class="normalTextSmallBold">
        Subject:
        </span>

        </td>

        <td valign="top" align="left">

        <input name="title" type="text" id="PostSubject" cols="55" style="width: 340px;">

        </td>

        </tr>

        <tr>

        <td valign="top" nowrap="" align="right">

        <span class="normalTextSmallBold">
        Message:
        </span>

        </td>

        <td valign="top" align="left">

        <textarea name="content" id="PostBody" cols="72" rows="2" style="height: 200px;"></textarea>

        </td>

        </tr>

        <tr>

        <td valign="center" align="right" width="93">
        <span class="normalTextSmallBold">&nbsp;</span>
        </td>

        <td valign="top" align="left">

        <span class="normalTextSmall">

        <input type="checkbox" name="AllowReplies" id="AllowReplies">

        <span>
        Do not allow replies to this post.
        </span>

        </span>

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

        <button id="finish" type="submit">
        Post
        </button>

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

        </span>

        </td>

        <td>&nbsp;&nbsp;&nbsp;</td>

        <td>&nbsp;&nbsp;&nbsp;</td>

        </tr>

        </tbody>

        </table>

        </td>

        </tr>

        </tbody>

        </table>

        </form>

        </div>
        <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
        </div>
        <style>
        /*****************************************************
        General Anchor
        *****************************************************/
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


        /*****************************************************
        Text and Anchor to display when a user is online
        *****************************************************/
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

        /*****************************************************
        Text and anchors used in the navigation menu
        *****************************************************/
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


        /*****************************************************
        Text and anchors used in the search
        *****************************************************/
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
    </div>
</div>