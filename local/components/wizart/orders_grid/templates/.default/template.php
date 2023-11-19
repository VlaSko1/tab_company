<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	//use Bitrix\Main\Grid\Panel\Actions;
	use Bitrix\Main\Localization\Loc;

?>
<!--<h1><?php //echo $arResult['companyID'] ?></h1>
<h4><?php //print_r() ?></h4>-->
<?php if (!empty($arParams['AJAX_LOADER'])) { ?>
    <script>
        BX.addCustomEvent('Grid::beforeRequest', function (gridData, argse) {
            if (argse.gridId != '<?=$arResult['GRID_ID'];?>') {
                return;
            }

            argse.method = 'POST'
            argse.data = <?= \Bitrix\Main\Web\Json::encode($arParams['AJAX_LOADER']['data']) ?>
        });
    </script>
<?php } ?>
<?

?>

<?
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		[
			'FILTER_ID' => $arResult['filter_id'],
			//'GRID_ID' => $arResult['list_id'],
			'FILTER' => $arResult['ui_filter'],
			'ENABLE_LIVE_SEARCH' => false, 
			'ENABLE_LABEL' => true,
			"FILTER_PRESETS" => [
				"ordersForwardAndReturnActive" => [
					"name" => 'Заказы прямого и возвратного потока (активные)',
					"default" => 'false', // если true - пресет по умолчанию
					"fields" => [
						"withInactive" => "field_1_value",
						"FIELD_2_ID" => "field_2_value",
					]
				],
				"ordersForwardActiveAndNotActive" => [
					"name" => 'Заказы прямого потока (активные и неактивные)',
					"default" => 'false', // если true - пресет по умолчанию
					"fields" => [
						"FIELD_1_ID" => "field_1_value",
						"FIELD_2_ID" => "field_2_value",
					]
				],
				"ordersForwardAndReturnActiveAndNotActive" => [
					"name" => 'Заказы прямого и обратного потока (активные и неактивные)',
					"default" => 'false', // если true - пресет по умолчанию
					"fields" => [
						"FIELD_1_ID" => "field_1_value",
						"FIELD_2_ID" => "field_2_value",
					]
				],
				"ordersForwardActive" => [
					"name" => 'Заказы прямого потока (активные)',
					"default" => 'false', // если true - пресет по умолчанию
					"fields" => [
						"withInactive" => "false",
						"withReturn" => "false",
					]
				],
			]
		]
		
	);


?>

<?
	$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
		'GRID_ID' => $arResult['list_id'],
		'COLUMNS' => $arResult['columns'],
		'ROWS' => $arResult['list'],
		'SHOW_ROW_CHECKBOXES' => true,
		'NAV_OBJECT' => array(), //$arResult['nav'],
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
		'ALLOW_COLUMNS_SORT'        => true,
		'ALLOW_COLUMNS_RESIZE'      => true,
		'ALLOW_HORIZONTAL_SCROLL'   => true,
		'ALLOW_SORT'                => true,
		'ALLOW_PIN_HEADER'          => true,
		'AJAX_OPTION_HISTORY'       => 'N',
	]);
?>
