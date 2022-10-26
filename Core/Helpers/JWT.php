<?php

namespace Core\Helpers;

class JWT {
	public static function sign(array $arr, int $exp) {
		$arr["iat"] = time();
		$arr["exp"] = time() + $exp;
		$header = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9";
		$payload = json_encode($arr);
		$payload = str_replace("=","",base64_encode($payload));
		$sign = hash_hmac("sha256","{$header}.{$payload}",\App\Config::APP_KEY,true);
		$sign = str_replace("=","",base64_encode($sign));
		$jwt = strtr("{$header}.{$payload}.{$sign}", '+/', '-_');
		return $jwt;
	}
	public static function check($token) {
		$token = strtr($token, '-_', '+/');
		$jwt = explode(".",$token);
		if(!is_array($jwt) OR count($jwt) !== 3) throw new \Exception("JWT inválido");
		$hashCompare = hash_hmac("sha256","{$jwt[0]}.{$jwt[1]}",\App\Config::APP_KEY,true);
		$userHash = base64_decode($jwt[2]);
		if(!hash_equals($hashCompare,$userHash)) throw new \Exception("JWT inválido");
		$payload = base64_decode($jwt[1]);
		$payload = json_decode($payload, true);
		if(empty($payload["iat"]) OR empty($payload["exp"]) OR $payload["exp"] < time()) throw new \Exception("JWT expirado");
		return $payload; 
	}
}