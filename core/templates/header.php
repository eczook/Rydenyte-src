<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$unreadmsgs = 0;
if (isset($_USER)) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0");
    $stmt->execute([$_USER["id"]]);
    $unreadmsgs = (int)$stmt->fetchColumn();
}
$ad = getAd($db, "728x90");
?>
<center>
    <div class="Ads_WideSkyscraper" style="position: relative; display: inline-block;">
        <?= $ad ? renderAd($ad) : '<img src="/images/UserAds/728x90.png" style="margin-top:5px" width="728" height="90" border="1">' ?>
    </div>
</center>
<div id="Header" style="margin-top: 5px;">
	<div id="Banner">
        <div id="Options">
            <div id="Authentication">
                <?php if (isset($_USER)): ?>
                    <span><a id="ctl00_BannerOptionsLoginView_BannerOptions_Anonymous_LoginHyperLink" href="/User.aspx">Logged in as <?= htmlspecialchars($_USER["username"]) ?></a> | <a href="/Logout.aspx">Logout</a></span>
                <?php else: ?>
                    <span><a id="ctl00_BannerOptionsLoginView_BannerOptions_Anonymous_LoginHyperLink" href="/Login/Default.aspx">Login</a></span>
                <?php endif; ?>
            </div>
            <div id="Settings">
                <?php if (isset($_USER)): ?>
                    Age: 13+, Chat Mode: Safe
                <?php endif; ?>
            </div>
        </div>
						    
		<div id="Logo" style="height: 10px;">
			<a id="ctl00_rbxImage_Logo" title="ROBLOX" href="/Default.aspx" style="display:inline-block;cursor:pointer;"><img src="/images/rydenyte_logo.png?v=2" border="0" alt="ROBLOX" blankurl="http://t6.roblox.com:80/blank-224x59.gif"></a>
		</div>
        <div id="Alerts">
            <table style="width:100%;height:100%">
                <tbody><tr>
                    <?php if (isset($_USER)): ?>
                    <td valign="middle">
                    <div id="ctl00_rbxAlerts_AlertSpacePanel">
                        <div id="AlertSpace">
                            <?php if ($unreadmsgs > 0): ?>
                            <div id="ctl00_rbxAlerts_MessageAlertPanel">
                                <div id="MessageAlert">
                                    <a id="ctl00_rbxAlerts_MessageAlertIconHyperLink" class="MessageAlertIcon" href="/My/Inbox.aspx"><img src="/images/Message.gif" style="border-width:0px;" /></a>&nbsp;
                                    <a id="ctl00_rbxAlerts_MessageAlertCaptionHyperLink" class="MessageAlertCaption" href="/My/Inbox.aspx" style="font-size: 12px;font-weight:bold"><?= $unreadmsgs ?> new messages</a>
                                </div>   
                            </div>
                            <?php endif; ?>
                            <?php if ($_USER["robux"] !== 0): ?>
                            <div id="ctl00_rbxAlerts_RobuxAlertPanel">
                                <div id="RobuxAlert">
                                    <a id="ctl00_rbxAlerts_TicketsAlertIconHyperLink" class="RobuxAlertIcon" href="/My/AccountBalance.aspx"><img src="/images/Robux.png" style="border-width:0px;"></a>&nbsp;
                                    <a id="ctl00_rbxAlerts_TicketsAlertCaptionHyperLink" class="RobuxAlertCaption" href="/My/AccountBalance.aspx" style="font-size: 12px;font-weight:bold"><?= number_format($_USER["robux"]) ?> RYBUX</a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($_USER["tix"] !== 0): ?>
                            <div id="ctl00_rbxAlerts_TicketsAlertPanel">
                                <div id="TicketsAlert">
                                    <a id="ctl00_rbxAlerts_TicketsAlertIconHyperLink" class="TicketsAlertIcon" href="/My/AccountBalance.aspx"><img src="/images/Tickets.png" style="border-width:0px;"></a>&nbsp;
                                    <a id="ctl00_rbxAlerts_TicketsAlertCaptionHyperLink" class="TicketsAlertCaption" href="/My/AccountBalance.aspx" style="font-size: 12px;font-weight:bold"><?= number_format($_USER["tix"]) ?> Tickets</a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
    </div>
                    </div></td>
                    <?php else: ?>
                    <td valign="middle"><a id="ctl00_BannerAlertsLoginView_BannerAlerts_Anonymous_rbxAlerts_SignupAndPlayHyperLink" class="SignUpAndPlay" text="Sign-up and Play!" href="/Login/New.aspx?ReturnUrl=%2fGames.aspx" style="display:inline-block;cursor:pointer;"><img src="/images/Holiday3Button.png" border="0" blankurl="http://t1.roblox.com:80/blank-210x40.gif"></a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>  
	</div>
    <div class="Navigation">
        <span><a id="ctl00_Menu_hlMyRoblox" class="MenuItem" href="/User.aspx">My RYDENYTE</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlGames" class="MenuItem" href="/Games.aspx">Games</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlCatalog" class="MenuItem" href="/Catalog.aspx">Catalog</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlBrowse" class="MenuItem" href="/Browse.aspx">People</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlBuildersClub" class="MenuItem" href="/Upgrades/BuildersClub.aspx">Builders Club</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlForum" class="MenuItem" href="/Forum/Default.aspx">Forum</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlNews" class="MenuItem" href="http://blog.ryblox.xyz/" target="_blank">News</a>&nbsp;<a id="ctl00_Menu_hlNewsFeed" href="http://blog.ryblox.xyz/?feed=rss"><img src="/images/feed-icons/feed-icon-14x14.png" alt="RSS" border="0"></a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlParents" class="MenuItem" href="/Parents.aspx">Parents</a></span>
        <span class="Separator">&nbsp;|&nbsp;</span>
        <span><a id="ctl00_Menu_hlHelp" class="MenuItem" href="http://wiki.ryblox.xyz/" target="_blank">Help</a></span>
    </div>
</div>
<?php if ($_USER["theme"] !== "rbx"): ?>
<div class="SystemAlert" style="border: 2px solid #000;border-top: 1.9px black solid;text-align: center;padding: 1px;">
                    <div id="ctl00_SystemAlertTextColor" class="SystemAlertText" style="background-color:orange;">
                        <div class="Exclamation"></div>
                        <div id="ctl00_LabelAnnouncement" style="color: white;">Welcome to RYDENYTE! <a href="https://discord.gg/d4qcHZm9SW" style="color: white;">Join the discord server</a></div>
                    </div>
                </div>
<?php endif; ?>