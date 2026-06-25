<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reason = $_POST["reason"];
    $user = $_POST["user"];

    $payload = [
        "embeds" => [[
            "title" => "Reporting $user"
        ]]
    ];
    if ($reason === "other") {
        $otherReason = $_POST["otherReason"];
    } else {

    }
}
?>
<title>In Game Chat Report</title>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>
<h2>Who are you trying to report?</h2>
<script src="https://code.jquery.com/jquery-1.7.0.js" integrity="sha256-fBiF7IYg9AoQ0EWUjT+fe4+cT3vS/x3ftIap8n6V4+M=" crossorigin="anonymous"></script>
<form method="post">
    <input name="user" required class="TextBox" placeholder="Type the persons username here.">
    <br><br>
    <select id="rbxInputReason" name="reason" required>
        <option value="profanity" selected>Profanity</option>
        <option value="privateinfo">Asking for Private Information</option>
        <option value="cyberbullying">Cyberbullying</option>
        <option value="threatning">Threatning to do something to themselves or you.</option>
        <option value="other">Other</option>
    </select>
    <br><br>
    <div id="displayOtherArea" style="display: none;">
        <textarea style="width:400px;height:115px" class="MultilineTextBox" name="otherReason" placeholder="State a more detailed reason here."></textarea>
        <br><br>
    </div>
    <button type="submit" class="Button">Submit Report</button>
</form>
<script>
$('#rbxInputReason').change(function() {
    var value = $(this).val();

    if (value === "other") {
        $("#displayOtherArea").show();
    } else {
        $("#displayOtherArea").hide();
    }
});
</script>