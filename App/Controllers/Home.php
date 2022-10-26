<?php

namespace App\Controllers;

use App\Helpers\Tools;
use App\Models\ForgetPassword;
use App\Models\User;
use Core\Helpers\CurlImplements;
use Core\Helpers\Response;
use Core\Helpers\SendMail;
use Core\Session;
use Core\View;

class Home extends \Core\Controller
{
    public function index()
    {
        View::renderTemplate('Home/index.html');
    }

    public function login()
    {
        if(Session::get('first_access')) header('Location:' . url() . '/refresh_password');
        if(Session::get('email') !== null)
            View::renderTemplate('Auth/dashboard.html');
        else
            View::renderTemplate('Home/login.html');
    }

    public function forgotPassword()
    {
        View::renderTemplate('Home/forgot_password.html');
    }

    public function forgotPasswordPost()
    {
        $exists_user = User::where('email', '=', self::$request->input('email'))->first();
        if(!empty($exists_user)){
            $hash = Tools::hash($exists_user['email']);
            $html_mail = file_get_contents(dirname(__DIR__) .'/Views/Mails/reset_password.html');
            $html_mail = str_replace('##NAME##', $exists_user['username'], $html_mail);
            $html_mail = str_replace('##LINK_RESET##', url() . '/reset_password/'. $hash, $html_mail);
            ForgetPassword::insert([
                'user_id' => $exists_user['id'],
                'token' => $hash
            ]);
            SendMail::send($exists_user['user_email'], $html_mail);
            Response::send('Enviamos um e-mail de confirmação na sua caixa de entrada.', 200);
        }
        else
            Response::send('E-mail não existe no sistema.',404);
    }


    public function resetPassword($hash)
    {
        if(!isset($hash)) return self::$response->send("Not Found", 400);
        $exists_user = ForgetPassword::where('token', '=', $hash)->first();
        if(!empty($exists_user))
            View::renderTemplate('Home/reset_password.html', ['user_id' => $exists_user['user_id']]);
        else
            header('Location: ' . url() .'/Login');
    }

    public function resetPasswordPost()
    {
        $user = User::where('id', '=', self::$request->input('user_id'))->first();
        if(empty($user)) return self::$response->send("Usuário não existe.", 400);
        ForgetPassword::where('user_id', '=', self::$request->input('user_id'))->delete();
        User::where('id', '=', self::$request->input('user_id'))->update([
            'password' => password_hash(self::$request->input('password'), PASSWORD_DEFAULT)
        ]);
        Response::send('Senha atualiza com sucesso.',200);
    }

    public function redirectDashboard() {
        header("Location: " . url() . "/dashboard");
        return;
    }

    public function debug() {
        //
    }

    public function showIp(){
        $curl = ( new CurlImplements('https://neonet.com.br/ip.php', 'GET'))->execute();
        dd($curl->body());
    }

}
