<?php 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arJsConfig = array( 
    'custom_main' => array( 
        'js' => '/local/js/custom_deal/deal.js', 
        //'css' => '/bitrix/js/custom/main.css', 
        'rel' => array(), 
    ) 
); 

foreach ($arJsConfig as $ext => $arExt) { 
    \CJSCore::RegisterExt($ext, $arExt); 
}


CUtil::InitJSCore(array('custom_main'));