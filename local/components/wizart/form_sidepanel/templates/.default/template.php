<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CJSCore::Init(array('ajax', 'window', "jquery"));

$this->addExternalCss('/local/components/wizart/form/templates/.default' . '/lib/fias-api/src/css/style.css');
$this->addExternalCss('/local/components/wizart/form/templates/.default' . '/lib/Datepicker/dist/css/datepicker.material.css');
$this->addExternalJS('/local/components/wizart/form/templates/.default' . '/lib/fias-api/src/js/core.js');
$this->addExternalJS('/local/components/wizart/form/templates/.default' . '/lib/fias-api/src/js/fias.js');
$this->addExternalJS('/local/components/wizart/form/templates/.default' . '/lib/Datepicker/dist/datepicker.js');

$APPLICATION->SetTitle("Form_sidepanel");


\Bitrix\Main\UI\Extension::load("ui.forms"); 
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.alerts");
?>
<!doctype html>
<html>
    <head>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
        <title><?php $APPLICATION->ShowTitle()?></title>
        <?php $APPLICATION->ShowHead()?>
    </head>
    <body>
        <?php $APPLICATION->ShowPanel()?>
        <main>
            <div class="wrapper">
                <section class="wrapper__form">
                    <h1 class="form__title">Форма обратной связи</h1>
                    <form class="form" action="#" name="form" id="form" method="POST">
                        <div class="form__block">
                            <p class="block__text">
                                Имя
                            </p>
                            <div class="ui-ctl ui-ctl-textbox ">
                                <input type="text" name="name" id="name" class="ui-ctl-element ui-ctl-element_mod">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Фамилия
                            </p>
                            <div class="ui-ctl ui-ctl-textbox ">
                                <input type="text" name="surname" id="surname" class="ui-ctl-element ui-ctl-element_mod">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Email
                            </p>
                            <div class="ui-ctl ui-ctl-textbox ">
                                <input type="email" name="email" id="email" class="ui-ctl-element ui-ctl-element_mod">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Дата рождения
                            </p>
                            <div class="ui-ctl ui-ctl-textbox ">
                                <input type="date" name="birthday" id="birthday" class="ui-ctl-element ui-ctl-element_mod">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Номер телефона
                            </p>
                            <div class="ui-ctl ui-ctl-textbox">
                                <input type="tel" name="tel" id="tel" class="ui-ctl-element ui-ctl-element_mod" placeholder="+7 (___)___-__-__">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Город
                            </p>
                            <div class="ui-ctl ui-ctl-textbox">
                                <input type="text" name="city" id="city" class="ui-ctl-element ui-ctl-element_mod">
                            </div>
                        </div>
                        <div class="form__block">
                            <p class="block__text">
                                Портфолио
                            </p>
                            <label class="ui-ctl ui-ctl-file-link ui-ctl-file-link_block" id="file-err">
                                <input type="file" name="file" id="file" class="ui-ctl-element ui-ctl-element_mod">
                                <div class="ui-ctl-label-text ui-ctl-label-text_mod">Выберите файл</div>
                            </label>
                        </div>
                        <button class="ui-btn ui-btn-md crm-btn-save" type="submit" id="sbm" >Сохранить</button>
                    </form>
                </section>
                <div class="modal-view" id="modal">
                    <div class="modal-view-content">
                        <div class="modal-view__image" id="modal-image"></div>
                        <h3 class="modal-view__title" id="modal-title">Успешно!</h3>
                        <p class="modal-view__text" id="modal-text">Ваша заявка успешно принята!</p>
                        <button class="ui-btn ui-btn-md ui-btn-primary-dark crm-btn-save2" id="modal-close">Ок</button>
                    </div>
                </div>
            </div>
            </main>
    </body>
</html>
