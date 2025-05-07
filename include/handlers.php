<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Local\Notifier\OrderNotifier;

Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    [OrderNotifier::class, 'handleOrder']
);




