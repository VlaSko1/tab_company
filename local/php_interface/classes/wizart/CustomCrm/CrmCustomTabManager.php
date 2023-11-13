<?php

/**
 * file path: local/php_interface/classes/wizart/CustomCrm/CrmCustomTabManager.php
 */
  
namespace wizart\CustomCrm;

use Bitrix\Main\Loader;

Loader::includeModule('crm');

/**
 * Менеджер для работы со вкладками сущностей CRM
 */
class CrmCustomTabManager
{
    /**
     * CRM права текущего пользователя
     * @var \CCrmPerms
     */
    protected \CCrmPerms $userPermissions;

    public function __construct()
    {
        $this->userPermissions = \CCrmPerms::GetCurrentUserPermissions();
    }

    /**
     * Получение актуальных вкладок
     * @param int $elementId
     * @param int $entityTypeID
     * @param array $tabs
     * @return array
     */
    public function getActualEntityTab(int $elementId, int $entityTypeID, array $tabs = []): array
    {
        switch ($entityTypeID) {
            case \CCrmOwnerType::Deal:
                // @TODO Реализовать получение вкладок для сделок
                break;
            case \CCrmOwnerType::Company:
                $tabs = $this->getActualCompanyTabs($tabs, $elementId);
                break;
            case \CCrmOwnerType::Contact:
                // @TODO Реализовать получение вкладок для контактов
                break;
        }

        return $tabs;
    }

    /**
     * Получение актуальных вкладок элемента сущности "Компания"
     * @param array $tabs
     * @param int $elementId
     * @return array
     */
    private function getActualCompanyTabs(array $tabs, int $elementId): array
    {
        $canUpdateCompany = \CCrmCompany::CheckUpdatePermission($elementId, $this->userPermissions);

        if ($canUpdateCompany) {
            $tabs[] = [
                'id' => 'component_users',
                'name' => 'Пользователи',
                'enabled' => !empty($elementId),
                'loader' => [
                    'serviceUrl' => '/local/components/wizart/order_grid/lazyload.ajax.php?&site=' . \SITE_ID . '&' . \bitrix_sessid_get(),
                    'componentData' => [
                        'template' => '',
                        'params' => [
                            // Параметры вызываемого компонента ($arParams)
                        ]
                    ]
                ]
            ];
        }

        return $tabs;
    }
}