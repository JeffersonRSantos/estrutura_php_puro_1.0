<?php

namespace Core;

use Core\Helpers\Request;
use Core\Helpers\Response;

abstract class Controller
{
    protected $route_params = [];

    public static $request;
    public static $response;

    public function __construct($route_params)
    {
        $this->route_params = $route_params;
        self::$request = new Request;
        self::$response = new Response;
    }

    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    protected function before()
    {
    }

    protected function after()
    {
    }
}
