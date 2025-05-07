<?php

namespace Local\Notifier;

use Bitrix\Main;
use Bitrix\Sale\Order;

class OrderNotifier
{
    public static function handleOrder(Main\Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter("ENTITY");
        $isNew = $event->getParameter("IS_NEW");

        CModule::IncludeModule("iblock");

        if (!$isNew) {
            return;
        }

        $sum = $order->getPrice();
        $items = self::getBasketItems($order);
        $props = self::getOrderProperties($order);

        $telegram = new TelegramNotifier();
        $crm = new OkoCrmNotifier();

        $telegram->send($order->getId(), $sum, $items, $props);
        $crm->send($order->getId(), $sum, $items, $props);
    }

    private static function getBasketItems(Order $order): array
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $basket = $order->getBasket();
        $items = [];

        foreach ($basket as $basketItem) {
            $productId = $basketItem->getProductId();
            $res = CIBlockElement::GetList([], ['ID' => $productId], false, false, ['ID', 'PROPERTY_SIZE']);
            $size = '';
            if ($arItem = $res->GetNext()) {
                $size = $arItem['PROPERTY_SIZE_VALUE'];
            }

            $items[] = [
                'NAME' => $basketItem->getField('NAME'),
                'PRICE' => $basketItem->getPrice(),
                'QUANTITY' => $basketItem->getQuantity(),
                'SIZE' => $size
            ];
        }
        return $items;
    }

    private static function getOrderProperties(Order $order): array
    {
        $propertyCollection = $order->getPropertyCollection();
        $properties = [];

        foreach ($propertyCollection as $property) {
            $properties[$property->getField('CODE')] = [
                'NAME' => $property->getField('NAME'),
                'VALUE' => $property->getValue(),
            ];
        }

        return $properties;
    }
}
