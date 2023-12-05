<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Localization\Loc;
\Bitrix\Main\Loader::includeModule('ui'); */

?>

<?
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		[
			'FILTER_ID' => $arResult['grid_id'],
			'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
			'GRID_ID' => $arResult['grid_id'],
			'FILTER' => $arResult['filter'],
			'ENABLE_LIVE_SEARCH' => false, 
			'ENABLE_LABEL' => true,
		]
	
	);
?>
<?
	$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
		'GRID_ID' => $arResult['grid_id'],
		'COLUMNS' => $arResult['columns'],
		'ROWS' => $arResult['list'],
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
		'TOTAL_ROWS_COUNT' 			=> $arResult['total'],
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