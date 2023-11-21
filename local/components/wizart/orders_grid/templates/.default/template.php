<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//use Bitrix\Main\Grid\Panel\Actions;
//use Bitrix\Main\Localization\Loc;
/*\Bitrix\Main\Loader::includeModule('ui'); 
$filterParams = [
	'GRID_ID' => $arResult['grid_id'],
	'FILTER_ID' => $arResult['filter_id'],
	'FILTER' => $arResult['filter'],
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
	],
	'ENABLE_LIVE_SEARCH' => true,
	'ENABLE_LABEL' => true,
	
];
\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);*/

?>
<?php if (!empty($arParams['AJAX_LOADER'])) { ?>
    <script>
        BX.addCustomEvent('Grid::beforeRequest', function (gridData, argse) {
            if (argse.gridId != '<?=$arResult['grid_id'];?>') {
                return;
            }

            argse.method = 'POST'
            argse.data = <?= \Bitrix\Main\Web\Json::encode($arParams['AJAX_LOADER']['data']) ?>
        });
    </script>
<?php } ?>

<?
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		[
			'FILTER_ID' => $arResult['filter_id'],
			'GRID_ID' => $arResult['grid_id'],
			'FILTER' => $arResult['filter'],
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
		'GRID_ID' => $arResult['grid_id'],
		'COLUMNS' => $arResult['columns'],
		'ROWS' => $arResult['rows'],
		"NAV_OBJECT" => $arResult['nav'],
		'SHOW_ROW_CHECKBOXES' => true,
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
		'PAGE_SIZES' =>  [
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100']
		],
		'AJAX_OPTION_JUMP'          => 'N',
		'TOTAL_ROWS_COUNT' 			=> $arResult['rows_count'],
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
