<?php

namespace Core;

use App\Config;
use Core\Cookie;
use Core\Helpers\JWT;
use Exception;

class Session {
    private static $started = 0;
    private static $session = null;
    private static function start() {
        if(self::$started == 1) return;
        
        if(!empty($_COOKIE[Config::SESSION_ID])) {
            $content = Cookie::get(Config::SESSION_ID);
            try {
                $content = JWT::check($content);
            } catch (Exception $e) {
                $content = [];
            }
        } else {
            $content = [];
        }
        Cookie::set(Config::SESSION_ID, JWT::sign($content,1200), 1200);
        self::$session = $content;
        self::$started = 1;
    }
    public static function set($key, $content) {
        self::start();
        self::$session[$key] = $content;
        Cookie::set(Config::SESSION_ID, JWT::sign(self::$session,1200), 1200);
    }
    public static function get($key) {
        self::start();
        return self::$session[$key] ?? null;
    }
    public static function destroy(){
        Cookie::set(Config::SESSION_ID, '', 0);
        self::$session = null;
    }
}