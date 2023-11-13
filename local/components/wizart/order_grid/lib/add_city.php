<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

class AddCity 
{
  protected $arEntities = [];

  public function setEntities($nameCity) 
  {
    array_push($this->arEntities, ['id' => count($this->arEntities) + 1, 'entityId' => 'my-entity', 'title' => $nameCity, 'tabs' => 'my-tab']);
  }

  public function getEntities() 
  {
    return $this->arEntities;
  }
}
