<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Application;

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';

if ($siteID !== '') {
    define('SITE_ID', $siteID);
}


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Header('Content-Type: text/html; charset=' . LANG_CHARSET);

global $APPLICATION;
$APPLICATION->ShowAjaxHead();

$request = Application::getInstance()->getContext()->getRequest();

$componentData = $request->get('PARAMS');

if(is_array($componentData)){
    $componentParams = isset($componentData['params']) && is_array($componentData['params']) ? $componentData['params'] : array();
}

$server = $request->getServer();

$ajaxLoaderParams = array(
    'url' => $server->get('REQUEST_URI'),
    'method' => 'POST',
    'dataType' => 'ajax',
    'data' => array('PARAMS' => $componentData)
);

$componentParams['AJAX_LOADER'] = $ajaxLoaderParams;

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    [
        'PLAIN_VIEW' => true,
        'USE_PADDING' => true,
        'POPUP_COMPONENT_NAME' => 'wizart:orders_grid',
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        "USE_UI_TOOLBAR" => "Y",
        'POPUP_COMPONENT_PARAMS' => $componentParams,
    ]
);

\CMain::FinalActions();
