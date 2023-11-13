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

    
    
}
