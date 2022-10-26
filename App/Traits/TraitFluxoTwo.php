<?php

namespace App\Traits;

use App\Helpers\Tools;
use App\Models\Lead;
use App\Models\StepsWhats;
use Core\Helpers\Queue;
use Core\Services\Caf;
use Core\Services\Zenvia;

/**
 * Fluxo Two (INSS)
 */
trait TraitFluxoTwo
{
    public static function fluxo($message, $phone, $step_id, $id_lead)
    {
        $user = Lead::select(
            'id',
            'user_name',
            'user_cpf',
            'step_whatsapp',
            'user_telefone',
            'resume_html',
            'tentativas_whatsapp',
            'seguro_prestamista_resumo',
            'seguro_prestamista_confirma',
            'seguro_prestamista',
            'process_id',
            'convenio',
            'fluxo_id',
            'caf_status'
        )->where('id', '=', $id_lead)->first();

        //Reinicia fluxo
        if ($message == '*') {

            //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
            Lead::saveStepsLead($user['user_telefone'], $user['id'], 4, MSG_2, 1);
            Zenvia::sendWhatsMessage(MSG_2, $phone, 'whatsapp');

            return true;
        }

        if ($message == 'Quero confirmar minha proposta') {
            Lead::saveStepsLead($user['user_telefone'], $user['id'], 3, MSG_FLUXO_101, 1);
            Zenvia::sendWhatsMessage(MSG_FLUXO_114, $phone, 'whatsapp');

            return true;
        }

        //FLUXO
        switch ($user['step_whatsapp']) {

                //VALIDA CPF
            case '1':
                if (Tools::verifyCPF($message) == 1) {
                    if ($user['user_cpf'] == Tools::limpaCPF($message)) {

                        StepsWhats::insert([
                            'message' => Tools::cleanResumeHtml($user['resume_html']),
                            'type' => 'bot',
                            'telefone' => $user['user_telefone'],
                            'lead_id' => $user['id'],
                            'id_fluxo' => 2,
                            'step' => 2,
                            'channel' => 'whatsapp'
                        ]);

                        Lead::saveStepsLead($user['user_telefone'], $user['id'], 5, MSG_FLUXO_101, 2);

                        Zenvia::sendWhatsMessage(Tools::cleanResumeHtml($user['resume_html']), $phone, 'whatsapp');
                        Zenvia::sendWhatsButtonMessage(MSG_FLUXO_101, $phone);
                    } else {
                        if ($user['tentativas_whatsapp'] < 3) {

                            $msg = MSG_FLUXO_102 . ' Tentativas: ' . $user['tentativas_whatsapp'] . '/ (3)';

                            //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                            switch ($user['tentativas_whatsapp']) {
                                case 1:
                                    Lead::saveStepsLead($user['user_telefone'], $user['id'], 6, $msg, 1);
                                    break;
                                case 2:
                                    Lead::saveStepsLead($user['user_telefone'], $user['id'], 7, $msg, 1);
                                    break;
                            }

                            Lead::where('id', '=', $user['id'])
                                ->update(['tentativas_whatsapp' => ($user['tentativas_whatsapp'] + 1), 'fluxo_closed' => 0]);

                            Zenvia::sendWhatsMessage(MSG_FLUXO_102, $phone, 'whatsapp');
                        } else {

                            //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                            Lead::saveStepsLead($user['user_telefone'], $user['id'], 18, MSG_FLUXO_106, 1);

                            Lead::where('id', '=', $user['id'])->update(['fluxo_closed' => 1, 'is_validation_manual' => 0]);

                            Zenvia::sendWhatsMessage(MSG_FLUXO_106, $phone, 'whatsapp');
                        }
                    }
                } else {
                    if ($user['tentativas_whatsapp'] <= 3) {

                        $msg = MSG_FLUXO_102 . ' Tentativas: ' . $user['tentativas_whatsapp'] . '/ (3)';

                        //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                        Lead::saveStepsLead($user['user_telefone'], $user['id'], 6, $msg, 1);

                        Lead::where('id', '=', $user['id'])
                            ->update(['tentativas_whatsapp' => ($user['tentativas_whatsapp'] + 1)]);

                        Zenvia::sendWhatsMessage(MSG_FLUXO_102, $phone, 'whatsapp');
                    } else {

                        //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                        Lead::saveStepsLead($user['user_telefone'], $user['id'], 18, MSG_FLUXO_106, 1);

                        Lead::where('id', '=', $user['id'])->update(['fluxo_closed' => 1, 'is_validation_manual' => 0]);

                        Zenvia::sendWhatsMessage(MSG_FLUXO_106, $phone, 'whatsapp');
                    }
                }
                break;

                //VALIDA RESUMO DA PROPOSTA OU ENVIA CAF
            case '2':
                switch (Tools::formatDecision($message)) {
                    case 'Sim':
                        if (isset($user['seguro_prestamista_confirma']) && !empty($user['seguro_prestamista_resumo'])) {

                            $prestamista_confirma = Tools::cleanResumeHtml($user['seguro_prestamista_confirma']);
                            $prestamista_resumo = Tools::cleanResumeHtml($user['seguro_prestamista_resumo']);

                            //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                            Lead::saveStepsLead($user['user_telefone'], $user['id'], 8, $prestamista_confirma, 3);

                            StepsWhats::insert([
                                'message' => $prestamista_resumo,
                                'type' => 'bot',
                                'telefone' => $user['user_telefone'],
                                'lead_id' => $user['id'],
                                'id_fluxo' => 2,
                                'step' => 10,
                                'channel' => 'whatsapp'
                            ]);

                            StepsWhats::insert([
                                'message' => MSG_FLUXO_111,
                                'type' => 'bot',
                                'telefone' => $user['user_telefone'],
                                'lead_id' => $user['id'],
                                'id_fluxo' => 2,
                                'step' => 10,
                                'channel' => 'whatsapp'
                            ]);

                            Zenvia::sendWhatsMessage($prestamista_confirma, $phone, 'whatsapp');
                            sleep(1);
                            Zenvia::sendWhatsMessage($prestamista_resumo, $phone, 'whatsapp');
                            sleep(1);
                            Zenvia::sendWhatsButtonMessage(MSG_FLUXO_111, $phone);
                        } else {

                            //Get Onboarding Caf
                            $resp = Caf::createOnboarding($user);
                            $url = url() . "/iniciar/formalizacao/" . $id_lead . '/' . Tools::crc32($id_lead);
                            $msg = str_replace('##NOME##', $user['user_name'], MSG_FLUXO_104);
                            $msg = str_replace("##LINK##", $url, $msg);

                            //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                            Lead::saveStepsLead($user['user_telefone'], $user['id'], 8, $msg, 4);

                            Lead::where('id', '=', $user['id'])
                                ->update(['url_caf' => $resp['url'], 'onboarding_caf' => $resp['_id']]);

                            Zenvia::sendWhatsMessage($msg, $phone, 'whatsapp');

                            // processa os documentos
                            $queue = new Queue("ProcessDocuments", rand(1, 6));
                            $queue->add([
                                "lead_id" => $user['id'],
                                "process_id" => $user['process_id'],
                                "user_cpf" => $user['user_cpf'],
                                "user_telefone" => $user['user_telefone'],
                                "user_name" => $user['user_name'],
                                "seguro_prestamista" => $user['seguro_prestamista'],
                                "convenio" => $user['convenio'],
                                "fluxo_id" => $user['fluxo_id']
                            ]);
                        }

                        break;
                    case 'Nao':

                        //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                        Lead::saveStepsLead($user['user_telefone'], $user['id'], 19, MSG_FLUXO_103, 4);

                        Lead::where('id', '=', $user['id'])->update(['fluxo_closed' => 1, 'is_validation_manual' => 0]);

                        Zenvia::sendWhatsMessage(MSG_FLUXO_103, $phone, 'whatsapp');
                        break;

                    default:

                        StepsWhats::insert([
                            'message' => MSG_FLUXO_107,
                            'type' => 'bot',
                            'telefone' => $phone,
                            'lead_id' => $id_lead,
                            'id_fluxo' => 2,
                            'step' => 8,
                            'channel' => 'whatsapp'
                        ]);

                        Lead::where('id', '=', $user['id'])->update(['fluxo_closed' => 0]);
                        Zenvia::sendWhatsMessage(MSG_FLUXO_107, $phone, 'whatsapp');
                        break;
                }
                break;

                //VALIDA RESUMO PRESTAMISTA E ENVIA CAF
            case '3':
                $msg = Tools::formatDecision($message);
                if ($msg == 'Sim' || $msg == 'Nao') {
                    switch (Tools::formatDecision($message)) {
                        case 'Sim':
                            $seguro_prestamista = 1;
                            $step_id = 10;
                            break;
                        case 'Nao':
                            $seguro_prestamista = 0;
                            $step_id = 21;
                            break;
                    }

                    //Get Onboarding Caf
                    $resp = Caf::createOnboarding($user);
                    $url = url() . "/iniciar/formalizacao/" . $user['id'] . '/' . Tools::crc32($user['id']);
                    $msg = str_replace('##NOME##', $user['user_name'], MSG_FLUXO_104);
                    $msg = str_replace("##LINK##", $url, $msg);

                    //$phone_user, $lead_id, $step_id, $step_whats_message, $step_id_whats (constants)
                    Lead::saveStepsLead($user['user_telefone'], $user['id'], $step_id, $msg, 4);

                    Lead::where('id', '=', $user['id'])->update([
                        'url_caf' => $resp['url'],
                        'onboarding_caf' => $resp['_id'],
                        'seguro_prestamista' => $seguro_prestamista
                    ]);

                    Zenvia::sendWhatsMessage($msg, $phone, 'whatsapp');

                    // processa os documentos
                    $queue = new Queue("ProcessDocuments", rand(1, 6));
                    $queue->add([
                        "lead_id" => $user['id'],
                        "process_id" => $user['process_id'],
                        "user_cpf" => $user['user_cpf'],
                        "user_telefone" => $user['user_telefone'],
                        "user_name" => $user['user_name'],
                        "seguro_prestamista" => $seguro_prestamista,
                        "convenio" => $user['convenio'],
                        "fluxo_id" => $user['fluxo_id']
                    ]);
                }
                break;

                //ENVIA LINK ACEITE DE DOCUMENTOS
            case 4:

                if ($user['caf_status'] <> 0) {

                    $msg = str_replace('##LINK##', url() . '/assinar/' . $user['id']  . '/' . $user['process_id'], MSG_FLUXO_108);

                    StepsWhats::insert([
                        'message' => $msg,
                        'type' => 'bot',
                        'telefone' => $user['user_telefone'],
                        'lead_id' => $user['id'],
                        'id_fluxo' => 2,
                        'step' => 4,
                        'channel' => 'whatsapp'
                    ]);

                    Zenvia::sendWhatsMessage($msg, $phone, 'whatsapp');
                }
                break;

                /***
                 * Adicionado esse step para auxilio na migração do banco de dados para a nova estrutura
                 * 
                 * Esse case 10, irá identificar os usuário que 
                 * estão no meio do processo e reiniciar o fluxo deles
                 */

            case 10:

                $msg = str_replace('##NAME##', $user['user_name'], MSG_6);
                Lead::saveStepsLead($user['user_telefone'], $user['id'], 4, $msg, 1);
                Zenvia::sendWhatsMessage($msg, $phone, 'whatsapp');
                break;

            default:
                error_log('FLUXO 1 - CAIU NO SWITCH DEFAULT');
                break;
        }
    }
}
