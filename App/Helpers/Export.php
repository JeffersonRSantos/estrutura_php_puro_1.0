<?php

require_once dirname(getcwd()). "/vendor/autoload.php";
require_once dirname(getcwd()). "/Core/Core.php";

use App\Helpers\Tools;
use App\Models\Lead;
use App\Models\StepsLead;
use App\Models\TypeDocumentUser;
use Core\Helpers\SendMail;

if(!$argc > 1) exit;

$sql = $argv[1];
$lead = Lead::raw($sql)->fetchAll();

$fileName = "auditoria_digital_" . md5(time() . rand()) . ".csv";
$fp = fopen(path("public/exports/") . $fileName, 'w');

fputcsv($fp, [
    "Nome", 
    "SMS", 
    "Telefone", 
    "CPF", 
    "Última ação", 
    "Liveness", 
    "CurrentStep", 
    "Parâmetro ACERTPIX", 
    "Processo ACERTPIX", 
    "Processo Prova de Vida",
    "Pacote de Vantagens",
    "Status Auditoria",
    "Criado em", 
    "Última interação"
]);

for ($i = 0; $i < count($lead); $i++) {
    $status = StepsLead::getStatusName($lead[$i]['step_id']);
    $status_document = ( $lead[$i]['type_document'] ? TypeDocumentUser::where('name', '=', strtoupper($lead[$i]['type_document']))->first() : 1);
    
    fputcsv($fp, [
        $lead[$i]["user_name"],
        "SMS Enviado",
        $lead[$i]["user_telefone"],
        $lead[$i]["user_cpf"],
        ($status['description'] ?? 'Fluxo não iniciado' ),
        ($status['liveness'] ?? 0 ),
        ($status['currentStep'] ?? 0 ),
        lang('status_lead_acertpix.'.$lead[$i]['acertpix_active']),
        lang('status_caf.'.$lead[$i]['acertpix_status']),
        lang('status_caf.'.$lead[$i]['caf_status']),
        ( $lead[$i]['seguro_prestamista'] != null ? lang('seguro_prestamista.'.$lead[$i]['seguro_prestamista']) : 'Pacote de Vantagens não enviado.'  ),
        (int) Tools::checkStatus($lead[$i], (int) $status_document['status']),

        date(
            "d/m/Y H:i:s",
            strtotime($lead[$i]["created_at"])
        ),
        date('d/m/Y H:i:s', strtotime($lead[$i]['updated_at']))
    ]);
}

fclose($fp);

$body = "<style>body{background:#f5f5f5;font-family:sans-serif;word-break:break-all;}.content{background:#fff;border:1px solid #ccc;padding:20px;max-width:calc(100% - 10px);width:500px;margin:20px auto}a{text-decoration:none;color:#0984e3}</style><div class=content><center><h3>Auditoria Online</h3><br>Seu relatório está pronto, segue o arquivo em anexo.</center></div>";
SendMail::send($argv[2], $body, path('public/exports/') . $fileName);
unlink(path("public/exports/") . $fileName);
