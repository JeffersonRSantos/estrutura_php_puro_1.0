<?php

namespace Core\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../public/assets/phpmailer/PHPMailer.php';
require '../public/assets/phpmailer/Exception.php';
require '../public/assets/phpmailer/SMTP.php';

date_default_timezone_set('America/Sao_Paulo');

class SendMail{
    public static function send($email, $message, $file = false){
        
        try {
            //Server settings
            $mail = new PHPMailer();
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'email-smtp.us-east-2.amazonaws.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'AKIA2X2MLI56TXCCVJU5';                     //SMTP username
            $mail->Password   = 'BKHxjDbH8azzs1PY0KeGSFCch5NB100m5jwZWu61kxcU';                               //SMTP password
            $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
            $mail->Port       = 587;                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->Debugoutput = 'html';
        
            //Recipients
            $mail->setFrom('no-reply@auditadigital.com.br');
            $mail->addAddress($email);     //Add a recipient
            //$mail->addAddress('ellen@example.com');               //Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');
        
            //Attachments
            if($file !== false){
                $mail->addAttachment($file);
            }
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'INSS Formalizacao - Credcesta';
            $mail->Body    = $message;
        
            if(!$mail->send()) echo ' Erro ao enviar e-mail';
            else return true;
        } catch (Exception $e) {
            echo "ERRO AO ENVIAR: {$mail->ErrorInfo}";
        }
    }
}
