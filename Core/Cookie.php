<?php

namespace Core;

class Cookie {
	public static function set($name, $content, int $exp) {
		$content = \Core\Helpers\AES::encrypt($content);
        setCookie($name, $content, time() + $exp, "/", "", true, true);
        return true;
    }
    public static function get($name) {
        if(empty($_COOKIE[$name])) return null;
		$res = \Core\Helpers\AES::decrypt($_COOKIE[$name]);
        return $res;
	}
}