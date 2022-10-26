<?php

namespace Core\Helpers;

class Validation {
	public static function require($arr, $json = true) {
        $request = Request::$data;
		foreach($arr as $item) {
            if(!isset($request[$item])) {
                Response::send("Campo obrigatório: " . $item, '400');
                die();
            }
        }
	}
}