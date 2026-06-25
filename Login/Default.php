<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$error = null;
$returnUrl = $_GET["ReturnUrl"] ?? "/";
if (!str_starts_with($returnUrl, "/")) {
    $returnUrl = "/";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($username)) {
        $error = "Username cannot be empty.";
    } elseif (empty($password)) {
        $error = "Password cannot be empty.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "Invalid username or password.";
        } else {
            $ip = getUserIP();
            $stmt = $db->prepare("UPDATE users SET last_seen = ?, last_seen_time = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute(["Website",$user["id"]]);
            $_SESSION["user_id"] = $user["id"];

            header("Location: " . $returnUrl);
            exit;
        }
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/headless.php"; ?>
    <div id="Body">
        <div id="FrameLogin" style="margin: 50px auto 150px auto; width: 500px; border: black thin solid; padding: 21px; z-index: 8; background-color: white;">
            <div id="PaneNewUser">
                <h3>New User?</h3>
                <p>You need an account to play RYDENYTE.</p>
                <p>If you aren't a RYDENYTE member then <a id="ctl00_cphRoblox_HyperLink1" href="/Login/New.aspx">register</a>. It's easy and we do <em>not</em> share your personal information with anybody.</p>
            </div>
            
            <div id="PaneLogin">
                <h3>Log In</h3>
                <?php if ($error): ?>
                <h3 style="color: red;"><?= htmlspecialchars($error) ?></h3>
                <?php endif; ?>
                <div class="AspNet-Login">
                    <form method="POST" action="">
                        <div class="AspNet-Login-UserPanel">
                            <label for="ctl00_cphRoblox_lRobloxLogin_UserName" class="TextboxLabel"><em>U</em>ser Name:</label>
                            <input type="text" id="ctl00_cphRoblox_lRobloxLogin_UserName" name="username" value="" accesskey="u" required="">
                        </div>
                        
                        <div class="AspNet-Login-PasswordPanel">
                            <label for="ctl00_cphRoblox_lRobloxLogin_Password" class="TextboxLabel"><em>P</em>assword:</label>
                            <input type="password" id="ctl00_cphRoblox_lRobloxLogin_Password" name="password" value="" accesskey="p" required="">
                        </div>
                        
                        <div class="AspNet-Login-SubmitPanel">
                            <input type="submit" value="Log In" id="ctl00_cphRoblox_lRobloxLogin_LoginButton" name="ctl00$cphRoblox$lRobloxLogin$LoginButton">
                        </div>
                        
                        <div class="AspNet-Login-PasswordRecoveryPanel">
                            <a href="#" title="Forgot your password?">
                            <br>Forgot Password?<br>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>