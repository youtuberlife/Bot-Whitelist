<?php
$servername = "localhost";
$name = "user";
$username = "pass";
$password = "demo";

$key = $_GET["key"];

$hwidTypes = array("Syn-Fingerprint", "Exploit-Guid", "Proto-User-Identifier", "Sentinel-Fingerprint");

$conn = new mysqli($servername, $username, $password, $name);

$result = $conn->query("SELECT userkey,hwid,Blacklisted,Reason FROM whitelistbot WHERE userkey = '$key'");

while($row = $result->fetch_assoc()){
	$database = array(
		array(
			"key" => $row["userkey"],
			"hwid" => $row["hwid"],
			"blacklist" => $row["Blacklisted"],
			"reason" => $row["Reason"],
		),
	);
}

function fetch_hwid($headers) {
	global $hwidTypes;

	$hwid = NULL;
	$count = 0;
	
	foreach($hwidTypes as $key => $header) {
		if (isset($headers[$header])) {
			$count = $count + 1;
			$hwid = $headers[$header];
		};
	}

	if ($count > 1) {
		$hwid = NULL;
	}

	return $hwid;	
}

if (isset($_GET["key"])) {
	$hwid = fetch_hwid(getallheaders());
	if (!is_null($hwid)) {
		$didUserPass = FALSE;
        $errorMsg = "Authentication Failed";
        $random = $_GET["random"];
		foreach($database as $_ => $user) {
			$stored_blacklist = $user["blacklist"];
			$reason = $user["reason"];
			$stored_key = $user["key"];
            $stored_hwid = $user["hwid"];
            $stored_random = $random;
            $random_matches = ($stored_random == $random);
            $blacklist_matches = ($stored_blacklist == "True");
	        $key_matches = ($stored_key == $key);
            $hwid_matches = ($stored_hwid == $hwid or "Unknown");
			if ($key_matches and $hwid_matches and $random_matches) {
                $message = json_encode(array('Message'=> "Authentication Success", 'Key'=> "$stored_key", 'Random'=> "$random", 'Hwid'=> "$stored_hwid", 'Blacklist'=> "$stored_blacklist", 'Reason' => "$stored_reason"));
                $didUserPass = TRUE;
                echo($message);
            } elseif ($key_matches and !$hwid_matches) {
                $errorMsg = "Invalid Hwid";
            }
        }
		if (!$didUserPass) {
			echo($errorMsg);
		};
	} else {
		die("No Hwid Supplied");
	}
} else {
	die("Invalid Key");
}

?>
