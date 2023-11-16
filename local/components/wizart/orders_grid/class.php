<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;


\Bitrix\Main\Loader::includeModule('crm');


class AjaxComponent extends CBitrixComponent implements Controllerable
{
    protected $errorCollection;
    

    public static  $gridName = 'order_grid';

    public static  $httpLink = 'https://middle-crm-predprod.middle-task.boxberry.ru/';

    public static $rest = 'v1/parcels';

    public static  $token = 'Hello';


    public static  $fild1CName = '';

    public function sortNavGet()
    {

    }

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        return [];
    }

    public function configureActions(): array
    {
        return [
            'send' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                ]
            ]
        ];
    }

    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }


    /**
     * Get data from API
     * @params array - array params for link
     * @return data 
     */
    public function getData(array $params=[]) 
    {
        $fullLink = self::$httpLink . self::$rest . '?token=' . self::$token;
        $params['searchParameters.shopCodes'] = 5555555;
        $params['page.offset'] = 0;
        $params['page.limit'] = 20;
        $ch = curl_init();
        $token = self::$token;
        curl_setopt($ch, CURLOPT_URL, $fullLink . '&' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $data = json_decode($response, true);

        return $data;
    }
}
