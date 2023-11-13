<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER['DOCUMENT_ROOT'] . '/workspace/logger.php');
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;


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

  /**
	 * @param string $ID_ELEMENT
	 * @return array
	 */
	public static function showSidepanelAction($ID_ELEMENT)
	{
        $logger = new Logger();
        $nameMethod = __FUNCTION__ . PHP_EOL;
        $arParams = func_get_args();
        $logger -> setLog($nameMethod, $arParams);
        try {
            $element=\Bitrix\Iblock\Elements\ElementGridblockTable::getByPrimary($ID_ELEMENT, array(

                'select'=>array('ID', 'NAME', 'FIRST_NAME', 'LAST_NAME', 'EMAIL', 'BIRTH_DATE', 'PHONE', 'CITY', 'FILE', 'DEAL_BINDING'),
                'cache' => ['ttl' => 3600],
            
            ))->fetchObject();
            $arElemData = [];
    
            
            $arElemData['NAME'] = $element -> getName();
            $arElemData['FIRST_NAME'] = $element -> getFirstName() -> getValue();
            $arElemData['LAST_NAME'] = $element -> getLastName() -> getValue();
            $arElemData['EMAIL'] = $element -> getEmail() -> getValue();
            $arElemData['BIRTH_DATE'] = $element -> getBirthDate() -> getValue();
            $arElemData['PHONE'] = $element -> getPhone() -> getValue();
            $arElemData['CITY'] = $element -> getCity() -> getValue();
            
            $fileId = $element -> getFile() -> getValue();
            $arElemData['FILE'] = CFile::ShowImage($fileId, 100, 100, "border=0", "", true);
    
            if ($element -> getDealBinding()) {
                $arDealRes = CCrmDeal::GetByID($element -> getDealBinding()->getValue());
                $htmlLinkCodeDeal =     "<a href='/crm/deal/details/{$arDealRes['ID']}/'
                                            target='_blank' bx-tooltip-user-id='DEAL_{$arDealRes['ID']}' 
                                            bx-tooltip-loader='/bitrix/components/bitrix/crm.deal.show/card.ajax.php' 
                                            bx-tooltip-classname='crm_balloon_no_photo'>
                                            {$arDealRes['TITLE']}								
                                        </a>";
    
                $htmlCodeDeal = "   <div class='form__block'>
                                        <p class='block__text'>
                                            Привязка к сделке
                                        </p>
                                        <div class='ui-ctl ui-ctl-textbox'>
                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$htmlLinkCodeDeal}</div>
                                        </div>
                                    </div>";
            }
            
    
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
                                            <section class='wrapper__form'>
                                                <h1 class='form__title'>Карточка элемента</h1>
                                                <form class='form' action='#' name='form' id='form' method='POST'>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Имя
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox '>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['FIRST_NAME']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Фамилия
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox '>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['LAST_NAME']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Email
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox '>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['EMAIL']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Дата рождения
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox '>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['BIRTH_DATE']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Номер телефона
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox'>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['PHONE']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Город
                                                        </p>
                                                        <div class='ui-ctl ui-ctl-textbox'>
                                                            <div class='ui-ctl-element ui-ctl-element_mod'>{$arElemData['CITY']}</div>
                                                        </div>
                                                    </div>
                                                    <div class='form__block'>
                                                        <p class='block__text'>
                                                            Портфолио
                                                        </p>
                                                        
                                                        <div class='ui-ctl ui-ctl-file-link ui-ctl-file-link_block ui-ctl-file-link_block-view' >
                                                            {$arElemData['FILE']}
                                                        </div>
                                                    </div>
                                                    {$htmlCodeDeal}
                                                </form>
                                            </section>
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
