<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Page\Asset as Asset;


CJSCore::Init(array('ajax', "ui", 'popup', "sidepanel", 'window', "jquery"));


$this->__hasCSS = true;
$this->__hasJS = true;

$this->addExternalJs($this->GetFolder() . '/script.js');
$this->addExternalCss($this->GetFolder() . '/style.css');

// получим массив групп текущего пользователя
global $USER;
$arGroups = $USER->GetUserGroupArray();

if (!in_array('1', $arGroups, true)) {
    $this->addExternalJs($this->GetFolder() . '/js/dont_stage_change_deal.js');
}

$this->__file = '/bitrix/components/bitrix/crm.deal.list/templates/.default/template.php';
$this->__folder = '/bitrix/components/bitrix/crm.deal.list/templates/.default';


$arResult['HEADERS'][] = [
	'id' => ID_NEW_COLUMN, 
	'name' => 'Привязка к заявке', 
	'sort' => 'binding_iblock', 
	'width' => 200, 
	'default' => true, 
	'editable' => true 
];

// Получаем список всех элементов инфоблока с их названием, id и привязкой к сделке по его символьному коду SYMBOL_CODE
$arListIblock = getListIblock(SYMBOL_CODE);

foreach($arResult['DEAL'] as $key => $value) 
{
	if ($result = existValInArray($value['ID'], $arListIblock)) {
		$arResult['DEAL'][$key][ID_NEW_COLUMN] = "<a target='_self' href='/application/linked/{$result}'>Открыть</a>";
	} 
	else
	{
		$strNoDeal = getNotDealApp($arListIblock) . "_" . $value['ID'];
		$arResult['DEAL'][$key][ID_NEW_COLUMN] = "<a target='_self' href='/application/notlinked/{$strNoDeal}'>Добавить элемент</a>";
	}
}

?>
<div id="addApp" style="display: none" >
  <p>Связать сделку <b>ID</b> = <span id="bind_id"></span> с заявкой:</p>
	<?
		$iblockId = CIBlock::GetList(array(), array("CODE" => SYMBOL_CODE), false, false, array("IBLOCK_ID"))->GetNext()['ID'];
		$APPLICATION->includeComponent('bitrix:iblock.element.selector', '',
			array(
				"ID" => "addApp",
				'SELECTOR_ID' => SYMBOL_CODE,
				'IBLOCK_ID' => $iblockId,
				'MULTIPLE' => 'N',
				'PANEL_SELECTED_VALUES' => 'N',
				'POPUP' => 'popupDeal',
			),
		null, array('HIDE_ICONS' => 'Y')
	);
	?>                                        
</div>
<?
