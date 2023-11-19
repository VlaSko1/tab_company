<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CJSCore::Init(array('ajax', 'window', "jquery", "ui", 'popup', "sidepanel"));
$APPLICATION->SetTitle("Orders");
\Bitrix\Main\Loader::IncludeModule('crm');
\Bitrix\Main\UI\Extension::load('ui.entity-selector');
\Bitrix\Main\Loader::includeModule('ui'); 

//CModule::IncludeModule("iblock");
//use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

/*$arResult['companyID'] = isset($_REQUEST['idCompany']) ? $_REQUEST['idCompany'] : '';

$arResult['data'] = $this->getData();

$arResult['list_id'] = $this::$gridName;
$arResult['filter_id'] = $this::$filterName;*/






/*



$arResult['nav_params'] = $arResult['grid_options'] -> GetNavParams();
$arResult['nav'] = new PageNavigation($arResult['list_id']);
$arResult['nav'] -> allowAllRecords(true)
                -> setPageSize($arResult['nav_params']['nPageSize'])
                -> initFromUri();
	
if ($arResult['nav']->allRecordsShown()) {
	$arResult['nav_params'] = false;
} else {
	$arResult['nav_params']['iNumPage'] = $arResult['nav'] -> getCurrentPage();
}


$arResult['ui_filter'] = [
	['id' => 'NAME', 'name' => 'Название', 'type' => 'text', 'default' => true],
	['id' => 'FIRST_NAME', 'name' => 'Имя', 'type' => 'text', ],
	['id' => 'LAST_NAME', 'name' => 'Фамилия', 'type' => 'text', ],
	['id' => 'EMAIL', 'name' => 'Email', 'type' => 'text'],
	['id' => 'BIRTH_DATE', 'name' => 'День рождения', 'type' => 'date'],
	['id' => 'PHONE', 'name' => 'Телефон', 'type' => 'text'],
	['id' => 'CITY', 'name' => 'Город', 'type' => 'entity_selector', 'params' => [
			'multiple' => 'Y',
			'dialogOptions' => [
				'items' => $this->getItemsCity($arResult['iblock_id']),
			'tabs' => [
				[ 'id' => 'my-tab', 'title' => 'Города' ]
			],
			'showAvatars' => false,
			]
    	]
	],
	['id' => 'DEAL_BINDING', 'name' => 'Привязка к сделке', 'type' => 'dest_selector', 'params' => [
		'enableCrm' => 'Y',
		'enableEmpty' => 'Y',
		'enableCrmDeals' => "Y",
		'contextCode' => 'CRM',
	]],
];

$arResult['filterOption'] = new Bitrix\Main\UI\Filter\Options($arResult['list_id']);
$arResult['filterData'] = $arResult['filterOption'] -> getFilter([]);
	
foreach ($arResult['filterData'] as $k => $v) {
	if($k == 'FIND' && $v) {
		$arResult['filterData'][] = array(
		"LOGIC" => "OR",
		array("NAME" => "%".$v."%"),
		array("PROPERTY_FIRST_NAME" => "%".$v."%"),
		array("PROPERTY_LAST_NAME" => "%".$v."%"),
		array('PROPERTY_EMAIL' => "%".$v."%"),
		array('PROPERTY_BIRTH_DATE' => "%".$v."%"),
		array('PROPERTY_PHONE' => "%".$v."%"),
		array('PROPERTY_CITY' => "%".$v."%"),
		);
	} else if ($k == 'NAME' && $v) {
		$arResult['filterData']['NAME'] = "%".$v."%";
	} else if ($k == 'FIRST_NAME' && $v) {
		$arResult['filterData']['PROPERTY_FIRST_NAME'] = "%".$v."%";
	} else if ($k == 'LAST_NAME' && $v) {
		$arResult['filterData']['PROPERTY_LAST_NAME'] = "%".$v."%";
	} else if ($k == 'EMAIL' && $v) {
		$arResult['filterData']['PROPERTY_EMAIL'] = "%".$v."%";
	} else if ($k == 'BIRTH_DATE_to' && $v) {
		$arResult['filterData']['PROPERTY_BIRTH_DATE'] = "%". explode(' ', $v)[0] ."%";
	} else if ($k == 'PHONE' && $v) {
		$arResult['filterData']['PROPERTY_PHONE'] = "%".$v."%";
	} else if ($k == 'CITY_label' && $v) {
		$arResult['filterData']['PROPERTY_CITY'] = array(...$v);
	}	else if ($k == 'DEAL_BINDING' && $v == 'EMPTY') {
		$arResult['filterData']['PROPERTY_DEAL_BINDING'] = false;
	} else if ($k == 'DEAL' && $v) {
		$arResult['filterData']['PROPERTY_DEAL_BINDING'] = substr($v, 7);
	}  else {
		$arResult['filterData'][$k] = $v;
	}
}

$arResult['filterData']['IBLOCK_ID'] = $arResult['iblock_id'];
$arResult['filterData']['ACTIVE'] = "Y";


*/

//$this->includeComponentTemplate();
