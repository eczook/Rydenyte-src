<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/auth.php";

if (empty($_USER)) {
    header("Location: /Login/Default.aspx");
    exit;
}

$ContentType = $_GET["ContentType"] ?? null;
$title = null;
$t = "";

if (empty($ContentType)) {
    die("No.");
}

if ($ContentType === '2') {
    $title = "T-Shirt Builder";
    $t = "T-Shirt";
} else if ($ContentType === '11') {
    $title = "Shirt Builder";
    $t = "Shirt";
} else if ($ContentType === '12') {
    $title = "Pants Builder";
    $t = "Pants";
} else if ($ContentType === '10') {
    $title = "Model Builder";
    $t = "Models";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if ($ContentType === '2') {
        $name = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filename = pathinfo($name, PATHINFO_FILENAME);

        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            die('Only PNG, JPG, and JPEG files are allowed.');
        }

        $dir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";
        $files = scandir($dir);
        $max = 0;

        foreach ($files as $f) {
            if (is_numeric($f)) {
                $num = (int)$f;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $nextId = $max + 1;
        $xml = 
'<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
    <External>null</External>
    <External>nil</External>
    <Item class="ShirtGraphic" referent="RBX0">
        <Properties>
        <Content name="Graphic">
            <url>http://www.ryblox.xyz/asset/?id='.$nextId.'</url>
        </Content>
        <string name="Name">Shirt Graphic</string>
        <bool name="archivable">true</bool>
        </Properties>
    </Item>
</roblox>';

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $nextId)) {
            file_put_contents($dir . $nextId+1,$xml);
            $stmt = $db->prepare("INSERT INTO catalog (asset_id, creator_id, name, description, price_tix, price_robux,category) VALUES (?,?,?,'',0,0,2)");
            $stmt->execute([$nextId+1,$_USER["id"],$filename]);
            $lastId = $db->lastInsertId();
            file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderItem.ashx?itemId=$lastId");
            header("Location: /Item.aspx?ID=$lastId");
        } else {
            die("Failed to upload.");
        }
    } else if ($ContentType === '11') {
        $name = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filename = pathinfo($name, PATHINFO_FILENAME);

        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            die('Only PNG, JPG, and JPEG files are allowed.');
        }

        $dir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";
        $files = scandir($dir);
        $max = 0;

        foreach ($files as $f) {
            if (is_numeric($f)) {
                $num = (int)$f;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $nextId = $max + 1;
        $xml = '<?xml version="1.0" encoding="utf-8"?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
  <External>null</External>
  <External>nil</External>
  <Item class="Shirt" referent="RBX0">
    <Properties>
      <Content name="ShirtTemplate">
        <url>http://www.ryblox.xyz/asset/?id='.$nextId.'</url>
      </Content>
      <string name="Name">Shirt</string>
      <bool name="archivable">true</bool>
    </Properties>
  </Item>
</roblox>';

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $nextId)) {
            file_put_contents($dir . $nextId+1,$xml);
            $stmt = $db->prepare("INSERT INTO catalog (asset_id, creator_id, name, description, price_tix, price_robux,category) VALUES (?,?,?,'',0,0,?)");
            $stmt->execute([$nextId+1,$_USER["id"],$filename,(int)$ContentType]);
            $lastId = $db->lastInsertId();
            file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderItem.ashx?itemId=$lastId");
            header("Location: /Item.aspx?ID=$lastId");
        } else {
            die("Failed to upload.");
        }
    } else if ($ContentType === '12') {
        $name = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filename = pathinfo($name, PATHINFO_FILENAME);

        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            die('Only PNG, JPG, and JPEG files are allowed.');
        }

        $dir = $_SERVER["DOCUMENT_ROOT"] . "/asset/assets/";
        $files = scandir($dir);
        $max = 0;

        foreach ($files as $f) {
            if (is_numeric($f)) {
                $num = (int)$f;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $nextId = $max + 1;
        $xml = '<?xml version="1.0" encoding="utf-8"?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
  <External>null</External>
  <External>nil</External>
  <Item class="Pants" referent="RBX0">
    <Properties>
      <Content name="PantsTemplate">
        <url>http://www.ryblox.xyz/asset/?id='.$nextId.'</url>
      </Content>
      <string name="Name">Pants</string>
      <bool name="archivable">true</bool>
    </Properties>
  </Item>
</roblox>';

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $nextId)) {
            file_put_contents($dir . $nextId+1,$xml);
            $stmt = $db->prepare("INSERT INTO catalog (asset_id, creator_id, name, description, price_tix, price_robux,category) VALUES (?,?,?,'',0,0,?)");
            $stmt->execute([$nextId+1,$_USER["id"],$filename,(int)$ContentType]);
            $lastId = $db->lastInsertId();
            file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderItem.ashx?itemId=$lastId");
            header("Location: /Item.aspx?ID=$lastId");
        } else {
            die("Failed to upload.");
        }
    } else if ($ContentType === '10') {
        $name = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filename = pathinfo($name, PATHINFO_FILENAME);

        if (!in_array($ext, ['rbxm', 'rbxmx'])) {
            die('Only RBXM and RBXMX files are allowed.');
        }

        $dir = $_SERVER["DOCUMENT_ROOT"] . "/IDE/asset/";
        $files = scandir($dir);
        $max = 0;

        foreach ($files as $f) {
            if (is_numeric($f)) {
                $num = (int)$f;
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $nextId = $max + 1;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir . $nextId)) {

            $stmt = $db->prepare("INSERT INTO models (name, type, skybox, creator_id)VALUES (?, ?, 0, ?)");
            $stmt->execute([$filename, "Free Models", $_USER["id"]]);
            $lastid = $db->lastInsertId();
            file_get_contents("https://www.ryblox.xyz/Thumbs/Renders/renderModel.ashx?id=$lastid");
            header("Location: /IDE/ClientToolbox.aspx");
        } else {
            die("Failed to upload.");
        }
    }
}
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<title><?= htmlspecialchars($title) ?></title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
    <div id="Body">
        <style>
        #Body table
        {
            border: 1px black solid;
        }
        .tablehead
        {
            font-size:16px; font-weight: bold; border-bottom:black 1px solid; width: 100%; background-color: #CCCCCC; color: #222222;
        }
        .tablebody
        {
            font-weight: lighter; background-color: transparent;font-family: Verdana;
        }
        .tablebody a {
            color:blue;
        }
        .tablebody a:hover {
            cursor:pointer;
        }
        th.tablebody {
        text-align: left;
        padding-left: 10px;
    }
    </style>

    <h1><?= htmlspecialchars($title) ?></h1>
    <big>
    <table cellspacing="0px" width="100%">
        <tbody>
            <tr>
                <th class="tablehead">Instructions</th>
            </tr>
            <tr>
                <th class="tablebody">
                    
                    <p>On RYDENYTE, a T-Shirt is a transparent torso adornment with a decal applied to the front surface. To create a T-Shirt:</p>
                    <ol>
                        <li>Click the "Browse" button below.</li>
                        <li>Use the File Explorer that pops up to browse your computer.</li>
                        <li>Find and select the picture that you want to use as the shirt's decal. Most standard images (.png, .bmp, .gif) will work.</li>
                        <li>Finally, click the "Create T-Shirt" button.</li>
                    </ol>
                    <p>The image you selected will be uploaded to RYDENYTE, where we will create a T-Shirt and add it to your inventory. To wear this T-Shirt, simply go to the <a href="/My/Character.aspx">Change Character</a> page, find them in your wardrobe, and click to wear them.</p>
                </th>
            </tr>
        </tbody>
        </table>
    <br>
    <table cellspacing="0px" width="100%">
        <tbody>
            <tr>
                <th class="tablehead">Upload Texture</th>
            </tr>
            <tr>
                <th class="tablebody">
                <center>
                    <form method="post" enctype="multipart/form-data" style="padding:25px;">
                        <input type="file" name="file" id="fileToUpload">
                        <br><br>
                        <input type="submit" name="submit" value="Create <?= htmlspecialchars($t) ?>">
                    </form>
                </center>
                </th>
            </tr>
        </tbody>
        </table>
    </big>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>