<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CJSCore::Init(array('ajax', 'window', "jquery", "ui", 'popup', "sidepanel"));
$APPLICATION->SetTitle("Workspace");
\Bitrix\Main\Loader::IncludeModule('crm');
\Bitrix\Main\UI\Extension::load('ui.entity-selector');
\Bitrix\Main\Loader::includeModule('ui'); 

CModule::IncludeModule("iblock");
use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

$arResult['iblock_id'] = CIBlock::GetList(array(), array("CODE" => $this::$simbolCode), false, false, array("IBLOCK_ID"))->GetNext()['ID'];
$arResult['list_id'] = $this::$gridName;
$arResult['grid_options'] = new GridOptions($arResult['list_id']);
$arResult['sort'] = $arResult['grid_options'] -> GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
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

$res = CIBlockElement::GetList(
	$arResult['sort']['sort'],
	$arResult['filterData'],
	false,
	$arResult['nav_params'],
	['ID', 'NAME', 'PROPERTY_FIRST_NAME', 'PROPERTY_LAST_NAME', 'PROPERTY_EMAIL', 'PROPERTY_BIRTH_DATE', 'PROPERTY_PHONE', 'PROPERTY_CITY', 'PROPERTY_FILE', 'PROPERTY_DEAL_BINDING']
);

$arResult['columns'] = [];
$arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true];
$arResult['columns'][] = ['id' => 'NAME', 'name' => 'Название', 'sort' => 'NAME', 'default' => true];
$arResult['columns'][] = ['id' => 'FIRST_NAME', 'name' => 'Имя', 'sort' => 'PROPERTY_FIRST_NAME', 'default' => true];
$arResult['columns'][] = ['id' => 'LAST_NAME', 'name' => 'Фамилия', 'sort' => 'PROPERTY_LAST_NAME', 'default' => true];
$arResult['columns'][] = ['id' => 'EMAIL', 'name' => 'Email', 'sort' => 'PROPERTY_EMAIL', 'default' => true];
$arResult['columns'][] = ['id' => 'BIRTH_DATE', 'name' => 'День рождения', 'sort' => 'PROPERTY_BIRTH_DATE', 'default' => true];
$arResult['columns'][] = ['id' => 'PHONE', 'name' => 'Телефон', 'sort' => 'PROPERTY_PHONE', 'default' => true];
$arResult['columns'][] = ['id' => 'CITY', 'name' => 'Город', 'sort' => 'PROPERTY_CITY', 'default' => true];
$arResult['columns'][] = ['id' => 'FILE', 'name' => 'Файл', 'sort' => 'PROPERTY_FILE', 'default' => true];
$arResult['columns'][] = ['id' => 'DEAL_BINDING', 'name' => 'Сделка', 'sort' => 'PROPERTY_DEAL_BINDING', 'default' => true]; 

while($row = $res->GetNext()) {
	$file = CFile::ShowImage($row['PROPERTY_FILE_VALUE'], 100, 100, "border=0", "", true);
	$arDealRes = CCrmDeal::GetByID($row['PROPERTY_DEAL_BINDING_VALUE']);
	$getDeal = null;
	if ($arDealRes) {
	$getDeal = "<a href='/crm/deal/details/{$arDealRes['ID']}/' 
							target='_blank' 
							onmouseover=\"dealViewInfo('{$arDealRes['ASSIGNED_BY_NAME']}', '{$arDealRes['ASSIGNED_BY_LAST_NAME']}', {$arDealRes['ASSIGNED_BY_ID']}, {$arDealRes['OPPORTUNITY']}, this)\">
									{$arDealRes['TITLE']}								
					</a>";
	}

	$action = [
		[
			'text' => 'Просмотр',
			'default' => true,
			'onclick' => "detailEl(" . $row['ID'] . ")", 
		], 
		[
			'text' => 'Удалить',
			'default' => true,
			'onclick' => "BX.ajax.runComponentAction('wizart:workspace','ajaxRequestDel',{
						mode: 'class',
						data: {
							'json[]': '" . $row['ID'] . "'
						}
						}).then(r => r?BX.Main.gridManager.getInstanceById('" . $arResult['list_id'] . "').reloadTable():location.reload());"
		],
	];

	if (($row['PROPERTY_DEAL_BINDING_VALUE'] === null) OR ($row['PROPERTY_DEAL_BINDING_VALUE'] == 'D_')){
        $action[] = [
            'text'    => 'Связать c',
            'default' => false,
            'onclick' => 'bindDeal('.$row['ID'].', \''. $arResult['list_id'].'\')'
        ];
  }
  
	$arResult['list'][] = [
		'data' => [
					"ID" => $row['ID'],
					"NAME" => $row['NAME'],
					"FIRST_NAME" => $row['PROPERTY_FIRST_NAME_VALUE'],
					"LAST_NAME" => $row['PROPERTY_LAST_NAME_VALUE'],
					"EMAIL" => $row['PROPERTY_EMAIL_VALUE'],
					"BIRTH_DATE" => $row['PROPERTY_BIRTH_DATE_VALUE'],
					"PHONE" => $row['PROPERTY_PHONE_VALUE'],
					"CITY" => $row['PROPERTY_CITY_VALUE'],
					"FILE" => $file,
					'DEAL_BINDING' => $getDeal,
			],
		'actions' => $action,
	];
	
}

$arResult['generalButton'] = new \Bitrix\UI\Buttons\Split\ApplyButton([
  	'text' => "Общее"
]);

$arResult['settingsButton'] = new \Bitrix\UI\Buttons\SettingsButton([
  
]);

$arResult['createButton'] = new \Bitrix\UI\Buttons\CreateButton([
	"click" => new \Bitrix\UI\Buttons\JsCode(
		"BX.SidePanel.Instance.open('/form_sidepanel/index.php', {
			'width': 800,
		})" 
	),
	"text" => "Добавить"
]);

$arResult['excelButton'] = new \Bitrix\UI\Buttons\Button([
	"color" => \Bitrix\UI\Buttons\Color::SECONDARY,
	"click" => new \Bitrix\UI\Buttons\JsCode(
		"clickBtnExcel('".$arResult['list_id']."');"
	),
	"text" => "Выгрузка в Excel"
]);

$this->includeComponentTemplate();
