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