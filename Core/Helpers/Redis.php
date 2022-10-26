<?php

namespace Core\Helpers;

use App\Config;
use Predis\Client;

class Redis {
    private static $client = null;

    private static function Client() {
        if(self::$client !== null) return self::$client;

        $options = [];
        if(!empty(Config()::REDIS_PASS)) {
            $options = ['parameters' => [
                'password' => Config()::REDIS_PASS
            ]];
        }
        if(Config()::APP_AMB == "dev") {
            self::$client = new Client([
                'host'   => Config()::REDIS_HOST,
                'port'   => Config()::REDIS_PORT,
            ], $options);
        } else {
            self::$client = new Client([
                'scheme' => 'tls',
                'host'   => Config()::REDIS_HOST,
                'port'   => Config()::REDIS_PORT,
            ], $options);
        }
        
        self::$client->connect();

        return self::$client;
    }

    public static function set($key, $value, int $ttl = null) {
        if($ttl === null) return self::Client()->set($key, $value);
        return self::Client()->set($key, $value, 'EX', $ttl);
    }

    public static function get($key) {
        return self::Client()->set($key);
    }

    public static function remember($key, int $duration, callable $callback) {
        $get = self::Client()->get($key);   
        if($get) return unserialize($get);

        $value = $callback();
        self::set($key, serialize($value), $duration);
        return $value;
    }

    public static function lPush($key, string $value) {
        $push = self::Client()->rPush($key, $value);
        return $push;
    }

    public static function lPop($key) {
        $pop = self::Client()->lpop($key   );
        return $pop;
    }
}