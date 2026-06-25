<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/core/config.php";
session_start();

$currentPage = $_SERVER['REQUEST_URI'];
$allowedPages = [
    '/Login/New.aspx',
    '/Login/Default.aspx'
];

if (!empty($_SESSION["user_id"])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $_USER = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT *
        FROM bans
        WHERE user_id = ?
        ORDER BY issued_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_USER["id"]]);
    $ban = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ban) {
        $active =
            $ban["ban_type"] === "warning" ||
            $ban["ban_type"] === "permanent" ||
            (
                $ban["ban_type"] === "temporary" &&
                !empty($ban["expires_at"]) &&
                strtotime($ban["expires_at"]) > time()
            );

        if ($active) {

            $allowedBanPages = [
                '/MembershipNotApproved.aspx',
                '/Login/New.aspx',
                '/Login/Default.aspx'
            ];

            if (!in_array(parse_url($currentPage, PHP_URL_PATH), $allowedBanPages)) {
                header("Location: /MembershipNotApproved.aspx?ID=".$_USER["id"]);
                exit;
            }
        }
    }

    $today = date("Y-m-d");

    if ($_USER["last_daily_tix"] != $today) {
        $stmt = $db->prepare("
            UPDATE users
            SET tix = tix + 20,
                last_daily_tix = ?,
                award = award + 1
            WHERE id = ?
        ");
        $stmt->execute([$today, $_USER["id"]]);

        $_USER["tix"] += 20;
        $_USER["last_daily_tix"] = $today;
    }

    function hash_ip($ip) {
		$salt = "RYDENYTEMasterHashKey";
		return hash("sha256", $ip . $salt);
	}

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] 
    ?? $_SERVER['REMOTE_ADDR'] 
    ?? null;

    if ($ip && strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    if ($ip) {
        $stmt = $db->prepare("UPDATE users SET ip_address = ? WHERE id = ?");
        $stmt->execute([
            hash_ip($ip),
            $_USER["id"]
        ]);
    }
} else {
    if (!in_array(parse_url($currentPage, PHP_URL_PATH), $allowedPages)) {
        header("Location: /AnimatedLanding.aspx");
        exit;
    }
}

if (!empty($_USER) && $_USER["theme"] === "rbx") {
    ob_start(function ($buffer) {
        $replacements = ["RYDENYTE" => "ROBLOX","Rydenyte" => "Roblox","rydenyte" => "roblox","Rydenyia" => "Robloxia","RYBUX" => "ROBUX","favicon.ico" => "roblox.ico"];
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $buffer
        );
    });
} else if (!empty($_USER) && $_USER["theme"] === "gubby") {
    ob_start(function ($buffer) {
        $replacements = ["RYDENYTE" => "GUBBYBLOX","Rydenyte" => "Gubbyblox","rydenyte" => "gubbyblox","Rydenyia" => "Gubbloxia","RYBUX" => "GUBUX","favicon.ico" => "gubbyblox.ico"];
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $buffer
        );
    });
}

$db->query("UPDATE users SET online = 0 WHERE last_seen_time < NOW() - INTERVAL 2 MINUTE");
?>