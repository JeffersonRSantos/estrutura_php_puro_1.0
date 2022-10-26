<?php

namespace Core;

use PDO;
use App\Config;

abstract class Database
{
    static $db = null;
    static $expire = null;

    protected static function getDB()
    {
        if(self::$expire !== null && time() > self::$expire) self::$db = null;

        if (self::$db === null) {
            $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8mb4';
            self::$db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));

            // Throw an Exception when an error occurs
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$expire = time() + 120;
        }

        return self::$db;
    }

    protected static function Escape($str, $removeQuote = false) {
        if($removeQuote) return substr(self::getDB()->quote($str),1,-1);
	    return self::getDB()->quote($str);
    }
}
