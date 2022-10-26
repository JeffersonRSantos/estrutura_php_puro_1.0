<?php

namespace Core\Helpers;

class AES {
    public static function encrypt($val) {
        $res = openssl_encrypt($val, "aes256", \App\Config::APP_KEY, 0, substr(md5(\App\Config::APP_KEY),0,16)) ?? null;
        return $res;
    }
    public static function decrypt($val) {
        $res = openssl_decrypt($val, "aes256", \App\Config::APP_KEY, 0, substr(md5(\App\Config::APP_KEY),0,16)) ?? null;
        return $res;
    }
}