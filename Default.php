<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$stmt = $db->prepare("SELECT * FROM games ORDER BY RAND() LIMIT 5");
$stmt->execute();

$coolGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>
<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <script>window.RufflePlayer=window.RufflePlayer||{};window.RufflePlayer.config={"autoplay":"on","unmuteOverlay":"hidden"};</script>
        <div id="SplashContainer">
            <div id="SignInPane">
                <div id="LoginViewContainer">
                    
                            <form id="LoginView" method="post" action="/Login/Default.aspx">
                                <?php if (isset($_USER)): ?>
                                <h5>Logged In</h5>
                                <?php else: ?>
                                <h5>Member Login</h5>
                                <?php endif; ?>
                                <div class="AspNet-Login">
                                        <div class="AspNet-Login">
                                            <?php if (isset($_USER)): ?>
                                            <img src="/Thumbs/Avatar.ashx?userId=<?= $_USER["id"] ?>" alt="<?= htmlspecialchars($_USER["username"]) ?>" width="140" style="margin-top:20px;margin-bottom:20px">
                                            <?php else: ?>
                                            <div class="AspNet-Login-UserPanel">
                                                <label for="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_UserName" id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_UserNameLabel" class="Label">Character Name</label>
                                                <input name="username" type="text" id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_UserName" tabindex="1" class="Text">
                                            </div>
                                            <div class="AspNet-Login-PasswordPanel">
                                                <label for="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_Password" id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_PasswordLabel" class="Label">Password</label>
                                                <input name="password" type="password" id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_Password" tabindex="2" class="Text">
                                            </div>
                                            <div class="AspNet-Login-SubmitPanel">
                                                <button id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_Login" tabindex="4" class="Button" type="submit">Login</button>
                                            </div>
                                            <div class="AspNet-Login-PasswordRecoveryPanel">
                                                <a id="ctl00_cphRoblox_rbxLoginView_lvLoginView_lSignIn_hlPasswordRecovery" tabindex="5" href="Login/ResetPasswordRequest.aspx">Forgot your password?</a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                </div>
                            </form>
                        
                </div>

			<br>
            <?php if (isset($_USER)): ?>
            <div style="text-align:center; background-color:#eeeeee; border:1px solid black;">
            <br>
            <h3>RYDENYTE News</h3>
            <a style="color: blue;" href="/">TRAILER!!</a>
            <br><br>
            <a style="color: blue;" href="/Install/Default.aspx">Client Release</a>
            <br><br>
            <a style="color: blue;" href="/Place.aspx?ID=1">First ever GAME!</a>
            <br><br>
            </div>
            <?php else: ?>
            <div id="Figure">
				<a id="ctl00_cphRoblox_LoginView1_ImageFigure" disabled="disabled" title="Figure" onclick="return false" style="display:inline-block;"><img src="/images/NewFrontPageGuy.png" border="0" alt="Figure" blankurl="http://t1.roblox.com:80/blank-115x130.gif"></a>
			</div>
            <?php endif; ?>
		</div>
        <div id="RobloxAtAGlance">
            <h2>RYDENYTE Virtual Playworld</h2>
            <h3>RYDENYTE is Free!</h3>
            <ul id="ThingsToDo">
                <li id="Point1">
                    <h3>Build your personal Place</h3>
                    <div>Create buildings, vehicles, scenery, and traps with thousands of virtual bricks.</div>
                </li>
                <li id="Point2">
                    <h3>Meet new friends online</h3>
                    <div>Visit your friend's place, chat in 3D, and build together.</div>
                </li>
                <li id="Point3">
                    <h3>Battle in the Brick Arenas</h3>
                    <div>Play with the slingshot, rocket, or other brick battle tools.  Be careful not to get "bloxxed".</div>
                </li>
            </ul>
            <div id="Showcase">
                <?php if (isset($_USER) && $_USER["theme"] === "rbx"): ?>
                <object width="400" height="326"><param name="movie" value="//www.youview.lol/v/Yf4k7X9tPCg"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youview.lol/v/Yf4k7X9tPCg" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="400" height="326"></embed></object>
                <?php else: ?>
                <object width="400" height="326"><param name="movie" value="//www.youview.lol/v/iTmXeUSJ7Ev"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youview.lol/v/iTmXeUSJ7Ev" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="400" height="326"></embed></object>
                <?php endif; ?>
            </div>
            <div id="ForParents">
                <a id="ctl00_cphRoblox_RobloxAtAGlanceLoginView_RobloxAtAGlance_Anonymous_hlKidSafe" title="RYDENYTE is kid-safe!" href="/Parents.aspx" style="display:inline-block;"><img title="ROBLOX is kid-safe!" src="/images/COPPASeal-125x125.jpg" border="0"></a>
            </div>
            <div id="PrivPolicy" style="font-size:large;margin-left: 130px;">
                <a id="ctl00_cphRoblox_RobloxAtAGlanceLoginView_RobloxAtAGlance_Anonymous_hlPrivacyPolicy" href="info/Privacy.aspx" style="display:inline-block;">Privacy Policy</a>
                <a id="ctl00_cphRoblox_RobloxAtAGlanceLoginView_RobloxAtAGlance_Anonymous_hlTruste" href="/" style="display:inline-block;"><img src="/images/truste_seal_kids.gif" border="0"></a>
            </div>
        </div>
            <div id="ctl00_cphRoblox_CoolPlaces_FlashContent">
                <?php
                $params = [];
                for ($i = 0; $i < min(5, count($coolGames)); $i++) {
                    $params[] = "place" . ($i + 1) . "=" . urlencode($coolGames[$i]['id']);
                }
                $params[] = "subdomain=" . urlencode("http://www.ryblox.xyz");
                $params[] = "v=2";

                if (isset($_USER) && $_USER["theme"] === "rbx") {
                    $swfUrl = "/images/CoolPlacesRBX08.swf?" . implode("&", $params);
                } else {
                    $swfUrl = "/images/CoolPlaces.swf?" . implode("&", $params);
                }
                ?>
                <embed src="<?= htmlspecialchars($swfUrl) ?>" width="900" height="100">
                <div id="ctl00_cphRoblox_CoolPlaces_ie6_peekaboo" style="clear: both"></div>
            </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>