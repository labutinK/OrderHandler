<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main;

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/constants.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/constants.php";
}
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/functions.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/functions.php";
}

Bitrix\Main\Loader::registerNamespace('Local', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lib');

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/handlers.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/handlers.php";
}