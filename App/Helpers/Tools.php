<?php

namespace App\Helpers;

class Tools
{
    public static function createAssinatura(string $name, string $cpf, string $uuid, string $data_formalizacao, string $document)
    {
        $signature = $cpf . '@@' . $uuid . '@@' . strrev(trim(explode(" ", $name)[0])) . '@@' . $data_formalizacao . '@@' . $document;
        $hash = self::hash($signature);
        $res = "";
        $let = str_split($hash);
        $count = count($let);
        $i = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($i >= 15) break;
            if (is_numeric($let[$i])) {
                $res .= $let[$i];
                continue;
            }
            $res .= substr(ord($let[$i]), -1, 1);
        }
        return (string) $res;
    }
    public static function createAssinaturaFull(string $name, string $cpf, string $uuid, string $data_formalizacao, string $document)
    {
        $signature = $cpf . '@@' . $uuid . '@@' . strrev(trim(explode(" ", $name)[0])) . '@@' . $data_formalizacao . '@@' . $document;
        return (string) self::hash($signature);
    }
    public static function hash(string $string)
    {
        return hash_hmac("sha256", $string, Config()::APP_KEY);
    }
    public static function hashS3($cpf, $id, $name)
    {
        $hash = strrev($cpf) . '@@' . strrev($id) . '@@' . strrev(trim(explode(" ", $name)[0]));
        return hash_hmac("sha256", $hash, Config()::APP_KEY);
    }
    public static function crc32(string $string)
    {
        return (string) crc32($string . Config()::APP_KEY);
    }
    public static function verifyCPF($cpf)
    {
        $cpf = str_replace(array(",",".","-"," "), "", $cpf);
        return preg_match('/^\d{3}[\.]?\d{3}[\.]?\d{3}[\-]?\d{2}$/', $cpf);
    }

    public static function cleanResumeHtml($resume)
    {
        $str_replace = str_replace(["\r", "\n"], ["", ""], $resume);
        $replaces = ['</p>  ', '<html>', "<body style = 'font-family: calibri;'>", "<body>", "<h3> ", "<h3>", "</h3>", "<p>", "</p>", "<br>", "</body>", "</html>"];
        $terms = ["\n\n", "", "", "", "*", "*", "*\n", "", "\n", "\n", "", ""];

        $replaces_html = str_replace($replaces, $terms, $str_replace);

        return $replaces_html;
    }
    public static function valorPorExtenso($valor = 0, $bolExibirMoeda = true, $bolPalavraFeminina = false)
    {
        $singular = null;
        $plural = null;
        if ($bolExibirMoeda) {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
        } else {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
        }
        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
        if ($bolPalavraFeminina) {
            if ($valor == 1) {
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
            } else {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
            }
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas", "quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
        }
        $z = 0;
        $valor = number_format($valor, 2, ".", ".");
        $inteiro = explode(".", $valor);
        for ($i = 0; $i < count($inteiro); $i++) {
            for ($ii = mb_strlen($inteiro[$i]); $ii < 3; $ii++) {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }
        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
        for ($i = 0; $i < count($inteiro); $i++) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($valor == "000")
                $z++;
            elseif ($z > 0)
                $z--;

            if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                $r .= (($z > 1) ? " de " : "") . $plural[$t];

            if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
        }
        $rt = mb_substr($rt, 1);
        return ($rt ? trim($rt) : "zero");
    }
    public static function mounted($s3_path)
    {
        return url("/mounted/" . $s3_path);
    }
    public static function moneyFormat($number)
    {
        return number_format($number, 2, ",", ".");
    }
    public static function formatFloat($number)
    {
        return number_format((float)$number, 2, '.', '');
    }
    public static function limpaCPF($valor)
    {
        $valor = preg_replace('/[^0-9]/', '', $valor);
        return $valor;
    }
    public static function formatDecision($msg)
    {
        $lpn = preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $msg);
        $lpn = mb_strtolower($lpn);
        return  ucfirst($lpn);
    }
    public static function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
    public static function arrayFilter($array){
        return array_filter($array);
    }
    
}
