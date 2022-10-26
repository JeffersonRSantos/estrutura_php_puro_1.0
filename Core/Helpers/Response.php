<?php

namespace Core\Helpers;

class Response {
    public static $status = 200;
    public static $headers = [];

    public static function send($value, $status = null) {
        if($status !== null) self::$status = $status;
        http_response_code(self::$status);
        foreach(self::$headers as $header) {
            header($header);
        }
        echo $value;
    }

    public static function json(array $value, $status = null) {
        if($status !== null) self::$status = $status;

        http_response_code(self::$status);
        self::header('Content-Type: application/json');
        self::send(json_encode($value));
    }

    public static function header(string $header) {
        self::$headers[] = $header;
    }
}