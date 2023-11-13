<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	use Bitrix\Main\Grid\Panel\Actions;
	use Bitrix\Main\Localization\Loc;

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($arResult['generalButton']);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($arResult['settingsButton']);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($arResult['createButton']);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($arResult['excelButton']);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
		'GRID_ID' => $arResult['list_id'],
		'FILTER_ID' => $arResult['list_id'],
		'FILTER' => $arResult['ui_filter'],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
	]);
?>
	<div id="addDeal" style="display: none">
  	<p>Связать элемент <b>ID</b> = <span id="bind_id"></span> со сделкой:</p>
<?

	$APPLICATION->IncludeComponent(
			'bitrix:main.user.selector',
			' ',
			[
				"ID" => "addDeal",
				"API_VERSION" => 3,
				"LIST" => ['not_deal' => 'not_deal'],
				"INPUT_NAME" => "fields",
				"USE_SYMBOLIC_ID" => true,
				"BUTTON_SELECT_CAPTION" => Loc::getMessage("MAIL_CLIENT_CONFIG_CRM_QUEUE_ADD"),
				"SELECTOR_OPTIONS" =>
					[
						'crmPrefixType' => 'SHORT',
						'enableCrm' => 'Y',
						'enableCrmDeals' => 'Y',
						'addTabCrmDeals' => 'Y',
						'enableUsers' => 'N',
					]
			]
	);
?>
</div>

<?
	$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
		'GRID_ID' => $arResult['list_id'],
		'COLUMNS' => $arResult['columns'],
		'ROWS' => $arResult['list'],
		'SHOW_ROW_CHECKBOXES' => true,
		'NAV_OBJECT' => $arResult['nav'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		'PAGE_SIZES' =>  [
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100']
		],
		'AJAX_OPTION_JUMP'          => 'N',
		'TOTAL_ROWS_COUNT' 					=> count($arResult['list']),
		'SHOW_CHECK_ALL_CHECKBOXES' => true,
		'SHOW_ROW_ACTIONS_MENU'     => true,
		'SHOW_GRID_SETTINGS_MENU'   => true,
		'SHOW_NAVIGATION_PANEL'     => true,
		'SHOW_PAGINATION'           => true,
		'SHOW_SELECTED_COUNTER'     => true,
		'SHOW_TOTAL_COUNTER'        => true,
		'SHOW_PAGESIZE'             => true,
		'SHOW_ACTION_PANEL'         => true,
		'ACTION_PANEL' 				=> [
			'GROUPS' => [ 
				'TYPE' => [ 
					'ITEMS' => [ 
						[
							'ID' => 'delete',
							'TYPE' => 'BUTTON',
							'TEXT' => 'Удалить',
							'CLASS' => 'icon remove',
							'ONCHANGE' => [
								[
									'ACTION' => Actions::CALLBACK,
									'CONFIRM' => true,
									'CONFIRM_APPLY_BUTTON' => 'Подтвердить',
									'DATA' => array(
										array(
											'JS' => "
												let json = BX.Main.gridManager.getById('" . $arResult['list_id'] . "').instance.rows.getSelectedIds()
												
												BX.ajax.runComponentAction('wizart:workspace','ajaxRequestDel',{
													mode: 'class',
													data: {
														json
													}			
												}).then(r => r?BX.Main.gridManager.getInstanceById('" . $arResult['list_id'] . "').reloadTable():location.reload());
											",
										),
									),
								]
							]
						],
					], 
				] 
			], 
		],
		'ALLOW_COLUMNS_SORT'        => true,
		'ALLOW_COLUMNS_RESIZE'      => true,
		'ALLOW_HORIZONTAL_SCROLL'   => true,
		'ALLOW_SORT'                => true,
		'ALLOW_PIN_HEADER'          => true,
		'AJAX_OPTION_HISTORY'       => 'N',
	]);
?>
