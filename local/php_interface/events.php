<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

//AddEventHandler('crm', 'onEntityDetailsTabsInitialized', 'onEntityDetailsTabs');

// Отлавливаем событие построения табов 
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager -> AddEventHandler('crm', 'onEntityDetailsTabsInitialized', 'onEntityDetailsTabs');
