<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$switch_url = $_SESSION['switch_url'];
$ap_mac = $_SESSION['ap_mac'];
$client_mac = $_SESSION['client_mac'];
$wlan = $_SESSION['wlan'];
$redirect = $_SESSION['redirect'];
$statusCode = $_SESSION['statusCode'];

if ($statusCode == 1) {
    $statusMessage = "You are already logged in.";
}
elseif ($statusCode == 2) {
    $statusMessage = "You are not configured to authenticate against this web portal.";
}
elseif ($statusCode == 3) {
    $statusMessage = "The email address specified cannot be used at this time. Perhaps the username is already logged into the system?";
}
elseif ($statusCode == 4) {
    $statusMessage = "This account has been excluded. Please contact the administrator.";
}
elseif ($statusCode == 5) {
    $statusMessage = "Invalid email or password. Please try again.";
}

$host_ip = $_SERVER['HOST_IP'];
$db_user = $_SERVER['DB_USER'];
$db_pass = $_SERVER['DB_PASS'];
$db_name = $_SERVER['DB_NAME'];
$db2_name = $_SERVER['DB_RADIUS_NAME'];

date_default_timezone_set("America/Los_Angeles");
$last_updated = date("Y-m-d H:i:s");

$con = mysqli_connect($host_ip, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
    echo "Failed to connect to SQL: " . mysqli_connect_error();
}

$con2 = mysqli_connect($host_ip, $db_user, $db_pass, $db2_name);

if (mysqli_connect_errno()) {
    echo "Failed to connect to SQL: " . mysqli_connect_error();
}

if ($_SESSION["user_type"] == "new") {
    mysqli_query($con, "INSERT INTO `users` (mac, last_updated) VALUES ('$client_mac', '$last_updated')");
    mysqli_query($con2, "INSERT INTO `radcheck` (username,attribute,op,value) VALUES ('$client_mac', 'Cleartext-Password', ':=','$client_mac')");
} else {
    mysqli_query($con, "UPDATE `users` SET `last_updated` = '$last_updated' WHERE `mac` = '$client_mac'");
}

mysqli_close($con);
mysqli_close($con2);

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Pragma" content="no-cache">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Web Authentication</title>
</head>

<body>

<form action="<?php echo $switch_url; ?>" id="loginForm" method="post" style="display:none">
    <?php if ($statusMessage) echo "<p>{$statusMessage}</p>"; ?>
    <input type="text" name="username" value="<?php echo $client_mac; ?>" type="hidden"/>
    <input type="password" name="password" value="<?php echo $client_mac; ?>" type="hidden"/>
    <input name="buttonClicked" size="16" maxlength="15" value="4" type="hidden">
    <input type="submit" value="Log in" />
</form>

<script>
    window.onload = function(){
        document.forms['loginForm'].submit();
    }
</script>

</body>
</html>