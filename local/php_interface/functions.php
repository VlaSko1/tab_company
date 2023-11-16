<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

function onEntityDetailsTabs($entityID, $entityTypeID, $eventName, $eventTab) 
{
    if ($entityTypeID === 4) {
        
        $siteID = isset($_REQUEST['site']) ? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';

        if ($siteID !== '') {
            define('SITE_ID', $siteID);
        }


        $idCompany = $entityID; // id самой сущности
        $tabs = $eventTab;
        $tabs[] =array (                            
                       'id' => 'store_orders',
                        'name' => 'Заказы интернет-магазина',
                        'loader' => array(
                            'serviceUrl' => '/local/components/wizart/orders_grid/lazyload.ajax.php?site=' . 
                                \SITE_ID . '&' . \bitrix_sessid_get() . '&idCompany=' . $idCompany ),
                        );
                
                    
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}
