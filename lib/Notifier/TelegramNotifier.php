<?php
namespace Local\Notifier;

use Bitrix\Main;
use Bitrix\Main\Diag\Debug;

class TelegramNotifier
{
    public function send($orderId, $sum, array $items, array $props): void
    {
        $botToken = Main\Config\Option::get("askaron.settings", "UF_BOT_TOKEN_ID");
        $chatId = Main\Config\Option::get("askaron.settings", "UF_TELEG_CHAT_ID");
        if (!$botToken || !$chatId) return;

        $message = "Создан новый заказ:\nID заказа: $orderId\nСумма заказа: $sum руб.\n\nПозиции товаров:\n";
        foreach ($items as $item) {
            $message .= "- Товар: {$item['NAME']}, Цена: {$item['PRICE']} руб., Кол-во: {$item['QUANTITY']}, Размер: {$item['SIZE']}..\n";
        }
        $message .= "\nСвойства заказа:\n";
        foreach ($props as $code => $property) {
            $message .= "- {$property['NAME']}: {$property['VALUE']}\n";
        }

        $query = http_build_query([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        ]);

        $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage?$query");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            Debug::writeToFile($error, "[TelegramNotifier]", "/telegram-error.log");
        }
        curl_close($ch);
    }
}
