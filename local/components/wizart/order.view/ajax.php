<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use \Bitrix\Main;
use \Bitrix\Crm;


require_once(__DIR__ . '/../orders_grid/class.php');

class AjaxController extends Controller implements Errorable
{
    protected $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        return $arParams;
    }

    public function executeComponent()
    {
        // Метод не будет вызван при ajax запросе
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

    public function getDataOrder($trackNumber, $shopCodes)
    {
        $fullLink = AjaxComponent::$httpLink . AjaxComponent::$rest . '?token=' . 'token';

        $params = [
            'searchParameters.numberParcels' => $trackNumber,
            'page.limit' => 20,
            'page.offset' => 0,
            'searchParameters.shopCodes' => $shopCodes,
        ];

        $ch = curl_init();
        $token = AjaxComponent::$token;
        curl_setopt($ch, CURLOPT_URL, $fullLink . '&' . http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
      
        return $data;
    }

    public function increase(&$number)
    {
        return $number += 1;
    }

    public function prepareRow($arData, $nextNumber)
    {
        $resultString = '';
        $numberBarcodes = 1;
        foreach ($arData as $value) {
            $tempSting = "
                <tr>
                    <td>{$nextNumber}</td>
                    <td rowspan='5'>{$numberBarcodes}</td>
                    <td>Штрих-код</td>
                    <td>{$value['barcode']}</td>
                </tr>
                <tr>
                    <td>{$this->increase($nextNumber)}</td>
                    <td>Вес, кг.</td>
                    <td>{$value['weight']}</td>
                </tr>
                <tr>
                    <td>{$this->increase($nextNumber)}</td>
                    <td>Длина, см.</td>
                    <td>{$value['length']}</td>
                </tr>
                <tr>
                    <td>{$this->increase($nextNumber)}</td>
                    <td>Ширина, см.</td>
                    <td>{$value['width']}</td>
                </tr>
                <tr>
                    <td>{$this->increase($nextNumber)}</td>
                    <td>Высота, см.</td>
                    <td>{$value['height']}</td>
                </tr>";

            $resultString .= $tempSting;
            $nextNumber++;
            $numberBarcodes++;
        }
        return $resultString;
    }

  /**
	 * @param string $stringData
	 * @return array
	 */
	public function showOrderAction($stringData)
	{
        try {
            [$trackNumber, $shopCodes] = explode('+', $stringData);

            $data = $this->getDataOrder($trackNumber, $shopCodes);

            $dataOrder = $data['parcels'][0];

            $dataInText = [
                "trackNumber" => $dataOrder['trackNumber'],
                "invoiceNumber" => $dataOrder['invoiceNumber'],
                "orderNumber" => $dataOrder['orderNumber'],
                "createDate" => AjaxComponent::getDateTimeStrFromStr($dataOrder['createDate']),
                "storeDate" => $dataOrder['storeDate'],
                "return" => $dataOrder['return'] ? 'Да' : 'Нет',
                "declaredValue" => AjaxComponent::getStrCost($dataOrder['declaredValue']),
                "amountPay" => AjaxComponent::getStrCost($dataOrder['amountPay']),
                "deliveryCost" => AjaxComponent::getStrCost($dataOrder['deliveryCost']),
                "paymentType" => AjaxComponent::getPaymentType($dataOrder['paymentType']),
                "actualWeight" => $dataOrder['actualWeight'],
                "deliveryType" => AjaxComponent::getDeliveryType($dataOrder['deliveryType']),
                "issueType" => AjaxComponent::getIssueType($dataOrder['issueType']),
                "barcodesCount" => count($dataOrder['barcodes']),
                "barcodes" => $dataOrder['barcodes'],
            ];
            
            $stringBarcode = $this->prepareRow($dataInText['barcodes'], 16);

            //var_dump($stringBarcode); die();
    
            return [
                'html' => " <!doctype html>
                            <html>
                            <head>
                                <title><?php $APPLICATION->ShowTitle()?></title>
                                <?php $APPLICATION->ShowHead()?>
                                <style>
                                    .wrapper {
                                        padding: 20px;
                                        
                                    }
                                    .title_data {
                                        text-align: center;
                                        margin-bottom: 5px;
                                    }
                                    .table_data {
                                        border-collapse:collapse;
                                        border: 1px solid black;
                                        width: 100%;
                                        font-size: 1.15em;
                                        
                                    }
                                    td, th {
                                        border: 1px solid black;
                                    }
                                    td {
                                        padding: 0 5px;
                                    }
                                </style>
                                
                            </head>
                            <body>
                                <?php $APPLICATION->ShowPanel()?>
                                    <main>
                                    <div class='wrapper'>
                                    <h1 class='title_data'>Данные по заказу</h1>
                                        <table class='table_data'>
                                            <tr>
                                                <th>
                                                    № п/п
                                                </th>
                                                <th colspan='2'>
                                                    Название параметра
                                                </th>
                                                <th>
                                                    Данные по заказу
                                                </th>
                                            </tr>
                                            <tr>
                                                <td>1</td>
                                                <td>Трек-номер</td>
                                                <td></td>
                                                <td>{$dataInText['trackNumber']}</td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Номер ЭН</td>
                                                <td></td>
                                                <td>{$dataInText['invoiceNumber']}</td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>Номер заказа ИМ</td>
                                                <td></td>
                                                <td>{$dataInText['orderNumber']}</td>
                                            </tr>
                                             <tr>
                                                <td>4</td>
                                                <td>Дата создания заказа</td>
                                                <td></td>
                                                <td>{$dataInText['createDate']}</td>
                                            </tr>
                                            <tr>
                                                <td>5</td>
                                                <td>Срок хранения</td>
                                                <td></td>
                                                <td>{$dataInText['storeDate']}</td>
                                            </tr>
                                            <tr>
                                                <td>6</td>
                                                <td>Признак возврата</td>
                                                <td></td>
                                                <td>{$dataInText['return']}</td>
                                            </tr>
                                            <tr>
                                                <td>7</td>
                                                <td>Объявленная стоимость, руб.</td>
                                                <td></td>
                                                <td>{$dataInText['declaredValue']}</td>
                                            </tr>
                                            <tr>
                                                <td>8</td>
                                                <td>Сумма к оплате, руб.</td>
                                                <td></td>
                                                <td>{$dataInText['amountPay']}</td>
                                            </tr>
                                            <tr>
                                                <td>9</td>
                                                <td>Стоимость доставки, руб.</td>
                                                <td></td>
                                                <td>{$dataInText['deliveryCost']}</td>
                                            </tr>
                                            <tr>
                                                <td>10</td>
                                                <td>Тип оплаты</td>
                                                <td></td>
                                                <td>{$dataInText['paymentType']}</td>
                                            </tr>
                                            <tr>
                                                <td>11</td>
                                                <td>Фактический вес, кг.</td>
                                                <td></td>
                                                <td>{$dataInText['actualWeight']}</td>
                                            </tr>
                                            <tr>
                                                <td>12</td>
                                                <td>Вид доставки</td>
                                                <td></td>
                                                <td>{$dataInText['deliveryType']}</td>
                                            </tr>
                                            <tr>
                                                <td>13</td>
                                                <td>Тип выдачи</td>
                                                <td></td>
                                                <td>{$dataInText['issueType']}</td>
                                            </tr>
                                            <tr>
                                                <td>14</td>
                                                <td>Количество мест</td>
                                                <td></td>
                                                <td>{$dataInText['barcodesCount']}</td>
                                            </tr>
                                            <tr>
                                                <td>15</td>
                                                <td>Места:</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            {$stringBarcode}
                                        </table>
                                    </div>
                                    </main>
                                </body>
                            </html>"
            ];

        } catch (Exeption $e) {
            $message = $e -> message;
            return [
                'html' => "<h1>Ошибка</h1>"
            ];
        }

        
	}
    
}
