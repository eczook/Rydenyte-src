<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

$id = $_GET["id"] ?? null;

if (empty($id)) {
    die("invalid id");
}

$stmt = $db->prepare("SELECT * FROM ads WHERE id = ?");
$stmt->execute([$id]);
$ad2 = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad2) {
    die("Ad not found");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reason = trim($_POST["reason"] ?? "");

    if (!empty($reason)) {
        $webhook = "https://discord.com/api/webhooks/1516182917309534440/15J3XSwXNj0Tzvs326CMTycaMpVw_EXkET-6FVyuXmJl1zFVbHCsRomuehdIAwZhLXBq";

        $payload = [
            "embeds" => [[
                "title" => "Reporting {$ad2['alt']}",
                "fields" => [
                    [
                        "name" => "Reported By",
                        "value" => $_USER["username"],
                        "inline" => true
                    ],
                    [
                        "name" => "Creator",
                        "value" => (string)$ad2["creator_id"],
                        "inline" => true
                    ],
                    [
                        "name" => "Reason",
                        "value" => $reason
                    ]
                ],
                "image" => [
                    "url" => "https://www.ryblox.xyz/images/UserAds/" . $ad2["filename"]
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

        header("Location: /Default.aspx");
    }
}
?>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title>Rydenyte - Report Abuse on <?= htmlspecialchars($ad2["alt"]) ?></title>

<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>

    <div id="Body">
        <h2>Why do you want to report "<?= htmlspecialchars($ad2["alt"]) ?>"?</h2>
        <div><img src="/images/UserAds/<?= htmlspecialchars($ad2["filename"]) ?>" alt="<?= htmlspecialchars($ad2["alt"]) ?>" style="border:1px solid;"></div>
        <br>

        <form method="post">
            <textarea
                name="reason"
                placeholder="Input your reason here."
                required
                style="width:600px;height:150px;"
            ></textarea>
            <br><br>
            <button type="submit">Submit</button>
        </form>

    </div>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>