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

    public static $filterName = 'order_filter';

    public static  $httpLink = 'https://middle-crm-predprod.middle-task.boxberry.ru/';

    public static $rest = 'v1/parcels';

    public static  $token = 'Hello';

    public static $arPaymentType = [
        1 => 'Предоплата',
        2 => 'Наличные',
        3 => 'Эквайринг',
    ];

    public static $arDeliveryType = [
        0 => 'Не определён',
        1 => 'Доставка до ПВЗ',
        2 => 'Курьерская доставка',
        3 => 'Доставка Почтой России',
        4 => 'Курьерская доставка 2.0',
    ];

    public static $arIssueType = [
        0 => "Без вскрытия",
        1 => "Со вскрытием",
        2 => "Частичная выдача",
    ];

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

    public function getDateTimeStrFromStr(string $stringDate)
    {
        $dateObj = date_create($stringDate);
        return date_format($dateObj, "d.m.Y H:i:s");
    }

    public function getStrCost(int $intCost)
    {
        if ($intCost === 0) {
            return 0;
        }
        if ($intCost < 10) {
            return '0.0' . strval($intCost);
        }
        if ($intCost < 100 ) {
            return '0.' . strval($intCost); 
        }

        $strCost = strval($intCost);
        
        $strCostInt = substr($strCost, 0, strlen($strCost) - 2);
        $strCostFract = substr($strCost, strlen($strCost) - 2, 2);
        
        $strCostResult = ''; 
        if (intval($strCostFract) !== 0) {
            $strCostResult .= '.' . $strCostFract; 
        }
        while(true) {
            if (strlen($strCostInt) > 3) {
                $strCostResult = ' ' . substr($strCostInt, strlen($strCostInt) - 3, 3) . $strCostResult;
                $strCostInt = substr($strCostInt, 0, strlen($strCostInt) - 3);
            } else {
                $strCostResult = $strCostInt . $strCostResult;
                break;
            }
        }

        return $strCostResult;
    }

    public function getPaymentType(int $numberPaymentType)
    {
        if (array_key_exists($numberPaymentType, self::$arPaymentType)) {
            return self::$arPaymentType[$numberPaymentType];
        }
        return $numberPaymentType;
    }

    public function getDeliveryType(int $numberDeliveryType)
    {
        if (array_key_exists($numberDeliveryType, self::$arDeliveryType)) {
            return self::$arDeliveryType[$numberDeliveryType];
        }
        return $numberDeliveryType;
    }

    public function getIssueType(array $arData)
    {
        if (array_key_exists($arData[0], self::$arIssueType)) {
            return self::$arIssueType[$arData[0]];
        }
        return $arData;
    }
}
