<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$userId = $_GET["userID"] ?? null;

if (empty($_USER)) {
    header("Location: /Login/Default.aspx");
    exit;
}

if (empty($userId)) {
    die("user invalid");
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$reporting = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reporting) {
    die("user not found");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reason = trim($_POST["reason"] ?? "");

    if (!empty($reason)) {

        $webhook = "YOUR_DISCORD_WEBHOOK_HERE";

        $payload = [
            "embeds" => [[
                "title" => "Reporting {$reporting['username']}",
                "color" => 16711680,
                "fields" => [
                    [
                        "name" => "Reported By",
                        "value" => $_USER["username"],
                        "inline" => true
                    ],
                    [
                        "name" => "Reported User",
                        "value" => $reporting["username"],
                        "inline" => true
                    ],
                    [
                        "name" => "User ID",
                        "value" => (string)$reporting["id"],
                        "inline" => true
                    ],
                    [
                        "name" => "Reason",
                        "value" => $reason
                    ]
                ],
                "thumbnail" => [
                    "url" => "https://www.ryblox.xyz/Avatar.ashx?userId=" . $reporting["id"]
                ]
            ]]
        ];

        $ch = curl_init($webhook);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true
        ]);

        curl_exec($ch);
        curl_close($ch);

        header("Location: /users/" . $reporting["id"] . "/profile");
        exit;
    }
}
?>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>Report Abuse - Rydenyte</title>
<link rel="stylesheet" href="/CSS/AbuseReport.css">

<div id="Container">

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">

        <h2>
            Report Abuse on <?= htmlspecialchars($reporting["username"]) ?>
        </h2>

        <div>
            <img src="/Thumbs/Avatar.ashx?username=<?= $reporting["username"] ?>&format=Png&x=100&y=100" alt="<?= htmlspecialchars($reporting["username"]) ?>" style="border:1px solid;">
        </div>
        <br>
        <form method="post">
            <textarea name="reason" placeholder="Input your reason here." required style="width:600px;height:150px;"></textarea>
            <br><br>
            <button type="submit">Submit</button>
        </form>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>