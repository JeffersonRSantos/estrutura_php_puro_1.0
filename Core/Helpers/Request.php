<?php

namespace Core\Helpers;

class Request {
    public static $data;
    public function __construct()
    {
        $json = json_decode(file_get_contents("php://input"),true) ?: [];
        self::$data = array_merge($_GET, $json, $_POST);
    }

    public static function input($value = null)
    {
        if($value) return self::$data[$value] ?? null;
        return (object) self::$data;
    }

    public static function header(){
        return getallheaders();
    }

    public static function thisUrl()
    {
        return $GLOBALS['this_url'];
    }
}