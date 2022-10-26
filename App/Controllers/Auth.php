<?php

namespace App\Controllers;

use App\Helpers\Tools;
use App\Models\User;
use Core\Helpers\Response;
use Core\Helpers\SendMail;
use Core\Helpers\Validation;
use Core\Session;
use Core\View;
use Exception;

class auth extends \Core\Controller
{
    public function login()
    {
        header('Location:' . url() . '/dashboard');
    }

    public function refreshPassword()
    {
        if (Session::get('first_access') == '1')
            View::renderTemplate('Auth/refresh_password.html');
        else
            header('Location:' . url() . '/dashboard');
    }

    public function refreshPasswordPost()
    {
        try {
            if (self::$request->input('password')) {
                User::where('id', '=', Session::get('user_id'))->update(['first_access' => 0, 'password' => password_hash(self::$request->input('password'), PASSWORD_DEFAULT)]);
                Session::set('first_access', 0);
                Response::send('Senha redefinida com sucesso.', 200);
            }
        } catch (\Exception $e) {
            return Response::send($e->getMessage(), 400);
        }
    }

    public function dashboard()
    {  
        //
    }

    public function logout()
    {
        Session::destroy();
        header('Location:' . url() . '/login');
    }

    public function profile()
    {
        if (Session::get('type_user') == 1) $users = User::get();
        else $users = User::where('id', '=', Session::get('user_id'))->get();
        View::renderTemplate('Auth/profile.html', ['users' => $users]);
    }

    public function createUser()
    {
        View::renderTemplate('Auth/create_user.html');
    }

    public function editUser($id)
    {
        if (Session::get('type_user') == 1) {
            $user = User::where('id', '=', $id)->first();
        } else {
            $user = User::where('id', '=', Session::get('user_id'))->first();
        }
        View::renderTemplate('Auth/edit_user.html', ['user' => $user]);
    }

    public function createUserPost()
    {
        Validation::require(['name', 'email', 'cpf', 'nivel']);
        $dados = self::$request->input();
        $exists_email = User::where('email', '=', $dados->email)->count();
        $password = Tools::randomPassword();
        if ($exists_email == 0) {
            $username = explode(' ', $dados->name);
            $username = strtolower($username[0]) . ($username[1] ? '.' . strtolower($username[1]) : '');
            $exists_username = User::where('username', '=', $username)->count();
            $username = ($exists_username == 0 ? $username : $username . $exists_username);
            User::insert([
                'email' => $dados->email,
                'name' => $dados->name,
                'cpf' => (int) Tools::limpaCPF($dados->cpf),
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'nivel' => $dados->nivel
            ]);

            $mail = file_get_contents(dirname(__DIR__) . '/Views/Mails/send_password.html');
            $msg = str_replace('##USERNAME##', $username, $mail);
            $msg = str_replace('##NEW_PASSWORD##', $password, $msg);
            $msg = str_replace('##LINK##', url() . '/login', $msg);
            SendMail::send($dados->email, $msg);
            return Response::send('Usuário cadastrado com sucesso.', 200);
        } else {
            return Response::send('E-mail já cadastrado no sistema.', 404);
        }
    }

    public function editUserPost()
    {
        $dados = (array) self::$request->input();
        $dados = Tools::arrayFilter($dados);
        $id = $dados['id'];
        unset($dados['id']);
        unset($dados['_TOKEN_CSRF']);
        unset($dados['confirmed_password']);
        if (isset($dados['password'])) $dados['password'] = password_hash($dados['password'], PASSWORD_DEFAULT);
        try {
            User::where('id', '=', $id)->update($dados);
            return Response::send('Perfil atualizado com sucesso', 200);
        } catch (Exception $e) {
            return Response::json([$e->getMessage()], 404);
        }
    }

    public function userInactiveAndActive($id)
    {
        try {
            User::where('id', '=', $id)->update(['inactive' => self::$request->input('type')]);
            return Response::send('Registro atualizado com sucesso.', 200);
        } catch (\Exception $th) {
            return Response::json([$th->getMessage()], 404);
        }
    }

}
