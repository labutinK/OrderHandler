<?php
namespace Local\Notifier;

use Bitrix\Main;
use Bitrix\Main\Diag\Debug;

class OkoCrmNotifier
{
    private const API_URL = 'https://api.okocrm.com/v2/leads';
    private const PIPELINE_ID = '10484';
    private const STAGE_ID = '48763';
    private const RESPONSIBLE_QUEUE = 'queue_1352';
    private const CF_PHONE = 'cf_13247';
    private const CF_ADDRESS = 'cf_13246';
    private const CF_INDEX = 'cf_13245';
    private const CF_MESSAGE = 'cf_13249';

    public function send($orderId, $sum, array $items, array $props): void
    {
        $token = Main\Config\Option::get("askaron.settings", "UF_OKOCRM_TOKEN");
        if (!$token) return;

        $fio = $props['FIO']['VALUE'] ?? '';
        $phone = $props['PHONE']['VALUE'] ?? '';
        $mail = $props['EMAIL']['VALUE'] ?? '';
        $city = $props['CITY']['VALUE'] ?? '';
        $address = $props['ADDRESS']['VALUE'] ?? '';
        $index = str_replace('-', '', $props['INDEX']['VALUE'] ?? '');
        $comment = 'Комментарий к заказу - ' . ($props['COMMENT']['VALUE'] ?? '');
        $addressFull = trim("$city $address");

        $message = "";
        foreach ($items as $item) {
            $message .= "- Товар: {$item['NAME']}, Цена: {$item['PRICE']} руб., Кол-во: {$item['QUANTITY']}, Размер: {$item['SIZE']}..\n";
        }
        $message .= ' ' . $comment;

        $postFields = [
            'name' => "Новый заказ - $orderId",
            'pipeline_id' => self::PIPELINE_ID,
            'stages_id' => self::STAGE_ID,
            'budget' => $sum,
            'contact[name]' => $fio,
            'contact[phone]' => $phone,
            'contact[email]' => $mail,
            'responsible' => self::RESPONSIBLE_QUEUE,
            self::CF_PHONE => $phone,
            self::CF_ADDRESS => $addressFull,
            self::CF_INDEX => $index,
            self::CF_MESSAGE => $message,
            'note[text]' => $comment,
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) || $httpCode >= 300) {
            $error = curl_error($ch) ?: "HTTP $httpCode: $response";
            Debug::writeToFile($error, "[OkoCrmNotifier]", "/okocrm-error.log");
        }
        curl_close($ch);
    }
}