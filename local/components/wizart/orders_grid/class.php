<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main;
use \Bitrix\Crm;

\Bitrix\Main\Loader::includeModule('ui');

\Bitrix\Main\UI\Extension::load('ui.entity-selector');
CJSCore::Init(array('ajax', 'window', "jquery", "ui", 'popup', "sidepanel"));

\Bitrix\Main\Loader::includeModule('crm');


class AjaxComponent extends CBitrixComponent implements Controllerable
{
    protected $errorCollection;
    
    private const  GRID_ID = 'order_grid';

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

    public $arPresets = [
        "ordersForwardAndReturnActive" => [
            "name" => 'Заказы прямого и возвратного потока (активные)',
			"default" => 'false', // если true - пресет по умолчанию
			"fields" => [
                "withInactive" => 'false',
                "withReturn" => 'true',
            ],
            'filter_rows' => 'withInactive,withReturn,return',
        ],
        "ordersForwardActiveAndNotActive" => [
            "name" => 'Заказы прямого потока (активные и неактивные)',
			"default" => false, // если true - пресет по умолчанию
			"fields" => [
                "withInactive" => 'true',
                "withReturn" => 'false',
            ],
            'filter_rows' => 'withInactive,withReturn,return',
        ],
        "ordersForwardAndReturnActiveAndNotActive" => [
            "name" => 'Заказы прямого и возвратного потока (активные и неактивные)',
			"default" => 'false', // если true - пресет по умолчанию
			"fields" => [
                "withInactive" => 'true',
                "withReturn" => 'true',
            ],
            'filter_rows' => 'withInactive,withReturn,return',
        ],
        "ordersForwardActive" => [
            "name" => 'Заказы прямого потока (активные)',
			"default" => 'true', // если true - пресет по умолчанию
			"fields" => [
                "withInactive" => 'false',
                "withReturn" => 'false',
            ],
            'filter_rows' => 'withInactive,withReturn,return',
        ],
    ];

    public static  $fild1CName = 'UF_CRM_1520882978';

    public function executeComponent()
    {
        $this->arResult['grid_id'] = static::GRID_ID;
        
        $this->arResult['grid_options'] = new GridOptions($this->arResult['grid_id']);
        
        $this->getNavParam();
                
        $this->getAllFilter();
        $this->arResult['params'] = $this->getParams($this->arResult['filterData']);

        $this->prepareGrid();
        
        $this->includeComponentTemplate();
    }

    private function getNavParam()
    {
        $this->arResult['nav_params'] = $this->arResult['grid_options'] -> GetNavParams();
        
        $this->arResult['nav'] = new PageNavigation($this->arResult['grid_id']);
        $this->arResult['nav'] -> allowAllRecords(true)
                        -> setPageSize($this->arResult['nav_params']['nPageSize'])
                        -> initFromUri();
        
        if ($this->arResult['nav']->allRecordsShown()) {
            $this->arResult['nav_params'] = false;
        } else {
            $this->arResult['nav_params']['iNumPage'] = $this->arResult['nav'] -> getCurrentPage();
        }
    }

    public function getAllFilter() 
    {
        $this->arResult['filter'] = $this->getFilterOptions();
        
        $this->arResult['filterOption'] = new Bitrix\Main\UI\Filter\Options($this->arResult['grid_id']);
       
        $this->arResult['FILTER_PRESETS'] = $this->arPresets;

        $this->arResult['filterData'] = $this->arResult['filterOption'] -> getFilter([]);

        foreach ($this->arResult['filterData'] as $k => $v) {
            $v = strip_tags($v);
            if($k == 'FIND' && $v) {
                $this->arResult['filterData']['find'] = array(
                    "trackNumber" => $v,
                    "invoiceNumber" => $v,
                    "orderNumber" => $v,
                );
            } else if ($k == 'searchParameters.numberParcels' && $v) {
                $this->arResult['filterData']['searchParameters.numberParcels'] = $v;
            } else if ($k == 'searchParameters.invoiceNumber' && $v) {
                $this->arResult['filterData']['searchParameters.invoiceNumber'] = $v;
            } else if ($k == 'createDate_from' && $v) {
                $this->arResult['filterData']['createDate_from'] = $v;
            } else if ($k == 'createDate_to' && $v) {
                $this->arResult['filterData']['createDate_to'] = $v;
            } else if ($k == 'PRESET_ID' && $v) {
                $this->arResult['filterData']['PRESET_ID'] = $v;
            } else if ($k == 'preset_id' && $v) {
                $this->arResult['filterData']['PRESET_ID'] = $v;
            } else {
                $this->arResult['filterData'][$k] = $v;
            }
        }
        

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
        $params = [];
        if ($inParams['searchParameters.numberParcels']) {
            $params['searchParameters.numberParcels'] = $inParams['searchParameters.numberParcels'];
        }

        if ($inParams['searchParameters.invoiceNumber']) {
            $params['searchParameters.invoiceNumbers'] = $inParams['searchParameters.invoiceNumber'];
        }

        if ($inParams['createDate_from']) {
            $params['createDateStart'] = $this->getDateStrFromStr($inParams['createDate_from']);
        }

        if ($inParams['createDate_to']) {
            $params['createDateEnd'] = $this->getDateStrFromStr($inParams['createDate_to']);
        }

        if ($inParams['PRESET_ID']) {
            if ($this->arPresets[$inParams['PRESET_ID']]) {
                
                foreach ($this->arPresets[$inParams['PRESET_ID']]['fields'] as $key => $value) {
                    $params[$key] = $value;
                }
            } else {
                $params['withInactive'] = 'false';
                $params['withReturn'] = 'false';
            }
        }

        // Получаем код компании
        $this->arResult['companyID'] = isset($_REQUEST['idCompany']) ? $_REQUEST['idCompany'] : '';
        
        
        // Получаем "Код контрагента в 1С" по которому будем искать заказы
        $this->arResult['shopCodes'] = $this->getCodeCounterparty();

        if (!$this->arResult['shopCodes']) {
            $this->arResult['error'] = 'Нет данных. Не заполнен код компании.';
        }

        $params['searchParameters.shopCodes'] = $this->arResult['shopCodes'];  //5555555
        $params['page.offset'] = ($this->arResult['nav_params']['iNumPage'] - 1) * $this->arResult['nav_params']['nPageSize'];
        $params['page.limit'] = $this->arResult['nav_params']['nPageSize'];
        return $params;
    }

    private function getCodeCounterparty()
    {
        Main\Loader::IncludeModule('crm');

        $companies = Crm\CompanyTable::getList([
            'filter' => ['ID' => $this->arResult['companyID']],
            'select' => [
                self::$fild1CName,
            
            ]
        ]);
        
        foreach ($companies as $company)
        {
            $result = $company[self::$fild1CName];
        }
        return $result;
    }


    /**
     * Get data from API
     * @params array - array params for link
     * @return data 
     */
    private function getData(array $params=[]) 
    {
        $fullLink = self::$httpLink . self::$rest . '?token=' . 'token';
        
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
            ['id' => 'searchParameters.numberParcels', 'name' => 'Трек-номер/номер заказа ИМ', 'type' => 'text', 'default' => true],
            ['id' => 'searchParameters.invoiceNumber', 'name' => 'Номер ЭН', 'type' => 'text', ],
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

    private function prepareGrid()
    {
        $this->arResult['data'] = $this->getData();

        $this->arResult['sort'] = $this->arResult['grid_options'] -> GetSorting(['sort' => ['createDate' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        
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

        if ($this->arResult['data']['error'] && !$this->arResult['error']) {
            $this->arResult['error'] = 'Данные о заказах не были получены. Обратитесь в службу поддержки, если ошибка повторяется.';
            $this->arResult['list'] = [];
            return;
        } else if (!$this->arResult['data']['total']) {
            $this->arResult['list'] = [];
            if (!$this->arResult['error']) {
                $this->arResult['error'] = 'Нет данных.';
            }
            return;
        }
        // Если ошибок нет, получаем общее количество заказов
        $this->arResult['total'] = $this->arResult['data']['total'];
        $this->arResult['nav']->setRecordCount($this->arResult['total']);

        $data = $this->arResult['data']['parcels'];
        for ($i = 0; $i < count($data); $i++) {

            $arRow = [
                "trackNumber" => $data[$i]['trackNumber'],
                "invoiceNumber" => $this->getLinkForPageOrder($data[$i]),
                "orderNumber" => $data[$i]['orderNumber'],
                "createDate" => self::getDateTimeStrFromStr($data[$i]['createDate']),
                "storeDate" => $data[$i]['storeDate'],
                "return" => $data[$i]['return'] ? 'Да' : 'Нет',
                "declaredValue" => self::getStrCost($data[$i]['declaredValue']),
                "amountPay" => self::getStrCost($data[$i]['amountPay']),
                "deliveryCost" => self::getStrCost($data[$i]['deliveryCost']),
                "paymentType" => self::getPaymentType($data[$i]['paymentType']),
                "actualWeight" => $data[$i]['actualWeight'],
                "deliveryType" => self::getDeliveryType($data[$i]['deliveryType']),
                "issueType" => self::getIssueType($data[$i]['issueType']),
                "barcodes" => count($data[$i]['barcodes']),
            ];

            if ($this->checkFindFilter($arRow)) {
                $this->arResult['list'][] = [
                    'data' => $arRow,
                ];
            }
            
        }

        // Пока сортировку не делаем
        //$this->sortData(); // TODO переделаю логику - формируем массив элементов грида, фильтруем их (если надо), затем сортируем, а полученный 
        // результат присваиваем $this->arResult['list'][] = ['data' => $row[i]] перебором в цикле
        
    }

    public function getLinkForPageOrder($data)
    {
        $trackNumber = $data['trackNumber'];
        return '<a href=' . "/company/order/{$trackNumber}+{$this->arResult['shopCodes']}" . '>' . $data['invoiceNumber'] . '</a>';
    }

    // Пока не используется.
    private function sortData()
    {
        if ($this->arResult['sort']['sort']) {
            $sortField = array_key_first($this->arResult['sort']['sort']);
            $sortType = $this->arResult['sort']['sort'][$sortField];
           
            if ($sortType === 'asc') {//desc
                uasort($this->arResult['list'], function ($a, $b) {
                    return ($a[$sortField] < $b[$sortField]) ? -1 : 1;
                });
            } else {
                uasort($this->arResult['list'], function ($a, $b) {
                    return ($a[$sortField] > $b[$sortField]) ? -1 : 1;
                });
            }
        }
    }

    /**
     * Check row grid for filter Find
     * @param array $arRow - array with data for row grid
     * @return boolean returns true if there is a match, otherwise false
     */
    private function checkFindFilter($arRow) 
    {
        if (!$this->arResult['filterData']['find']) {
            return true;
        }
        foreach ($this->arResult['filterData']['find'] as $key => $value) {
            if ($arRow[$key] == $value) return true;
        }

        return false;
    }
    
    public static function getDateTimeStrFromStr(string $stringDate)
    {
        $dateObj = date_create($stringDate);
        return date_format($dateObj, "d.m.Y H:i:s");
    }

    private function getDateStrFromStr(string $stringDateTime)
    {
        $dateObj = date_create($stringDateTime);
        return date_format($dateObj, "Y-m-d");
    }

    public static function getStrCost(int | null $intCost)
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

    public static function getPaymentType(int $numberPaymentType)
    {
        if (array_key_exists($numberPaymentType, self::$arPaymentType)) {
            return self::$arPaymentType[$numberPaymentType];
        }
        return $numberPaymentType;
    }

    public static function getDeliveryType(int $numberDeliveryType)
    {
        if (array_key_exists($numberDeliveryType, self::$arDeliveryType)) {
            return self::$arDeliveryType[$numberDeliveryType];
        }
        return $numberDeliveryType;
    }

    public static function getIssueType(array $arData)
    {
        if (array_key_exists($arData[0], self::$arIssueType)) {
            return self::$arIssueType[$arData[0]];
        }
        return $arData;
    }

}
