<?php

namespace Core;

class PdoConn extends \Core\Database {
    public static function get() {
        return self::getDB();
    }
}