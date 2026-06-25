<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$error = null;

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $passwordConfirm = trim($_POST["passwordconfirm"] ?? "");
    $email = trim($_POST["EMail"] ?? "");
    $inviteKey = trim($_POST["InviteKey"] ?? "");
    $ageGroup = $_POST["agegroup"] ?? "1";
    $chatMode = $_POST["chatmode"] ?? "false";

	if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
		die("Invalid CSRF token");
	}

	$turnstileToken = $_POST['cf-turnstile-response'] ?? '';

	$verify = file_get_contents(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		false,
		stream_context_create([
			'http' => [
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query([
					'secret'   => '0x4AAAAAADkIoCbIvREcnsj2lAmUKxw5ViY',
					'response' => $turnstileToken,
					'remoteip' => $_SERVER['REMOTE_ADDR']
				])
			]
		])
	);

	$verifyResult = json_decode($verify, true);

	if (empty($verifyResult['success'])) {
		$error = "Please complete the Cloudflare verification.";
	}

    if (!$username || !$password || !$passwordConfirm || !$inviteKey) {
        $error = "Please fill in all required fields.";
    }

    if ($ageGroup === "1") {
        $error = "You cant be under 13 to play.";
    }

    if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
        $error = "Username must be 3-20 characters, letters and numbers only.";
    }
    
    if (strlen($password) < 4 || strlen($password) > 50) {
        $error = "Password must be between 4 and 50 characters.";
    }

    if ($password !== $passwordConfirm) {
        $error = "Passwords do not match.";
    }

    $check = $db->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);

    if ($check->fetch()) {
        $error = "Username already taken.";
    }
	
    $invite = $db->prepare("SELECT * FROM invite_keys WHERE `key` = ? AND used = 0");
    $invite->execute([$inviteKey]);
    $inviteRow = $invite->fetch(PDO::FETCH_ASSOC);

    if (!$inviteRow) {
        $error = "Invalid or already used invite key: $inviteKey";
    }

    if (!$error) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $auth = generateAuthToken(10);

		function hash_ip($ip) {
			$salt = "RYDENYTEMasterHashKey";
			return hash("sha256", $ip . $salt);
		}

		$ipHash = hash_ip(getUserIP());

        $insert = $db->prepare("
			INSERT INTO users (username, password, ip_address, email, auth, chat_mode, created_at)
			VALUES (?, ?, ?, ?, ?, ?, NOW())
		");

		$insert->execute([
			$username,
			$hashedPassword,
			$ipHash,
			$email,
			$auth,
			$chatMode === "true" ? 1 : 0,
		]);

        $userId = $db->lastInsertId();
		$checkIp = $db->prepare("SELECT id FROM users WHERE ip_address = ? AND id != ?");
		$checkIp->execute([$ipHash, $userId]);

		if ($checkIp->fetch()) {
			$ban = $db->prepare("
				INSERT INTO bans (user_id, ban_type, reason, issued_at)
				VALUES (?, 'permanent', 'Alt account detected (same IP)', NOW())
			");
			$ban->execute([$userId]);

		}
		$placeName = $username . "'s Place";

		$createPlace = $db->prepare("
			INSERT INTO games (asset_id, name, description, creator_id, access)
			VALUES (?, ?, ?, ?, ?)
		");

		$welcomeMessage = $db->prepare("
			INSERT INTO messages 
			(sender_id, recipient_id, subject, body, is_read, is_friend_request, created_at)
			VALUES (?, ?, ?, ?, 0, 0, NOW())
		");

		$welcomeMessage->execute([
			1,
			$userId,
			"Welcome to RYDENYTE!",
			"Dear RYDENYTE User:
			Welcome to RYDENYTE! We are constantly working to make RYDENYTE a fun, safe, creative place for people of all ages.

			We update RYDENYTE regularly, so be sure to visit our NEWS section to find out about all the exciting changes.

			If you have questions about how something works, our HELP section is a great place to start. It's maintained by the RYDENYTE community for the RYDENYTE community. There's a ton of helpful information, including tutorials and answers to Frequently Asked Questions (FAQs).

			Finally, please feel free to post your comments and suggestions in the RYDENYTE forums.

			Thank you!"
		]);

		$assetDir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";
		$sourceAsset = $assetDir . "/1";

		$files = array_filter(scandir($assetDir), function ($file) {
			return is_numeric($file);
		});

		$maxId = 1;
		foreach ($files as $file) {
			if ((int)$file > $maxId) {
				$maxId = (int)$file;
			}
		}

		$newAssetId = $maxId + 1;
		$destinationAsset = $assetDir . "/" . $newAssetId;
		
		if (file_exists($sourceAsset)) {
			copy($sourceAsset, $destinationAsset);
		}
		$createPlace->execute([$newAssetId,$placeName, "Welcome to " . $placeName . " On RYDENYTE!",$userId,"public"]);
		$lastInsert = $db->lastInsertId();
		file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderPlace.ashx?placeId=$lastInsert");

        $db->prepare("UPDATE invite_keys SET used = 1, used_by = ? WHERE id = ?")->execute([$userId, $inviteRow["id"]]);

		file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderCharacter.ashx?userId=$userId");

        $_SESSION["user_id"] = $userId;
        header("Location: /Games.aspx");
        exit;
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<link rel="stylesheet" href="/CSS/AllCSS.ashx">
<div id="Container">
  <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/headless.php"; ?>
  <div id="Body">
    <div id="Registration">
			<form id="ctl00_cphRoblox_upAccountRegistration" method="post" action="">
					<input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
					<h2>Sign Up and Play</h2>
					<h3>Step 1 of 2: Create Account</h3>
					<?php if ($error): ?>
					<p style="text-align: center;color:red;"><?= $error ?></p>
					<?php endif; ?>
					<div id="EnterAgeGroup">
						<fieldset title="Provide your age-group">
							<legend>Provide your age-group</legend>
							<div class="Suggestion">
								This will help us to customize your experience.  Users under 13 years will only be shown pre-approved images.
							</div>
							<div class="AgeGroupRow">
								<span id="ctl00_cphRoblox_rblAgeGroup"><input id="ctl00_cphRoblox_rblAgeGroup_0" type="radio" name="agegroup" value="1" checked="checked" tabindex="5"><label for="ctl00_cphRoblox_rblAgeGroup_0">Under 13 years</label><br><input id="ctl00_cphRoblox_rblAgeGroup_1" type="radio" name="agegroup" value="2" tabindex="5"><label for="ctl00_cphRoblox_rblAgeGroup_1">13 years or older</label></span>
							</div>
						</fieldset>
					</div>
					<div id="EnterUsername">
						<fieldset title="Choose a name for your RYDENYTE character">
							<legend>Choose a name for your RYDENYTE character</legend>
							<div class="Suggestion">
								Use 3-20 alphanumeric characters: A-Z, a-z, 0-9, no spaces
							</div>
							<div class="Validators">
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
							</div>
							<div class="UsernameRow">
								<label for="ctl00_cphRoblox_UserName" id="ctl00_cphRoblox_UserNameLabel" class="Label">Character Name:</label>&nbsp;<input name="username" type="text" id="ctl00_cphRoblox_UserName" tabindex="1" class="TextBox">
							</div>
						</fieldset>
					</div>
					<div id="EnterPassword">
						<fieldset title="Choose your RYDENYTE password">
							<legend>Choose your RYDENYTE password</legend>
							<div class="Suggestion">
								4-10 characters, no spaces
							</div>
							<div class="Validators">
								<div></div>
								<div></div>
								<div></div>
								<div></div>
							</div>
							<div class="PasswordRow">
								<label for="ctl00_cphRoblox_Password" id="ctl00_cphRoblox_LabelPassword" class="Label">Password:</label>&nbsp;<input name="password" type="password" id="ctl00_cphRoblox_Password" tabindex="2" class="TextBox">
							</div>
							<div class="ConfirmPasswordRow">
								<label for="ctl00_cphRoblox_TextBoxPasswordConfirm" id="ctl00_cphRoblox_LabelPasswordConfirm" class="Label">Confirm Password:</label>&nbsp;<input name="passwordconfirm" type="password" id="ctl00_cphRoblox_TextBoxPasswordConfirm" tabindex="3" class="TextBox">
							</div>
						</fieldset>
					</div>
					<div id="EnterChatMode">
						<fieldset title="Choose your chat mode">
							<legend>Choose your chat mode</legend>
							<div class="Suggestion">
								All in-game chat is subject to profanity filtering and moderation.  For enhanced chat safety, choose SuperSafe Chat; only chat from pre-approved menus will be shown to you.
							</div>
							<div class="ChatModeRow">
								<span id="ctl00_cphRoblox_rblChatMode"><input id="ctl00_cphRoblox_rblChatMode_0" type="radio" name="chatmode" value="false" checked="checked" tabindex="6"><label for="ctl00_cphRoblox_rblChatMode_0">Safe Chat</label><br><input id="ctl00_cphRoblox_rblChatMode_1" type="radio" name="chatmode" value="true" tabindex="6"><label for="ctl00_cphRoblox_rblChatMode_1">SuperSafe Chat</label></span>
							</div>
						</fieldset>
					</div>
					<div id="EnterEmail">
						<fieldset title="Provide your email address">
							<legend>Provide your email address</legend>
							<div class="Suggestion">
								This will allow you to recover a lost password
							</div>
							<div class="Validators">
								<div></div>
								<div></div>
								<div></div>
							</div>
							<div class="EmailRow">
								<label for="ctl00_cphRoblox_TextBoxEMail" id="ctl00_cphRoblox_LabelEmail" class="Label">Your Email:</label>&nbsp;<input name="EMail" type="text" id="ctl00_cphRoblox_TextBoxEMail" tabindex="4" class="TextBox">
							</div>
						</fieldset>
					</div>
					<div id="EnterEmail">
						<fieldset title="Provide your Invite key">
							<legend>Provide your Invite Key</legend>
							<div class="Suggestion">You need an invite key to register on RYDENYTE.</div>
							<div class="Validators">
								<div></div>
								<div></div>
								<div></div>
							</div>
							<div class="EmailRow">
								<label for="ctl00_cphRoblox_TextBoxEMail" id="ctl00_cphRoblox_LabelEmail" class="Label">Your Invite Key:</label>&nbsp;<input name="InviteKey" type="text" id="ctl00_cphRoblox_TextBoxEMail" tabindex="4" class="TextBox">
							</div>
						</fieldset>
					</div>
					<div id="EnterEmail">
						<fieldset title="Verify you are human">
							<legend>Verification</legend>
							<div class="Suggestion">Please complete the verification below.</div>
							<div class="Validators">
								<div></div>
								<div></div>
								<div></div>
							</div>
							<div class="EmailRow">
								<div class="cf-turnstile" data-sitekey="0x4AAAAAADkIoP5DJki2Ee7A">
								</div>
							</div>
						</fieldset>
					</div>
					<div class="Confirm">
						<button type="submit" name="ctl00$cphRoblox$ButtonCreateAccount" type="submit" id="ctl00_cphRoblox_ButtonCreateAccount" tabindex="5" class="BigButton">Register</button>
					</div>
      </div>
    </form>
    <div id="Sidebars">
      <div id="AlreadyRegistered">
        <h3>Already Registered?</h3>
        <p>If you just need to login, go to the <a id="ctl00_cphRoblox_HyperLinkLogin" href="Default.aspx?ReturnUrl=%2f">Login</a> page.</p>
        <p>If you have already registered but you still need to download the game installer, go directly to <a id="ctl00_cphRoblox_HyperLinkDownload" href="/web/20070804083927/http://roblox.com/Install/Default.aspx?ReturnUrl=%2f">download</a>.</p>
      </div>
      <div id="TermsAndConditions">
        <h3>Terms &amp; Conditions</h3>
        <p>Registration does not provide any guarantees of service. See our <a id="ctl00_cphRoblox_HyperLinkToS" href="/Info/TermsOfService.aspx" target="_blank">Terms of Service</a> and <a id="ctl00_cphRoblox_HyperLinkEULA" href="/web/20070804083927/http://roblox.com/Info/EULA.htm" target="_blank">Licensing Agreement</a> for details.</p>
        <p>RYDENYTE will not share your email address with 3rd parties. See our <a id="ctl00_cphRoblox_HyperLinkPrivacy" href="/Info/Privacy.aspx" target="_blank">Privacy Policy</a> for details.</p>
      </div>
    </div>
  </div>
  <div style="clear: both;"></div>
  <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>