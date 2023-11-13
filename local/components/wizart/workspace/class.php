<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER['DOCUMENT_ROOT'] . '/workspace/logger.php');
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\ActionFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Bitrix\Main\Diag\Debug as Debug;

\Bitrix\Main\Loader::includeModule('crm');
CModule::IncludeModule('pull');
RegisterModuleDependences("pull", "OnGetDependentModule", "text", "AjaxComponent" );


class AjaxComponent extends CBitrixComponent implements Controllerable
{
    protected $errorCollection;
    
    public static $simbolCode = 'applications';

    public static $gridName = 'list_form';

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
     * Нужен для работы Push&Pull
     */
    public static function OnGetDependentModule()
    {
        return Array(
            'MODULE_ID' => "test",
            'USE' => Array("PUBLIC_SECTION")
    	);
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
     * Getting array of city in iblock 
     * @param integer $idIblock - id iblock
     * @return array - rray of city in iblock 
     */
    public function getItemsCity($idIblock) 
    {
        $obCityAdd = new AddCity();
        $resCity = CIBlockElement::GetList(
            array("SORT"=>"ASC"),
            ['IBLOCK_ID' => $idIblock],
            false,
            false,
            ['PROPERTY_CITY'],
        );
        
        while($row = $resCity->GetNext()) {
            $obCityAdd -> setEntities($row['PROPERTY_CITY_VALUE']);
        }
        Debug::writeToFile($obCityAdd -> getEntities(),'', '/local/logs/bug.log');
        return $obCityAdd -> getEntities();
    }

    /**
     * Binds a transaction to an element
	 * @param string $idBlockEl
     * @param string $idDeal
	 * @return array
	 */
    public static function bindDealAction($idBlockEl, $idDeal) {
        $logger = new Logger();
        $nameMethod = __FUNCTION__ . PHP_EOL;
        $arParams = func_get_args();
        $logger -> setLog($nameMethod, $arParams);
        try {
            $el = new CIBlockElement;

            $element=\Bitrix\Iblock\Elements\ElementGridblockTable::getByPrimary($idBlockEl, array(

                'select' => array('ID', 'DEAL_BINDING'),
                'cache' => ['ttl' => 3600],
            
            ))->fetchObject();
            
            if($element -> getDealBinding()) 
            {
                $arReturn['status'] = 'error';
                $arReturn['error_message'] = 'Ошибка! К элемент c id - ' . $idBlockEl . ' сделка уже привязана!' ;
                return $arReturn;
            }
            $arElemData = [];
            $arElemData['DEAL_BINDING'] = $idDeal;

            $el -> SetPropertyValuesEx(
                $element->getId(),
                null,
                $arElemData,
            ); 

            $arReturn['status'] = 'ok';
            $arReturn['message'] = 'Сделка связана с элементом инфоблока.';

            return $arReturn;

        } catch (Exeption $e) {
            $arReturn['status'] = 'error';
            $arReturn['error_message'] = 'Ошибка при обновлении элемента инфоблока - ' . $e -> $message;
            return $arReturn;
        }
        
    }

    /**
     * Deletes elements according to the received id
     * @param array $json Array with id of elements to delete 
     * @return array Returns the result of the deletion as an array
     */
    public function ajaxRequestDelAction($json)
    {
        $logger = new Logger();
        $nameMethod = __FUNCTION__ . PHP_EOL;
        $arParams = func_get_args();
        $logger -> setLog($nameMethod, $arParams);
        try {
            global $DB;
            global $APPLICATION;
            if (!CModule::IncludeModule('iblock')) {
                ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));
            }else{
                $arResult["GRID_ID"] = $arResult['list_id'];
                $strError = "";
                $errorID = $arResult["GRID_ID"] . "_error";
                $obElement = new CIBlockElement;
                foreach($json as $id) {
                    $arElement["ID"] = ($id);
                    $DB->StartTransaction();
                    $APPLICATION->ResetException();
                    if (!$obElement->Delete($arElement["ID"])) {
                        $DB->Rollback();
                        if ($ex = $APPLICATION->GetException())
                            $strError = GetMessage("CC_BLL_DELETE_ERROR") . " " . $ex->GetString();
                        else
                            $strError = GetMessage("CC_BLL_DELETE_ERROR") . " " . GetMessage("CC_BLL_UNKNOWN_ERROR");
                    } else {
                        $DB->Commit();
                    }
                }
            }
        } catch (Exception $e) {
            $this->errorCollection[] = new Error($e->getMessage());
            return [
                "result" => "Произошла ошибка",
            ];
        }
    }

    /**
     * Экспортирует грид в виде таблицы Excel на сервер и возвращает ссылку для скачивания файла
     * @param array принимает текущий фильтр грида в виде массива
     * @return void просто завершает выполнение метода 
     */
    public static function exportExcelAction($filter){
        //Debug::writeToFile($idBlock,'', '/local/logs/excel.log');
        
        session_write_close();

        if ($filter['NAME']) $filter['NAME'] = '%'.$filter['NAME'].'%';
        if ($filter['FIRST_NAME']) $filter['PROPERTY_FIRST_NAME'] = '%'.$filter['FIRST_NAME'].'%';
        if ($filter['LAST_NAME']) $filter['PROPERTY_LAST_NAME'] = '%'.$filter['LAST_NAME'].'%';
        if ($filter['FIND']) $filter[] = ['SEARCHABLE_CONTENT' => '%'.$filter['FIND'].'%']; // в поле SEARCHABLE_CONTENT обычно попадают данные всех текстовых полей грида (но это не точно)
        if ($filter['EMAIL']) $filter['PROPERTY_EMAIL'] = '%'.$filter['EMAIL'].'%';
        if ($filter['CITY_label'][0]) $filter['PROPERTY_CITY'] = $filter['CITY_label'][0];
        if ($filter['PHONE']) $filter['PROPERTY_PHONE'] = '%'.$filter['PHONE'].'%';
        if ($filter['BIRTH_DATE']) $filter['PROPERTY_BIRTH_DATE'] = "%". explode(' ', $filter['BIRTH_DATE'])[0] ."%";

        $arSelect = Array("ID", "NAME", 'PROPERTY_FIRST_NAME', 'PROPERTY_LAST_NAME', 'PROPERTY_EMAIL', 'PROPERTY_BIRTH_DATE', 'PROPERTY_PHONE', 'PROPERTY_CITY', 'PROPERTY_DEAL_BINDING', 'PROPERTY_FILE');
        $filter['IBLOCK_ID'] = CIBlock::GetList(array(), array("CODE" => self::$simbolCode), false, false, array("IBLOCK_ID"))->GetNext()['ID'];

        switch ($filter['BIRTH_DATE_datesel']) {
            case 'RANGE':
                $filter[] = [
                    'LOGIC' => 'AND',
                    ">=PROPERTY_BIRTH_DATE" => substr($filter["BIRTH_DATE_from"], 0, 10),
                    "<=PROPERTY_BIRTH_DATE" => substr($filter["BIRTH_DATE_to"], 0, 10),
                ];
                break;
            case 'EXACT':
                $filter["PROPERTY_BIRTH_DATE"] = substr($filter["BIRTH_DATE_from"], 0, 10);
                break;
        }
        $grid_options = new GridOptions(self::$gridName);

        $sort = $grid_options->GetSorting(['sort' => ['ID' => 'ASC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        $rows = CIBlockElement::GetList($sort['sort'], $filter, false, '', $arSelect);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Имя');
        $sheet->setCellValue('C1', 'Фамилия');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Дата рождения');
        $sheet->setCellValue('F1', 'Номер телефона');
        $sheet->setCellValue('G1', 'Город');
        $sheet->setCellValue('H1', 'Файл');
        $sheet->setCellValue('I1', 'Сделка');
        $i = 2;

        while($row = $rows->GetNext()){
            $sheet->setCellValue('A'.$i, $row['ID']);
            $sheet->setCellValue('B'.$i, $row['PROPERTY_FIRST_NAME_VALUE']);
            $sheet->setCellValue('C'.$i, $row['PROPERTY_LAST_NAME_VALUE']);
            $sheet->setCellValue('D'.$i, $row['PROPERTY_EMAIL_VALUE']);
            $sheet->setCellValue('E'.$i, $row['PROPERTY_BIRTH_DATE_VALUE']);
            $sheet->setCellValue('F'.$i, $row['PROPERTY_PHONE_VALUE']);
            $sheet->setCellValue('G'.$i, $row['PROPERTY_CITY_VALUE']);
            $sheet->setCellValue('H'.$i, $row['PROPERTY_FILE_VALUE']);
            $sheet->setCellValue('I'.$i, $row['PROPERTY_DEAL_BINDING_VALUE']);
            $i++;
        }
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(16);
        $writer = new Xlsx($spreadsheet);
        $date = date('Y-m-d-H-i-s');
        
        $filepath = $_SERVER['DOCUMENT_ROOT'].'/upload/test/'.$date.'.xlsx';
        $writer->save($filepath);
        $extLinkUrl = '';
        $idFile = '';
        if (\Bitrix\Main\Loader::includeModule('disk'))
        {
            $storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId(1);
            if ($storage)
            {
                $folder = $storage->getRootObject(); 
                $folder = $folder->getChild( 
                    array( 
                        '=NAME' => 'tempFolder',  
                        'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER 
                    ) 
                );
                if (!$folder) 
                {
                    $folder = $storage->addFolder(
                        array(
                            'NAME' => 'tempFolder',
                            'CREATE_BY' => 1
                        )
                    );
                }
                if ($folder)
                {
                    $fileArray = \CFile::MakeFileArray($filepath);
                    $file = $folder->uploadFile($fileArray, array(
                        'CREATED_BY' => 1
                    ));
                    if ($file) 
                    {
                        $urlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();
                        $extLinkUrl = $urlManager->getPathFileDetail($file);
                        $idFile = $file->getFileId();
                    }
                    
                }
            }
        }
        global $USER;
        CPullStack::AddByUser(
            $USER->GetID(),
            Array(
                'module_id' => 'test',
                'command' => 'check',
                'params' => Array('path' => $extLinkUrl, 'idFile' => $idFile, 'filePath' => $filepath),
            )
        );
        return;

    }
    
    /**
     * Удаляет "временный" файл из битры по его айдишнику и из файловой структуры по его пути. 
     * @param string $idFile id файла для удаления из битры
     * @param string $filePath путь к файлу в файловой структуре для удаления файла
     * @return array массив с результатом выполнения удаления файла 
     */
    public static function delFileByIdAction($idFile, $filePath)
    {
        Debug::writeToFile(gettype($idFile),'', '/local/logs/excel.log');
        //\CFile::Delete($idFile); Первый способ удаления файла из интерфейса битры.

        // Второй способ удаления файла из интерфейса битры:
        $deletedBy = 1;

        $diskFile = \Bitrix\Disk\File::load([
            '=FILE_ID' => $idFile
        ]);

        if ( $diskFile instanceof \Bitrix\Disk\BaseObject )
        {
            $result = $diskFile->delete($deletedBy);

            if ( $result )
            {
                unlink($filePath); // удаляет файл Excel, который был создан пакетом PhpSpreadsheet в строке 256
                return ['result' => 'Ok!'];
            }
            else
            {
                return ['result' => "Not delete file whith id {$idFile}"];
            }
        }
        else
        {
            return ['result' => "Файл диска с id {$idFile} не найден"];
        }

        // Третий спопоб удаления файла из интерфейса битры.
        /*$storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId(1); 
        if ($storage) 
        { 
            //$folder = $storage->getRootObject(); 
            $folder = $folder->getChild( 
                array( 
                    '=NAME' => 'tempFolder',  
                    'TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER 
                ) 
            );
            $file = $folder->getChild( 
                array( 
                    '=FILE_ID' => $idFile,  
                ) 
            ); 
            if ($file) 
            { 
                $file->delete($deletedBy);
                
            } 
        }*/
    }

}
