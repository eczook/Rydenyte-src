<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $blurb = trim($_POST["Blurb"] ?? "");
    $chatmode = ($_POST["chatmode"] ?? "false") === "true" ? 1 : 0;
    $theme = trim($_POST["theme"] ?? "default");

    if (strlen($blurb) > 1000) {
        $blurb = substr($blurb, 0, 1000);
    }

    $stmt = $db->prepare("UPDATE users SET blurb = ?, chat_mode = ?, theme = ? WHERE id = ?");
    $stmt->execute([$blurb,$chatmode,$theme,$_USER["id"]]);

    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit;
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>RYDENYTE - Edit Profile</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <div id="EditProfileContainer">
            <h2>Edit Profile</h2>
            <form method="post">
                <div id="ChatMode">
                    <fieldset title="Update your chat mode">
                        <legend>Update your chat mode</legend>
                        <div class="Suggestion">
                            All in-game chat is subject to profanity filtering and moderation.  For enhanced chat safety, choose SuperSafe Chat; only chat from pre-approved menus will be shown to you.
                        </div>
                        <div class="ChatModeRow">
                            <table id="ctl00_cphRoblox_rblChatMode" border="0">
                                <tbody style="font-size: 12px;color:#555">
                                    <tr>
                                        <td><input id="ctl00_cphRoblox_rblChatMode_0" type="radio" name="chatmode" value="false" <?= ($_USER["chat_mode"] ?? 0) == 0 ? 'checked="checked"' : '' ?> tabindex="2"><label for="ctl00_cphRoblox_rblChatMode_0">Safe Chat</label></td>
                                    </tr>
                                    <tr>
                                        <td><input id="ctl00_cphRoblox_rblChatMode_1" type="radio" name="chatmode" value="true" <?= ($_USER["chat_mode"] ?? 0) == 1 ? 'checked="checked"' : '' ?> tabindex="2"><label for="ctl00_cphRoblox_rblChatMode_1">SuperSafe Chat</label></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>
                <div id="ResetPassword">
                    <fieldset title="Reset your password">
                        <legend>Change your password</legend>
                        <div class="Suggestion">Click the button below to change your password.</div>
                        <div class="ResetPasswordRow">
                            &nbsp;<a id="ctl00_cphRoblox_ChangePassword" href="/Login/ResetPasswordRequest.aspx">Change Password</a>
                        </div>
                    </fieldset>
                </div>
                <div id="EnterEmail">
                    <fieldset title="Update Email Address">
                        <legend>Update Email Address</legend>
                        <div class="Validators">
                            <div><span id="ctl00_cphRoblox_RegularExpressionValidator2" style="color:Red;display:none;">Please enter a valid email address.</span></div>
                            <div><span id="ctl00_cphRoblox_RequiredFieldValidator1" style="color:Red;display:none;">Email is required.</span></div>
                            <div><span id="ctl00_cphRoblox_CustomValidatorEmail" style="color:Red;display:none;">An account with this email address already exists.</span></div>
                        </div>
                        <div class="EmailRow">
                            <label for="ctl00_cphRoblox_TextBoxEMail" id="ctl00_cphRoblox_LabelEmail" class="Label">Email:</label>&nbsp;
                            <input name="ctl00$cphRoblox$TextBoxEMail" type="text" value="" id="ctl00_cphRoblox_TextBoxEMail" tabindex="4" class="TextBox">
                            <br>
                        </div>
                    </fieldset>
                </div>
                <div id="Blurb">
                    <fieldset title="Update your personal blurb">
                        <legend>Update your personal blurb</legend>
                        <div class="Suggestion">
                            Describe yourself here (max. 1000 characters).  Make sure not to provide any details that can be used to identify you outside RYDENYTE.
                        </div>
                        <div class="BlurbRow">
                            <textarea name="Blurb" rows="2" cols="20" id="ctl00_cphRoblox_tbBlurb" tabindex="3" class="MultilineTextBox"><?= htmlspecialchars($_USER["blurb"] ?? "") ?></textarea>
                        </div>
                    </fieldset>
                </div>
                <div id="EnterEmail">
                    <fieldset title="Update your theme">
                        <legend>Update your theme</legend>
                        <div class="Suggestion">
                        This will change your site theme, making it look unique!
                        </div>
                        <br>
                        <div class="EmailRow">
                            <label for="theme" class="Label">Selected theme:</label>
                            <select id="theme" name="theme">
                                <option value="default" <?= ($_USER["theme"] ?? "default") === "default" ? 'selected' : '' ?>>
                                    Regular
                                </option>
                                <option value="dark" <?= ($_USER["theme"] ?? "default") === "dark" ? 'selected' : '' ?>>
                                    Dark
                                </option>
                                <option value="rbx" <?= ($_USER["theme"] ?? "default") === "rbx" ? 'selected' : '' ?>>
                                    ROBLOX
                                </option>
                                <option value="gubby" <?= ($_USER["theme"] ?? "default") === "gubby" ? 'selected' : '' ?>>
                                    GUBBYBLOX
                                </option>
                            </select>
                        </div>
                        <br>
                    </fieldset>
                </div>
                <div class="Buttons">
                    <button type="submit" id="ctl00_cphRoblox_lbSubmit" tabindex="4" class="Button">Update</button>&nbsp;
                    <a href="?cancel=1" id="ctl00_cphRoblox_lbCancel" tabindex="5" class="Button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>