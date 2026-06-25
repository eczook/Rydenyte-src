<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (!isset($_SESSION["user_id"])) {
    die("You must be logged in.");
}

$userId = $_SESSION["user_id"];
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $groupName = trim($_POST["name"] ?? "");
    $groupDescription = trim($_POST["description"] ?? "");

    if ($_USER["robux"] < 5) {
        $error = "Insufficient funds.";
    } elseif (empty($groupName)) {
        $error = "Group name cannot be empty.";
    } else {
        $stmt = $db->prepare("
            INSERT INTO groups (name, creator_id, description)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$groupName, $userId, $groupDescription]);

        $groupId = $db->lastInsertId();

        $stmt = $db->prepare("
            INSERT INTO group_members (group_id, user_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$groupId, $userId]);

        $stmt = $db->prepare("
            UPDATE users
            SET robux = robux - 5
            WHERE id = ?
        ");
        $stmt->execute([$userId]);

        header("Location: /Group.aspx?id=" . $groupId);
        exit;
    }
}
?>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE - Create Group</title>

<div id="Container">

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">

        <h2>Create Group</h2>

        <?php if ($error): ?>
            <div class="Error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirmPayment()">
            <table>
                <tr>
                    <td>
                        <b>Group Name:</b>
                    </td>
                    <td><input type="text" name="name" maxlength="50" required></td>
                </tr>
                <tr>
                    <td><b>Group Description:</b></td>
                    <td><textarea type="text" name="description" maxlength="50" required></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="Button">Create Group</button>
                    </td>
                </tr>
            </table>

        </form>
        <script type="text/javascript">
            function confirmPayment() {
                return confirm("Do you wanna pay 5 RYBUX to create a group?");
            }
        </script>
    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>

</div>

<style>
.Error {
    color: red;
    margin-bottom: 10px;
}

input[type="text"],
textarea {
    width: 300px;
    padding: 4px;
    border: 1px solid #000;
}

textarea {
    resize: vertical;
}
</style>