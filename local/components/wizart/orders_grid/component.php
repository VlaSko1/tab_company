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

$arResult['companyID'] = isset($_REQUEST['idCompany']) ? $_REQUEST['idCompany'] : '';

$arResult['data'] = $this->getData();

$arResult['list_id'] = $this::$gridName;
$arResult['filter_id'] = $this::$filterName;

$arResult['grid_options'] = new GridOptions($arResult['list_id']);
$arResult['sort'] = $arResult['grid_options'] -> GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);

$arResult['columns'] = [];
$arResult['columns'][] = ['id' => 'trackNumber', 'name' => 'Трек-номер', 'sort' => 'trackNumber', 'default' => true];
$arResult['columns'][] = ['id' => 'invoiceNumber', 'name' => 'Номер ЭН', 'sort' => 'invoiceNumber', 'default' => true];
$arResult['columns'][] = ['id' => 'orderNumber', 'name' => 'Номер заказа ИМ', 'sort' => 'orderNumber', 'default' => true];
$arResult['columns'][] = ['id' => 'createDate', 'name' => 'Дата создания заказа', 'sort' => 'createDate', 'default' => true];
$arResult['columns'][] = ['id' => 'storeDate', 'name' => 'Срок хранения', 'sort' => 'storeDate', 'default' => true];
$arResult['columns'][] = ['id' => 'return', 'name' => 'Признак возврата', 'sort' => 'return', 'default' => true];
$arResult['columns'][] = ['id' => 'declaredValue', 'name' => 'Объявленная стоимость, руб.', 'sort' => 'declaredValue', 'default' => true];
$arResult['columns'][] = ['id' => 'amountPay', 'name' => 'Сумма к уплате, руб.', 'sort' => 'amountPay', 'default' => true];
$arResult['columns'][] = ['id' => 'deliveryCost', 'name' => 'Стоимость доставки, руб.', 'sort' => 'deliveryCost', 'default' => true];
$arResult['columns'][] = ['id' => 'paymentType', 'name' => 'Тип оплаты', 'sort' => 'paymentType', 'default' => true];
$arResult['columns'][] = ['id' => 'actualWeight', 'name' => 'Фактический вес, кг', 'sort' => 'actualWeight', 'default' => true];
$arResult['columns'][] = ['id' => 'deliveryType', 'name' => 'Вид доставки', 'sort' => 'deliveryType', 'default' => true];
$arResult['columns'][] = ['id' => 'issueType', 'name' => 'Тип выдачи', 'sort' => 'issueType', 'default' => true];
$arResult['columns'][] = ['id' => 'barcodes', 'name' => 'Места', 'sort' => 'barcodes', 'default' => true];

$data = $arResult['data']['parcels'];
for ($i = 0; $i < count($data); $i++) {
  
	$arResult['list'][] = [
		'data' => [
					"trackNumber" => $data[$i]['trackNumber'],
					"invoiceNumber" => $data[$i]['invoiceNumber'],
					"orderNumber" => $data[$i]['orderNumber'],
					"createDate" => $this->getDateTimeStrFromStr($data[$i]['createDate']),
					"storeDate" => $data[$i]['storeDate'],
					"return" => $data[$i]['return'] ? 'Да' : 'Нет',
					"declaredValue" => $this->getStrCost($data[$i]['declaredValue']),
					"amountPay" => $this->getStrCost($data[$i]['amountPay']),
					"deliveryCost" => $this->getStrCost($data[$i]['deliveryCost']),
					"paymentType" => $this->getPaymentType($data[$i]['paymentType']),
					"actualWeight" => $data[$i]['actualWeight'],
					"deliveryType" => $this->getDeliveryType($data[$i]['deliveryType']),
					"issueType" => $this->getIssueType($data[$i]['issueType']),
					"barcodes" => count($data[$i]['barcodes']),
			],
	];
	
}


$arResult['ui_filter'] = [
	['id' => 'trackNumber', 'name' => 'Трек-номер', 'type' => 'text', 'default' => true],
	['id' => 'invoiceNumber', 'name' => 'Номер ЭН', 'type' => 'text', ],
	['id' => 'orderNumber', 'name' => 'Номер заказа ИМ', 'type' => 'text', ],
	['id' => 'createDate', 'name' => 'Дата создания заказа', 'type' => 'date', 
		"exclude" => array(
			\Bitrix\Main\UI\Filter\DateType::YESTERDAY,
			\Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
			\Bitrix\Main\UI\Filter\DateType::TOMORROW,
			\Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
			\Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
			\Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
			\Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
			\Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
			\Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
			\Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
			\Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
			\Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
			\Bitrix\Main\UI\Filter\DateType::QUARTER,
			\Bitrix\Main\UI\Filter\DateType::YEAR,
			\Bitrix\Main\UI\Filter\DateType::EXACT,
			\Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
			\Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
			\Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
			\Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
			\Bitrix\Main\UI\Filter\DateType::MONTH,
		)
	],
	
];

$arResult['filterOption'] = new Bitrix\Main\UI\Filter\Options($arResult['filter_id']);


$arResult['filterData'] = $arResult['filterOption'] -> getFilter([]);

foreach ($arResult['filterData'] as $k => $v) {
	if($k == 'FIND' && $v) {
		$arResult['filterData'][] = array(
		"LOGIC" => "OR",
		array("trackNumber" => "%".$v."%"),
		array("invoiceNumber" => "%".$v."%"),
		array("orderNumber" => "%".$v."%"),
		array('createDate' => "%".$v."%"),
		);
	} else if ($k == 'trackNumber' && $v) {
		$arResult['filterData']['trackNumber'] = "%".$v."%";
	} else if ($k == 'invoiceNumber' && $v) {
		$arResult['filterData']['invoiceNumber'] = "%".$v."%";
	} else if ($k == 'orderNumber' && $v) {
		$arResult['filterData']['orderNumber'] = "%".$v."%";
	} else if ($k == 'createDate_to' && $v) {
		$arResult['filterData']['createDate'] = "%". explode(' ', $v)[0] ."%";
	}  else {
		$arResult['filterData'][$k] = $v;
	}
}
$arResult['filterData']['ACTIVE'] = "Y";

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

$this->includeComponentTemplate();
