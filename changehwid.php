<?php
$servername = "localhost";
$name = "user";
$username = "pass";
$password = "demo";

$key = $_GET["key"];
$hwid = $_GET["hwid"];

$conn = new mysqli($servername, $username, $password, $name);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$ips = getUserIpAddr();

$sql = "UPDATE whitelistbot SET hwid = '$hwid', ip = '$ips' WHERE userkey = '$key'";

if ($key and $hwid) {
if ($conn->query($sql) === TRUE) {
  echo "Hi Guys!";
}

?>