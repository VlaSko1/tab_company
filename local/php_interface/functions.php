<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();


CModule::IncludeModule("workflow", "bizproc", "iblock", 'crm');

/**
 * Получает значение контента (из события "OnEndBufferContent") и преобразует его с помощью функции "changeLink"
 * @param string &$content - ссылка на контент сайта
 * @return void просто изменяет контент по ссылке
 */
function ChangeExternalLink(&$content)
{
    $content = changeLink($content);
}


/**
 * Преобразует контент сайта - добавляет всем внешним ссылкам не содержащим IP адрес (или доменное имя) локального сервера атрибут rel со значением noreferrer
 * @param string $buffer - строка контента для изменения
 * @return string $newBuffer - новая строка контента с изменениями согласно описания функции
 */
function changeLink($buffer)
{
    $nameServer = $_SERVER['SERVER_NAME'];
    $reg = "/<a\s+href=('||\")https?:\/\/(?!{$nameServer})[\w\/.\"']+/";

    $newBuffer = $buffer;

    while (true) {
        preg_match($reg, $newBuffer, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches) || count($matches) === 0)
            break;

        $string = '';
        for ($i = 0; $i < count($matches); $i++) {
            if ($i == 0) {
                $string = $matches[$i][0];
            }
        }

        $arString = str_split($string, 1);

        $newString = '';
        for ($i = 0; $i < count($arString); $i++) {
            if ($i === 2) {
                $newString .= " rel='noreferrer' ";
            }
            $newString .= $arString[$i];
        }

        $newBuffer = str_replace($string, $newString, $newBuffer);

    }

    return $newBuffer;

}

/**
 * Возвращает ассоциативный массив элементов информационного блока с данными ID, NAME и свойства привязки к сделке (DEAL_BINDING)
 * @param string $code - символьный код инфоблока, данные по элементам которого нужно получить
 * @return array $data - ассоциативный массив элементов информационного блока с данными ID, NAME и свойства привязки к сделке (DEAL_BINDING)
 */
function getListIblock($code)
{
    $res = CIBlockElement::GetList(
        array(),
        array('IBLOCK_CODE' => $code, 'ACTIVE' => 'Y'),
        false,
        false,
    ['ID', 'NAME', 'PROPERTY_DEAL_BINDING']
    );

    $data = array();
    while ($row = $res->getNext()) {
        $data[] = [
            'ID' => $row['ID'],
            "NAME" => $row['NAME'],
            'DEAL_BINDING' => $row['PROPERTY_DEAL_BINDING_VALUE']
        ];
    }

    return $data;

}

/**
 * Проверяет наличие в ассоциативном массиве $arData элемента со свойством DEAL_BINDING равным значению $value, в случае нахождения возвращает ID элемента, иначе - false
 * @param string $value - строковое значение идентификатора сделки
 * @param array $arData - ассоциативный массив с элементами инфоблока заявок, у которых проверяется привязка к сделке с id равным $value
 * @return (string || boolean) - возвращает id элемента ифоблока заявок, которому привязана сделка с id равным $value, либо возвращает false
 */
function existValInArray($value, $arData)
{
    $result = false;
    foreach ($arData as $arItem) {
        if ($arItem[NAME_DEAL_PROP] === $value) {
            $result = $arItem['ID'];
            break;
        }
    }
    return $result;
}

/**
 * Из входящего ассоциативного массива элементов заявок формирует строку вида "25_13_45" куда входят id элементов 
 * заявок, к которым не привязаны сделки
 * @param array $arApp - ассоциативный массив с элементами инфоблока заявок
 * @return string строка вида "25_13_45" куда входят id элементов заявок, к которым не привязаны сделки, либо ''
 */
function getNotDealApp($arApp)
{
    $strNotDeal = '';
    $arFilter = array_filter($arApp, function ($k) {
        return $k[NAME_DEAL_PROP] === null;
    });

    foreach ($arFilter as $item) {
        if ($strNotDeal !== '')
            $strNotDeal .= '_';
        $strNotDeal .= $item["ID"];
    }
    return $strNotDeal;
}

/**
 * Преобразует входящее значение в рублях в доллары по курсу, который зашит в битриксе
 * @param float $rub - сумма в рублях
 * @return string - возвращает преобразованную сумму из рублей в доллары с добавлением знака '$' в виде строки
 */
function getAmountDolFromRub(float $rub)
{
    return round(CCurrencyRates::ConvertCurrency($rub, "RUB", "USD"), 2) . ' $';
}

/**
 * Из входящих параметров id сделки и имени ответственного формирует массив со звонками, относящимися к данной сделке (либо пустой массив)
 * @param string $idDeal - id сделки для которой ищем звонки в таблице Дел
 * @param string $fullName - полное имя ответственного за сделку
 * @return array - массив с данными звонков относящимися к сделке с id=$idDeal
 */
function getArDataForTableField($idDeal, $fullName)
{
    $res = CCrmActivity::GetList(array('START_TIME' => 'ASC'), ['OWNER_TYPE_ID' => 2, 'TYPE_ID' => 2, 'OWNER_ID' => $idDeal], false, false, ['START_TIME', 'DESCRIPTION'], array());
    $arActiveList = [];
    while ($row = $res->getNext()) {
        $arActiveList[] = [
            'DATA' => $row['START_TIME'],
            'NAME' => $fullName,
            'DESCRIPTION' => $row['DESCRIPTION'],
        ];
    }
    return $arActiveList;
}

/**
 * Функция запускается перед созданием документа сделки и добавляет дополнительные поля и значения к этим полям для отправки в генерируемый документ
 * @param object $event - объект события создания документа
 * @return void - ничего не возвращает, но добавляет в генерируемый документа новые поля и значения к ним 
 */
function onBeforeProcessDocument($event)
{
    $document = $event->getParameter('document');
    $dataProvaider = $document->getProvider();
    $ownerId = $dataProvaider->getSource();
    $ownerType = $dataProvaider->getCrmOwnerType();
    if ($ownerType == 2) {
        // получаем данные сделки
        $arDealRes = CCrmDeal::GetByID($ownerId);

        $sumDealInDollar = getAmountDolFromRub($arDealRes['OPPORTUNITY']);

        // добавляем новое поле "сумма сделки в долларах" и его значение
        $document->setValues([
            'SumDealInDollar' => $sumDealInDollar,
        ]);

        // подготовка данных для формирования таблицы звонков
        $fullName = $arDealRes['ASSIGNED_BY_LAST_NAME'] . ' ' . $arDealRes['ASSIGNED_BY_NAME'] . ' ' . $arDealRes['ASSIGNED_BY_SECOND_NAME'];

        $arDataForTableField = getArDataForTableField($arDealRes['ID'], $fullName);

        if ($arDataForTableField) {
            // Если массив с данными звонков не пустой - формируем данные для таблицы звонков
            $obj_call_data = $arDataForTableField;

            $objectSettings = [
                'ITEM_NAME' => 'CALL',
                'ITEM_PROVIDER' => \Bitrix\DocumentGenerator\DataProvider\HashDataProvider::class ,
            ];

            $document->setValues([
                'TABLE' => new \Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider(
                $obj_call_data, $objectSettings
            ),
                'TableCallData' => 'TABLE.CALL.DATA',
                'TableCallName' => 'TABLE.CALL.NAME',
                'TableCallDescription' => 'TABLE.CALL.DESCRIPTION',
                'TableIndex' => 'TABLE.INDEX'
            ]);

            $document->setFields([
                'TABLE' => [
                    'PROVIDER' => 'Bitrix\\DocumentGenerator\\DataProvider\\ArrayDataProvider',
                    'OPTIONS' => $objectSettings,
                    'VALUE' => $obj_call_data,
                ],
            ]);
        }
    }

}

/**
 * Функция вызывается событием изменения элемента инфоблока методом Update, 
 * проверяет принадлежность к нужному инфоблоку, проверяет отсутствие привязанной ранее заявки и
 * наличие заявки в передаваемых данных, если все условия удовлетворяются - запускает заранее созданные 
 * бизнес процесс (его id указан в константах) информирующий администратора (так настроен сам бизнес процесс) о привязки заявки 
 * к сделке
 * @param array $arFields - Массив полей изменяемого элемента информационного блока.
 * @return void - ничего не возвращает, реализует логику согласно описанию функции 
 */
function startBusinessProcessIfBindingDeal(&$arFields)
{
    if ($arFields["IBLOCK_ID"] !== (int)BLOCK_ID) return;
    // Получаем данные изменяемого элемента инфоблока до его изменения (id инфоблока и его элемента а также значения поля сделки)
    $arElemDataBeforeUpdate = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        ['IBLOCK_ID' => $arFields["IBLOCK_ID"], 'ID' => $arFields['ID']],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'PROPERTY_DEAL_BINDING'],
    )->fetch();

    if (!$arElemDataBeforeUpdate["PROPERTY_DEAL_BINDING_VALUE_ID"]) {   // Проверяем присутствует ли в изначальных данных сделка, если есть - ничего не выполняем
        foreach ($arFields["PROPERTY_VALUES"] as $key => $value) {       
            if (!$value['n0']) continue;                                // если полученное свойство не является сделкой - пропускаем итеррацию
            if (!$value['n0']["VALUE"]) break;                       // если поле сделки пустое - выходим из цикла

            // Запускаем выполнение бизнес процесса уведомляющего администратора о привязке сделки к заявке
            $arErrorsTmp = array();
            CBPDocument::StartWorkflow(
                WORKFLOW_ID, 
                array("bizproc", "CBPVirtualDocument", $arFields['ID']), 
                array('Parameter1' => $arFields['ID'], 'Parameter2' => $value['n0']["VALUE"]), 
                $arErrorsTmp,
            );
        }
        // Пример работы с входящими значениями (вдруг забуду).
        /*echo('<pre>');
        var_dump($arElemDataBeforeUpdate["PROPERTY_DEAL_BINDING_VALUE_ID"]);
        echo('<br>');
        echo('<br>');
        var_dump($arFields["PROPERTY_VALUES"]);
        die();*/

    }
}

/**
 * Функция вызывается событием изменения элемента инфоблока методом SetPropertyValuesEx, 
 * проверяет принадлежность к нужному инфоблоку, проверяет отсутствие привязанной ранее заявки и
 * наличие заявки в передаваемых данных, если все условия удовлетворяются - запускает заранее созданные 
 * бизнес процесс (его id указан в константах) информирующий администратора (так настроен сам бизнес процесс) о привязки заявки 
 * к сделке
 * @param int $ELEMENT_ID - Идентификатор элемента инфоблока.
 * @param int IBLOCK_ID - Идентификатор инфоблока.
 * @param array $PROPERTY_VALUES - Массив значений свойств элемента инфоблока.
 * @param array $propertyList - Массив, описывающий Список свойств.
 * @param array $arDBProps - Текущие значения свойств элемента. (При вардампе - пустой массив, когда сделка присоединяется)
 * @return void - ничего не возвращает, реализует логику согласно описанию функции 
 */
function startBusinessSetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $propertyList, $arDBProps) {
    if ($IBLOCK_ID !== BLOCK_ID) return; 
    $arElemDataBeforeUpdate = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        ['IBLOCK_ID' => $IBLOCK_ID, 'ID' => $ELEMENT_ID],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'PROPERTY_DEAL_BINDING'],
    )->fetch();
  
    if ($arElemDataBeforeUpdate["PROPERTY_DEAL_BINDING_VALUE_ID"]) return; // Проверяем присутствует ли в изменяемых данных сделка, если есть - ничего не выполняем
    if ($PROPERTY_VALUES[NAME_DEAL_PROP]) {   
        
        // Запускаем выполнение бизнес процесса уведомляющего администратора о привязке сделки к заявке
        $arErrorsTmp = array();
        CBPDocument::StartWorkflow(
            WORKFLOW_ID, 
            array("bizproc", "CBPVirtualDocument", $ELEMENT_ID), 
            array('Parameter1' => $ELEMENT_ID, 'Parameter2' => $PROPERTY_VALUES[NAME_DEAL_PROP]), 
            $arErrorsTmp,
        );
    }
}
  
//TODO допиши PHPDoc
function stopChangeStatusDeal(&$event)
{
    // получим массив групп текущего пользователя
    global $USER;
    $arGroups = $USER->GetUserGroupArray();

    if (!in_array('1', $arGroups, true)) {
        if ($event["STAGE_ID"] === 'EXECUTING' || $event["STAGE_ID"] === 'FINAL_INVOICE' || $event["STAGE_ID"] === 'WON') {
            global $APPLICATION;
            $curDeal = \CCrmDeal::GetList(['DATE_CREATE' => 'DESC'],["ID"=>$arFields['ID'], 'CHECK_PERMISSIONS' => 'N'],[],false)->fetch();
            $event['STAGE_ID'] = $curDeal['STAGE_ID'];
            
            $APPLICATION->throwException(
                'Стадия сделки не может быть перемещена на "в работе" и на стадии после неё, если пользователь не является администратором'
            );
            return false;
        }
        
    }
    
}

// Регистрация классов из папки classes
spl_autoload_register(function($sClassName)
{
	$sClassFile = __DIR__.'/classes';

	if ( file_exists($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php') )
	{
		require_once($sClassFile.'/'.str_replace('\\', '/', $sClassName).'.php');
      	return;
	}

	$arClass = explode('\\', strtolower($sClassName));
	foreach($arClass as $sPath )
	{
	    $sClassFile .= '/'.ucfirst($sPath);
	}
  
	$sClassFile .= '.php';
	if (file_exists($sClassFile))
	{
		require_once($sClassFile);
	}
});

/*function onEntityDetailsTabs($entityID, $entityTypeID, $eventName, $eventTab) 
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
                            //'serviceUrl' => '/local/templates/page/store_orders.php'),
                            'serviceUrl' => '/local/components/wizart/order_grid/lazylode.ajax.php?site=' . \SITE_ID . '&' . \bitrix_sessid_get()),
                            //'serviceUrl' => '/local/templates/page/store_orders.php')
                            //'serviceUrl' => '/local/php_interface/example/damage.php?id=list_policy&ideventdeal='.$ideventdeal),
                        );
                
                    
        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}*/