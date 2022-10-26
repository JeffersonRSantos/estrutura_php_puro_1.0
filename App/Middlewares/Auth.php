<?php

namespace App\Middlewares;

use Core\Helpers\Request;
use Core\Helpers\Response;
use Core\Session;
use Core\View;

class Auth
{
    public $request;

    public function __construct()
    {
        $this->request = (new Request)->input();
    }
    public function handle($params, $next)
    {

        if ($this->validateLogin())
            return $next;
    }

    protected function md5Reforced($string) {
        $res = hash_hmac("sha256", $string, "formalizacaooacaziamrof");
        $res = hash_hmac("sha256", $res, "formalizacaooacaziamrof");
        return $res;
    }

    protected function validateLogin()
    {
        if (Session::get('user_id') == null) {
            if (isset($this->request->username)) {
                $lead = Model('User')::where('email', '=', $this->request->username)->first();
                if (empty($lead)) $lead = Model('User')::where('username', '=', $this->request->username)->first();
                if (!empty($lead)) {
                    if ($lead['inactive'] == 1) return Response::send('Seu perfil de usuário está inativo.', 403);
                    if (password_verify($this->request->password, $lead['password']) OR $lead['old_password'] == $this->md5Reforced($this->request->password)) {
                        Session::set('email', $lead['email']);
                        Session::set('password', $this->request->password);
                        Session::set('type_user', $lead['nivel']);
                        Session::set('first_access', $lead['first_access']);
                        Session::set('user_id', $lead['id']);
                        return true;
                    } else {
                        return Response::send('Usuário ou senha inválida.', 403);
                    }
                }else
                    return Response::send('Usuário ou senha inválida.', 403);
            } else {
                header('Location: ' . url() . '/login');
            }
        } else {
            return true;
        }
    }
}
