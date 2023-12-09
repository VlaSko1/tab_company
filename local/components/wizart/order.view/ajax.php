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
    
            return [
                'html' => " <!doctype html>
                            <html>
                            <head>
                                <title><?php $APPLICATION->ShowTitle()?></title>
                                <?php $APPLICATION->ShowHead()?>
                            </head>
                            <body>
                                <?php $APPLICATION->ShowPanel()?>
                                    <main>
                                    <div class='wrapper'>
                                    <h1>Данные по заказу</h1>
                                        <table>
                                            <tr>
                                                <th>
                                                    № п/п
                                                </th>
                                                <th>
                                                    Название параметра
                                                </th>
                                                <th>
                                                </th>
                                                <th>
                                                    Данные по заказу
                                                </th>
                                            </tr>
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
