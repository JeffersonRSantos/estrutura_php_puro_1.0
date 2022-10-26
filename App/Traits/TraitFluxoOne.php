<?php

namespace App\Traits;

use App\Helpers\Tools;
use App\Models\IN100;
use App\Models\StepsWhats;
use Core\Services\Zenvia;

/**
 * Fluxo One (in100)
 */
trait TraitFluxoOne
{
    public static function fluxo($message, $phone, $fluxo_id, $id_lead)
    {
        $user = IN100::where('id', '=', $id_lead)->first();
        
        //Reinicia fluxo
        if ($message == '*') {
            StepsWhats::insert([
                'message' => MSG_2,
                'type' => 'bot',
                'telefone' => $phone,
                'lead_id_in100' => $id_lead,
                'id_fluxo' => 1,
                'step' => 1,
                'channel' => 'whatsapp'
            ]);

            Zenvia::sendWhatsMessage(MSG_2, $phone, 'whatsapp');
            IN100::where('id', '=', $id_lead)
                ->update([
                    'step_whatsapp' => 1,
                    'tentativas_whatsapp' => 0
                ]);
            return;
        }

        if ($message == 'Quero confirmar minha proposta') {
            StepsWhats::insert([
                'message' => MSG_FLUXO_114,
                'type' => 'bot',
                'telefone' => $phone,
                'lead_id_in100' => $id_lead,
                'id_fluxo' => 1,
                'step' => 1,
                'channel' => 'whatsapp'
            ]);
            Zenvia::sendWhatsMessage(MSG_FLUXO_114, $phone, 'whatsapp');
            return true;
        }

        //Segue o fluxo
        switch ($user['step_whatsapp']) {
            case '1':
                if (Tools::verifyCPF($message) == 1) {
                    if ($user['cpf'] == Tools::limpaCPF($message)) {
                        $url = url() . "/iniciar/" . $id_lead . '/' . Tools::crc32($id_lead);
                        $message = str_replace('##LINK_TERMO_AUTORIZACAO##', $url, MSG_FLUXO_2);

                        StepsWhats::insert([
                            'message' => $message,
                            'type' => 'bot',
                            'telefone' => $phone,
                            'lead_id_in100' => $id_lead,
                            'id_fluxo' => 1,
                            'step' => 2,
                            'channel' => 'whatsapp'
                        ]);

                        Zenvia::sendWhatsMessage($message, $phone, 'whatsapp');
                        IN100::where('id', '=', $id_lead)
                            ->update([
                                'step_whatsapp' => 2,
                                'tentativas_whatsapp' => 0
                            ]);
                    } else {
                        if ($user['tentativas_whatsapp'] < 3) {
                            StepsWhats::insert([
                                'message' => MSG_FLUXO_1_ERRO . ' Tentativas: ' . $user['tentativas_whatsapp'] . '/ (3)',
                                'type' => 'bot',
                                'telefone' => $phone,
                                'lead_id_in100' => $id_lead,
                                'id_fluxo' => 1,
                                'step' => 6,
                                'channel' => 'whatsapp'
                            ]);

                            Zenvia::sendWhatsMessage(MSG_FLUXO_1_ERRO, $phone, 'whatsapp');
                            IN100::where('id', '=', $id_lead)->update(['tentativas_whatsapp' => ($user['tentativas_whatsapp'] + 1)]);
                        } else {
                            StepsWhats::insert([
                                'message' => MSG_FLUXO_8_ERRO,
                                'type' => 'bot',
                                'telefone' => $phone,
                                'lead_id_in100' => $id_lead,
                                'id_fluxo' => 1,
                                'step' => 8,
                                'channel' => 'whatsapp'
                            ]);

                            Zenvia::sendWhatsMessage(MSG_FLUXO_8_ERRO, $phone, 'whatsapp');
                            IN100::where('id', '=', $id_lead)->update(['fluxo_closed' => 1, 'step_whatsapp' => 8]);
                        }
                    }
                } else {
                    StepsWhats::insert([
                        'message' => MSG_FLUXO_1_ERRO,
                        'type' => 'bot',
                        'telefone' => $phone,
                        'lead_id_in100' => $id_lead,
                        'id_fluxo' => 1,
                        'step' => 6,
                        'channel' => 'whatsapp'
                    ]);

                    Zenvia::sendWhatsMessage(MSG_FLUXO_1_ERRO, $phone, 'whatsapp');
                }
                break;

            case '2':
                $url = url() . "/iniciar/" . $id_lead . '/' . Tools::crc32($id_lead);
                Zenvia::sendWhatsMessage($url . "\n\n" .MSG_FLUXO_7, $phone, 'whatsapp');
                break;

            default:
                error_log('FLUXO 1 - CAIU NO SWITCH DEFAULT');
                break;
        }
    }
}
