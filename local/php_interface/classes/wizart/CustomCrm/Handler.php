<?php
  
/**
 * file path: local/php_interface/classes/wizart/CustomCrm/Handler.php
 */

namespace wizart\CustomCrm;

use Bitrix\Main\EventResult;
use Bitrix\Main\Event;

/**
 * События, выполняемые в рамках модуля Заявки счёта
 */
class Handler
{
    /**
     * Получение актуальных вкладок элемента CRM
     * @param Event $event
     * @return EventResult
     */
    static function setCustomTabs(Event $event): EventResult
    {
        $entityId = $event->getParameter('entityID');
        $entityTypeID = $event->getParameter('entityTypeID');
        $tabs = $event->getParameter('tabs');

        $crmCustomTabManager = new CrmCustomTabManager();

        $tabs = $crmCustomTabManager->getActualEntityTab($entityId, $entityTypeID, $tabs);

        return new EventResult(EventResult::SUCCESS, [
            'tabs' => $tabs,
        ]);
    }
}