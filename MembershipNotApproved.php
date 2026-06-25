<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (empty($_USER)) {
    header("Location: /Login/New.aspx");
    exit;
}

$stmt = $db->prepare("
    SELECT *
    FROM bans
    WHERE user_id = ?
    ORDER BY issued_at DESC
    LIMIT 1
");
$stmt->execute([$_USER["id"]]);
$ban = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reactivate"])) {

    $stmt = $db->prepare("
        DELETE FROM bans
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([
        $ban["id"],
        $_USER["id"]
    ]);

    header("Location: /");
    exit;
}

if (!$ban) {
    header("Location: /");
    exit;
}

$isPermanent = $ban["ban_type"] === "permanent";
$isWarning = $ban["ban_type"] === "warning";

if (!$isPermanent && !$isWarning) {
    $days = ceil(
        (strtotime($ban["expires_at"]) - strtotime($ban["issued_at"]))
        / 86400
    );
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Rydenyte | Disabled Account</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/headless.php"; ?>
    <div id="Body">
        <div style="margin: 150px auto 150px auto; width: 500px; border: black thin solid; padding: 22px;">
            <?php
            if ($ban["ban_type"] === "warning") {
                echo "<h1>Warning</h1>";
            } elseif ($ban["ban_type"] === "permanent") {
                echo "<h1>Account Deleted</h1>";
            } else {
                echo "<h1>Banned for {$days} Days</h1>";
            }
            ?>
            <p>
                Our content monitors have determined that your behavior at RYDENYTE
                has been in violation of our Terms of Service.
                <?=
                $isWarning
                    ? "This is an official warning on your account."
                    : (
                        $isPermanent
                            ? "Your account has been permanently terminated."
                            : "We will terminate your account if you do not abide by the rules."
                    );
                ?>
            </p>
            <p>
                Reported:
                <span style="font-weight:bold">
                    <?= date("n/j/Y g:i:s A", strtotime($ban["issued_at"])) ?>
                </span>

                <br><br>

                Moderator Note:
                <span style="font-weight:bold">
                    <?= htmlspecialchars($ban["moderator_note"]) ?>
                </span>
            </p>
            <div class="blackbox">
                <div class="classified">
                    <?= htmlspecialchars($ban["reason"]) ?>
                </div>

                <span>
                    <?= nl2br(htmlspecialchars($ban["evidence"])) ?>
                </span>
            </div>
            <style class="thestyling" type="text/css">
            .blackbox {
                display: inline-flex;
                align-items: center;
                gap: 12px;

                padding: 4px;
                border: 2px solid;

                font-size: 10px;
            }

            .classified {
                padding: 3px 8px;
                text-align: left;
                border: 2px solid;
                white-space: nowrap;
            }
            </style>
            <p>
                Please abide by the <a href="http://wiki.roblox.com/index.php?title=Community_Guidelines">RYDENYTE Community Guidelines</a> so that RYDENYTE can be fun for users of all ages.
            </p>
            <div id="ctl00_cphRoblox_Panel3">
                <?php if ($isWarning): ?>

                    <p>
                        This warning has been added to your account record.
                    </p>

                <?php elseif ($isPermanent): ?>

                    <p>
                        Your account has been permanently disabled.
                    </p>

                <?php else: ?>

                    <p>
                        Your account has been disabled for <?= $days ?> days.
                        You may reactivate it after
                        <span id="ctl00_cphRoblox_Label6">
                            <?= date("n/j/Y g:i:s A", strtotime($ban["expires_at"])) ?>
                        </span>
                    </p>

                <?php endif; ?>
            </div>
            <?php $canReactivate =
    !$isPermanent &&
    $ban["ban_type"] === "temporary" &&
    !empty($ban["expires_at"]) &&
    strtotime($ban["expires_at"]) <= time(); ?>

            <?php if ($canReactivate || $isWarning): ?>
            <div id="ctl00_cphRoblox_UpdatePanel1" style="text-align:center;">
                <form method="post">
                    <div>
                        <input type="checkbox" id="agreement" name="agreement" required>
                        <label for="agreement">
                            I agree to the Terms of Service
                        </label>
                        <br><br>

                        <button type="submit" name="reactivate">
                            Reactivate Account
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>