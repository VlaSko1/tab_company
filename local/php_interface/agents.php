<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Функция агент (подключается как агент в битриксе) - создаёт уведомление для администратора, если количество необработанных заявок 
 * (заявок без привязанных сделок) равно или больше 10
 * @return string возвращает имя выполняемой функции агента, для следующего ее выполнения в битриксе
 */
function alertCountAppWithoutDeal()
{
	$arListEl = getListIblock(SYMBOL_CODE);

	$arFilterNotDeal = array_filter($arListEl, function($k) {
		return $k[NAME_DEAL_PROP] === null;
	});

	$countElemNotDeal = count($arFilterNotDeal);
	if ($countElemNotDeal >= 10 ) {
		CAdminNotify::Add(Array(
			"MESSAGE" => "Обратите внимание!<br>Количество необработанных заявок - {$countElemNotDeal}",
		));
	} 
	
	return "alertCountAppWithoutDeal();";
}	
