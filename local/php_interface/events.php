<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Событие происходит после формирования буфера - всем внешним ссылкам добавляется атрибут rel со значением 'noreferrer'
AddEventHandler("main", "OnEndBufferContent", "ChangeExternalLink");

// Другой вариант навешивания обработчика на событие использования документогенератора (до создания документа).
// Нужна для добавления кастомных плейсехолдеров из заранее загруженного шаблона перед генерацией документа по выбранному шаблону
\Bitrix\Main\EventManager::getInstance()->addEventHandler('documentgenerator', 'onBeforeProcessDocument', 'onBeforeProcessDocument');

// Событие происходит после изменения элемента инфоблока заявок, если произошла привязка сделки
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", 'startBusinessProcessIfBindingDeal');

// Событие происходит после изменения элемента инфоблока заявок (в кастомном гриде), если произошла привязка сделки
AddEventHandler('iblock', "OnIBlockElementSetPropertyValuesEx", 'startBusinessSetPropertyValuesEx');

// Происходит после изменения сделки (меняется статус сделки и если изменяет не админ - отказ)
AddEventHandler('crm', "OnBeforeCrmDealUpdate", 'stopChangeStatusDeal');

//AddEventHandler('crm', 'onEntityDetailsTabsInitialized', 'onEntityDetailsTabs');

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('crm', 'onEntityDetailsTabsInitialized', [
        'wizart\\CustomCrm\\Handler',
        'setCustomTabs'
    ]
);
