<?php

namespace App\Helpers;

use Core\Services\BancoMaster;

class Storage {
    private static function genPath($lead_id, $cpf,$document) {
        $file = $cpf."_".Tools::hash($cpf)."_".$lead_id."_".$document;
        return $file;
    } 
    public static function  getMountedUrl($lead_id, $user_cpf, $document) {
        return url("/storage/" . self::genPath($lead_id, $user_cpf, $document));
    }
    public static function deleteDocuments($user_cpf) {
        foreach(glob(path("public/storage/".$user_cpf."_*")) as $file) {
            unlink($file);
        }
    }
}