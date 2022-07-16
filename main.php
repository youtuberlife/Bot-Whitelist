<?php
#Make sure this file is named exactly "main.php" or it wont work (yes it must be lowercase)

function prettyPrint( $json ) # I didn't make this function here, I found it on stackoverflow because I didn't want the json format to be super ugly and hard to read. Everything else I made though.
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

# Made By ComoEsteban on v3rm or Formula#6704

if(file_exists("data.json") == false){
    $data_file = fopen("data.json","w");
    fwrite($data_file,"[]");
    fclose($data_file);
}
if(file_exists("buyers.json") == false){
    $data_file = fopen("buyers.json","w");
    fwrite($data_file,"[]");
    fclose($data_file);
}

$condition = $_GET["condition"];

$file = fopen("data.json","r");
$data = json_decode(fread($file,filesize("data.json")),true);
fclose($file);

if($condition == "start"){
    $discord = intval($_GET["discord"]);
    $userid = intval($_GET["userid"]);
}
if($condition == "exists"){
    $filename = $_GET["file"];
    $returndata = array();
    if(file_exists($filename)){
        $returndata["exists"] = True;
    }else{
        $returndata["exists"] = False;
    }
    echo(json_encode($returndata));
}
if($condition == "create"){
    $filename = $_GET["name"];
    $extension = $_GET["extension"];
    $file = fopen($filename.$extension,"w");
    if($filename == "asset_data"){
        fwrite($file,"[]");
    }
    fclose($file);
}
if($condition == "updateid"){
    $id = intval($_GET["id"]);
    $file = fopen("asset_data.json","r");
    $filedata = json_decode(fread($file,filesize("asset_data.json")),true);
    fclose($file);
    $array_to_return = array();
    if($filedata && array_key_exists("assetid",$filedata)){
        $array_to_return["old"] = $filedata["assetid"];
    }else{
        $array_to_return["old"] = null;
    }
    echo(json_encode($array_to_return));
    $new = array();
    $new["assetid"] = $id;
    $file = fopen("asset_data.json","w");
    fwrite($file,json_encode($new));
    fclose($file);
}
if($condition == "newpurchase"){
    $userid = intval($_GET["userid"]);
    $discord = intval($_GET["discord"]);
    
    $file = fopen("data.json","r");
    $filedata = json_decode(fread($file,filesize("data.json")),true);
    fclose($file);
    
    $data = array();
    $data["robloxid"] = $userid;
    $data["discord"] = $discord;
    $data["complete"] = false;
    $data["purchase id"] = null;
    
    array_push($filedata,$data);
    
    $file = fopen("data.json","w");
    fwrite($file,json_encode($filedata));
    fclose($file);
}
if($condition == "purchasecomplete"){
    $discord = intval($_GET["discord"]);
    $key = hash("md5",$_GET["key"]);
    
    $file = fopen("data.json","r");
    $data = json_decode(fread($file,filesize("data.json")),true);
    fclose($file);
    
    $found = false;
    
    $count = 0;
    foreach($data as $v){
        print($v["discord"]);
        if($v["discord"] == $discord){
            unset($data[$count]);
        }
        $count = $count+1;
    }
    
    $file = fopen("data.json","w");
    fwrite($file,json_encode($data));
    fclose($file);
    
    $file = fopen("buyers.json","r");
    $filedata = json_decode(fread($file,filesize("buyers.json")),true);
    fclose($file);
    
    if($filedata == NULL){
        $filedata = array();
    }
    
    $data = array();
    
    $data["discord"] = $discord;
    $data["key"] = $key;
    $data["purchase time"] = time();
    $data["ip"] = null;
    $data["last execution"] = null;
    $data["last ip change"] = null;
    
    array_push($filedata,$data);
    
    $file = fopen("buyers.json","w");
    fwrite($file,prettyPrint(json_encode($filedata)));
    fclose($file);
}
if($condition == "checkwhitelist"){
    $key = hash("md5",$_GET["key"]);
    $ip = hash("md5",$_SERVER["REMOTE_ADDR"]);
    $time = intval($_GET["time"]);
    $num = intval($_GET["n"]);
    
    $userdata = null;
    
    $file = fopen("buyers.json","r");
    $data = json_decode(fread($file,filesize("buyers.json")),true);
    
    $count = 0;
    foreach($data as $v){
        if($v["key"] == $key){
            $userdata = $v;
            break;
        }
        $count = $count+1;
    }
    
    if($userdata == null){
        echo($time*$num);
        return;
    }
    
    if($userdata["ip"] == null){
        $data[$count]["ip"] = $ip;
        $data[$count]["last ip change"] = time();
    }else{
        if($userdata["ip"] != $ip){
            $ipchangeseconds = time()-$userdata["last ip change"];
            if($ipchangeseconds < 3600){
                echo(strval(3600-$ipchangeseconds));
                return;
            }else{
                $data[$count]["ip"] = $ip;
                $data[$count]["last ip change"] = time();
            }
        }
    }
    
    $data[$count]["last execution"] = time();
    $file = fopen("buyers.json","w");
    fwrite($file,prettyPrint(json_encode($data)));
    echo($time-$num);
}
if($condition == "hasbought"){
    $userid = intval($_GET["robloxid"]);
    $purchaseid = $_GET["purchaseid"];
    $file = fopen("data.json","r");
    $data = json_decode(fread($file,filesize("data.json")),true);
    fclose($file);
    
    $count = 0;
    foreach($data as $v){
        if($v["robloxid"] == $userid && $v["complete"] == false){
            $data[$count]["complete"] = true;
            $data[$count]["purchase id"] = $purchaseid;
            break;
        }
        $count = $count+1;
    }
    
    $file = fopen("data.json","w");
    fwrite($file,json_encode($data));
    fclose($file);
}
if($condition == "cancelled"){
    $discord = intval($_GET["discord"]);
    
    $file = fopen("data.json","r");
    $data = json_decode(fread($file,filesize("$data.json")),true);
    fclose($file);
    
    $count = 0;
    foreach($data as $v){
        if($v["discord"] == $discord){
            unset($data[$count]);
        }
        $count = $count+1;
    }
    
    if($data == null){
        $data = array();
    }
    
    $file = fopen("data.json","w");
    fwrite($file,json_encode($data));
    fclose($file);
}
?>