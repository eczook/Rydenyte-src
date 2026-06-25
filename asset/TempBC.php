<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Content-Type: application/xml");
?>
<?xml version="1.0" encoding="utf-8"?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
    <External>null</External>
    <External>nil</External>
    <Item class="BodyColors">
        <Properties>
            <int name="HeadColor">1</int>
            <int name="LeftArmColor">1</int>
            <int name="LeftLegColor">1</int>
            <string name="Name">Body Colors</string>
            <int name="RightArmColor">1</int>
            <int name="RightLegColor">1</int>
            <int name="TorsoColor">1</int>
            <bool name="archivable">true</bool>
        </Properties>
    </Item>
</roblox>