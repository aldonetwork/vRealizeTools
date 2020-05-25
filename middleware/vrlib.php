<?php
require_once("./config_inc.php");
function vrops_get_token(){

	if(file_exists($tmp_login_file)){
		$token=json_decode(file_get_contents($tmp_login_file), $assoc = true);
		if(($token["validity"]/1000)>time()){
			return $token["token"];
		}
	}
	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$vrops_server."/suite-api/api/auth/token/acquire");
        //curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json"));
	$data=array();
	$data["username"]=$vrops_user;
	$data["password"]=$vrops_password;
	$data_string = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	$result = curl_exec($ch);
	file_put_contents($tmp_login_file,$result);
	$token=json_decode($result, $assoc = true);
	return $token["token"];
}
?>
