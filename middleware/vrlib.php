<?php
require_once("./config_inc.php");
function vr_get_login_token(){
	global $config;
	if(file_exists($config["tmp_login_file"])){
		$token=json_decode(file_get_contents($config["tmp_login_file"]), $assoc = true);
		if(($token["validity"]/1000)>time()){
			return $token["token"];
		}
	}
	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$config["vr_server"]."/suite-api/api/auth/token/acquire");
        //curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));
	$data=array();
	$data["username"]=$config["vr_user"];
	$data["password"]=$config["vr_password"];
	$data_string = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	$result = curl_exec($ch);
	file_put_contents($tmp_login_file,$result);
	$token=json_decode($result, $assoc = true);
	return $token["token"];
}


function vr_post_data($path,$postData){
	global $config;
	$token=vr_get_login_token();
	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$config["vr_server"].$path);
        //curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","Authorization: vRealizeOpsToken $token"));
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
	$result = curl_exec($ch);
	return $result;
}

function generateToken($vrops_objectId){
	global $config;
	$dbconn = pg_connect("host=".$config["pg_host"]." port=".$config["pg_port"]." dbname=".$config["pg_dbname"]." user=".$config["pg_user"]." password=".$config["pg_password"]);
	$token=generateRandomString(32);
	pg_prepare($dbconn, "generateToken", 'INSERT INTO vr_tokens (token, vr_object) VALUES ($1,$2)');
	$result=pg_execute($dbconn, "generateToken", array($token,$vrops_objectId));
	return $token;
}

?>
