<?php

namespace Core;

trait Model {

	public static function __callStatic($method, $args) {
		$invoke = new \Core\Querybuilder(self::$table);
		return call_user_func_array([$invoke,$method],$args);
	}
	
}