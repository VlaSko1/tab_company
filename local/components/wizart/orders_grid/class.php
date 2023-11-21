<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

\Bitrix\Main\Loader::includeModule('crm');


class AjaxComponent extends CBitrixComponent implements Controllerable
{
    protected $errorCollection;
    

    private const  GRID_ID = 'order_grid';

    private const FILTER_ID = 'order_filter';

    private static  $httpLink = 'https://middle-crm-predprod.middle-task.boxberry.ru/';

    private static $rest = 'v1/parcels';

    private static  $token = 'Hello';

    private static $arPaymentType = [
        1 => 'Предоплата',
        2 => 'Наличные',
        3 => 'Эквайринг',
    ];

    private static $arDeliveryType = [
        0 => 'Не определён',
        1 => 'Доставка до ПВЗ',
        2 => 'Курьерская доставка',
        3 => 'Доставка Почтой России',
        4 => 'Курьерская доставка 2.0',
    ];

    private static $arIssueType = [
        0 => "Без вскрытия",
        1 => "Со вскрытием",
        2 => "Частичная выдача",
    ];

    private static  $fild1CName = '';

    public function executeComponent()
    {
        $this->arResult['grid_id'] = static::GRID_ID;
        $this->arResult['filter_id'] = static::FILTER_ID;
        $this->filter = (new \Bitrix\Main\UI\Filter\Options($this->arResult['filter_id']))->getFilter();
        $this->grid = new \Bitrix\Main\Grid\Options($this->arResult['grid_id']);
        $sortOptions = $this->grid->GetSorting();
        $this->sortOptions = $sortOptions["sort"];
        $this->nav = new \Bitrix\Main\UI\PageNavigation( $this->arResult['grid_id']);
        $nav_params = $this->grid->GetNavParams();
        $this->nav->allowAllRecords(true)
            ->setPageSize($nav_params["nPageSize"])
            ->initFromUri();
        
        $this->arResult['params'] = $this->getParams();
        $this->arResult['filter'] = $this->getFilterOptions();
        $this->arResult['data'] = $this->getData();
        $this->prepareGrid();
        $this->prepareResult($this->arResult['list']);
        $this->includeComponentTemplate();
    }


    public function prepareResult($data, $filterData = null)
    {
        $this->arResult['rows_count'] = $this->nav->getRecordCount();
        $this->arResult['rows'] = $data;
        $this->arResult['nav'] = $this->nav;
        $this->arResult['filter_data'] = $filterData;
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

    private function getParams($inParams = [])
    {
        $this->arResult['companyID'] = isset($_REQUEST['idCompany']) ? $_REQUEST['idCompany'] : '';
        $params['searchParameters.shopCodes'] = 5555555;
        $params['page.offset'] = 0;
        $params['page.limit'] = 20;
        return $params;
    }

    /**
     * Get data from API
     * @params array - array params for link
     * @return data 
     */
    private function getData(array $params=[]) 
    {
        $fullLink = self::$httpLink . self::$rest . '?token=' . self::$token;
        
        $ch = curl_init();
        $token = self::$token;
        curl_setopt($ch, CURLOPT_URL, $fullLink . '&' . http_build_query($this->arResult['params']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $data = json_decode($response, true);

        return $data;
    }

    private function getFilterOptions()
    {
        $options = [
            ['id' => 'trackNumber', 'name' => 'Трек-номер', 'type' => 'text', 'default' => true],
            ['id' => 'invoiceNumber', 'name' => 'Номер ЭН', 'type' => 'text', ],
            ['id' => 'orderNumber', 'name' => 'Номер заказа ИМ', 'type' => 'text', ],
            ['id' => 'createDate', 'name' => 'Дата создания заказа', 'type' => 'date', 
                "exclude" => array(
                    \Bitrix\Main\UI\Filter\DateType::YESTERDAY,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
                    \Bitrix\Main\UI\Filter\DateType::TOMORROW,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
                    \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::QUARTER,
                    \Bitrix\Main\UI\Filter\DateType::YEAR,
                    \Bitrix\Main\UI\Filter\DateType::EXACT,
                    \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::MONTH,
                )
            ],
            
        ];
        
        return $options;
    }

    private function getFilter()
    {
        $filterOption = new \Bitrix\Main\UI\Filter\Options(static::FILTER_ID);
        $filterData = $filterOption->getFilter();
        $filter = [];

        $filter['=%message'] = "%" . $filterData['FIND'] . "%";
        foreach (static::getFilterOptions() as $filterOption)
        {
            $optionName = $filterOption['id'];
            $optionType = $filterOption['type'];

            if ($optionType === 'text') {
                if (empty($filterData[$optionName])) {
                    continue;
                }
                $filter[$optionName] = "%" . $filterData[$optionName] . "%";
            }
            else if ($optionType === 'date' || $optionType === 'number') {
                if (empty($filterData[$optionName."_from"])) {
                    continue;
                }
                $filter['>='.$optionName] = $filterData[$optionName."_from"]; 
                $filter['<='.$optionName] = $filterData[$optionName."_to"]; 
            }
            else if ($optionType === 'list') {
                if (empty($filterData[$optionName])) {
                    continue;
                }
                if (!is_array($filterData[$optionName])) {
                    $filterData[$optionName] = [$filterData[$optionName]];
                }
                $filter['@'.$optionName] = $filterData[$optionName];
            }
        }

        return $filter;
    }

    private function prepareGrid()
    {
        $this->arResult['grid_options'] = new GridOptions($this->arResult['grid_id']);
        $this->arResult['sort'] = $this->arResult['grid_options'] -> GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);

        $this->arResult['columns'] = [];
        $this->arResult['columns'][] = ['id' => 'trackNumber', 'name' => 'Трек-номер', 'sort' => 'trackNumber', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'invoiceNumber', 'name' => 'Номер ЭН', 'sort' => 'invoiceNumber', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'orderNumber', 'name' => 'Номер заказа ИМ', 'sort' => 'orderNumber', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'createDate', 'name' => 'Дата создания заказа', 'sort' => 'createDate', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'storeDate', 'name' => 'Срок хранения', 'sort' => 'storeDate', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'return', 'name' => 'Признак возврата', 'sort' => 'return', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'declaredValue', 'name' => 'Объявленная стоимость, руб.', 'sort' => 'declaredValue', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'amountPay', 'name' => 'Сумма к уплате, руб.', 'sort' => 'amountPay', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'deliveryCost', 'name' => 'Стоимость доставки, руб.', 'sort' => 'deliveryCost', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'paymentType', 'name' => 'Тип оплаты', 'sort' => 'paymentType', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'actualWeight', 'name' => 'Фактический вес, кг', 'sort' => 'actualWeight', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'deliveryType', 'name' => 'Вид доставки', 'sort' => 'deliveryType', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'issueType', 'name' => 'Тип выдачи', 'sort' => 'issueType', 'default' => true];
        $this->arResult['columns'][] = ['id' => 'barcodes', 'name' => 'Места', 'sort' => 'barcodes', 'default' => true];

        $data = $this->arResult['data']['parcels'];
        for ($i = 0; $i < count($data); $i++) {
        
            $this->arResult['list'][] = [
                'data' => [
                            "trackNumber" => $data[$i]['trackNumber'],
                            "invoiceNumber" => $data[$i]['invoiceNumber'],
                            "orderNumber" => $data[$i]['orderNumber'],
                            "createDate" => $this->getDateTimeStrFromStr($data[$i]['createDate']),
                            "storeDate" => $data[$i]['storeDate'],
                            "return" => $data[$i]['return'] ? 'Да' : 'Нет',
                            "declaredValue" => $this->getStrCost($data[$i]['declaredValue']),
                            "amountPay" => $this->getStrCost($data[$i]['amountPay']),
                            "deliveryCost" => $this->getStrCost($data[$i]['deliveryCost']),
                            "paymentType" => $this->getPaymentType($data[$i]['paymentType']),
                            "actualWeight" => $data[$i]['actualWeight'],
                            "deliveryType" => $this->getDeliveryType($data[$i]['deliveryType']),
                            "issueType" => $this->getIssueType($data[$i]['issueType']),
                            "barcodes" => count($data[$i]['barcodes']),
                    ],
            ];
            
        }
    }
    
    private function getDateTimeStrFromStr(string $stringDate)
    {
        $dateObj = date_create($stringDate);
        return date_format($dateObj, "d.m.Y H:i:s");
    }

    private function getStrCost(int $intCost)
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

    private function getPaymentType(int $numberPaymentType)
    {
        if (array_key_exists($numberPaymentType, self::$arPaymentType)) {
            return self::$arPaymentType[$numberPaymentType];
        }
        return $numberPaymentType;
    }

    private function getDeliveryType(int $numberDeliveryType)
    {
        if (array_key_exists($numberDeliveryType, self::$arDeliveryType)) {
            return self::$arDeliveryType[$numberDeliveryType];
        }
        return $numberDeliveryType;
    }

    private function getIssueType(array $arData)
    {
        if (array_key_exists($arData[0], self::$arIssueType)) {
            return self::$arIssueType[$arData[0]];
        }
        return $arData;
    }

    public function setPageSizeAction($pageSize)
    {
        var_dump($pageSize); die();
    }
}
