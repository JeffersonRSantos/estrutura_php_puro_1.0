<?php

namespace App\Middlewares;

use Core\Helpers\JWT;
use Core\Helpers\Request;
use Core\Helpers\Response;

class AuthApi {
    public function handle($params, $next) {
        $headers = Request::header();
        try {
            if(empty($headers['Authorization'])) throw new \Exception("Missing authorization token");
            $headers['Authorization'] = str_replace("Bearer ", "", $headers['Authorization']);
            $token = $headers['Authorization'];
            JWT::check($token);
        } catch(\Exception $e) {
            return Response::json(["status"=>"error","message"=>$e->getMessage()],400);
        }
        
        
        return $next;
    }
}