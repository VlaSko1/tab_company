let popupExcel;
let clickBtnExcel = function (idFilter, idBlock, sort) {
    
    popupExcel = BX.PopupWindowManager.create("popup-message", null, {
        content: "Экспорт в файл.",
        autoHide: false,
        closeByEsc: true,
        width: 340,
        height: 210, 
        overlay: {
            backgroundColor: 'black', opacity: '80'
        },
        buttons: [
            new BX.PopupWindowButton({
                text: "Отмена",
                className: "ui-btn",
                events: {
                    click: function () {
                        const elemLink = document.getElementById('link__file');
                        let idFile = elemLink.dataset.id;
                        let filePath = elemLink.dataset.path;
                        this.popupWindow.close(); // закрытие окна
                        BX.ajax.runComponentAction('wizart:workspace', 'delFileById', {
                            mode: 'class',
                            data: {
                                idFile,
                                filePath
                            }
                        }).then(function (response) {
                            console.log(response.data.result);
                        }, function (response) {
                            console.log(response);
                        })
                    }
                }
            })
        ]
    });
    popupExcel.setContent('<div class="popup__text"><p>Экспорт в файл.</p><div class="loader">loading</div></div>');
    popupExcel.show();

    return new Promise(function (resolve, reject) {
        
        filter = BX.Main.filterManager.getById(idFilter).getFilterFieldsValues();
        
        BX.ajax.runComponentAction('wizart:workspace', 'exportExcel', {
            mode: 'class',
            data: {
                filter: BX.Main.filterManager.getById(idFilter).getFilterFieldsValues(),
            }
        }).then(function (response) {

        }, function (response) {
            console.log(response);
        })
    });
}

const detailEl = function (ID) {
    BX.SidePanel.Instance.open("wizart:workspace" + ID, {
        cacheable: true,
        contentCallback: function (slider) {
            return new Promise(function (resolve, reject) {
                BX.ajax.runComponentAction('wizart:view_sidepanel', 'showSidepanel', {
                    mode: 'ajax',
                    data: {
                        ID_ELEMENT: ID
                    }
                }).then(function (response) {
                    resolve({ html: response.data.html });
                }, function (response) {
                    //сюда будут приходить все ответы, у которых status !== 'success'
                    resolve({ html: `<h3 class='error_message'>Ошибка: ${response.errors[0].message}</h3><h3 class='error_message'>Код ошибки: ${response.errors[0].code}</h3>` });
                    setTimeout(() => location.reload(), 3000);
                    //console.log(response);
                });
            }).then(null, function (r) { console.log(r) });
        },
        animationDuration: 100,
        width: 900
    });
}

const bindDeal = function (idBlock, idgrid) {
    const span = document.getElementById('bind_id');
    span.innerText = idBlock;
    const popup = BX.PopupWindowManager.create("popup-message", null, {
        content: "Bind Deal!",
        autoHide: false,
        closeByEsc: true,
        width: 340,
        overlay: {
            backgroundColor: 'black', opacity: '80'
        },
        buttons: [
            new BX.PopupWindowButton({
                text: "Сохранить",
                className: "ui-btn ui-btn-success",
                events: {
                    click: function () {
                        idDeal = BX.UI.SelectorManager.instances['addDeal'].itemsSelected;
                        idDeal = Object.keys(idDeal);
                        if (idDeal[0] !== 'not_deal') {
                            idDeal = idDeal.join('').slice(2);
                            return new Promise(function (resolve, reject) {
                                idBlock = span.innerText; // иначе сохраняется id блока с ранее привязанной сделкой и сделка не привызывается к новому блоку
                                BX.ajax.runComponentAction('wizart:workspace', 'bindDeal', {
                                    mode: 'class',
                                    data: {
                                        sessid: BX.message('bitrix_sessid'),
                                        idBlockEl: idBlock,
                                        idDeal: idDeal
                                    }
                                }).then(function (response) {
                                    if (response.data.status == 'error') {
                                        message_title = 'Ошибка';
                                        message = response.data.error_message
                                    } else {
                                        message_title = 'Успешно';
                                        message = response.data.message
                                    }
                                    BX.UI.Dialogs.MessageBox.alert(message_title, message, (messageBox, button, event) => {
                                        messageBox.close();
                                        popup.close();
                                        BX.Main.gridManager.getInstanceById(idgrid).reloadTable();
                                    });
                                }, function (response) {
                                    message_title = 'Ошибка';
                                    message = response.errors[0].message + ` Код ошибки: ${response.errors[0].code}`;
                                    BX.UI.Dialogs.MessageBox.alert(message_title, message, (messageBox, button, event) => {
                                        messageBox.close();
                                        popup.close();
                                        BX.Main.gridManager.getInstanceById(idgrid).reloadTable();
                                    });
                                    //сюда будут приходить все ответы, у которых status !== 'success'
                                    console.log(response);
                                });
                            })
                        }

                    }
                }
            }),
            new BX.PopupWindowButton({
                text: "Отмена",
                className: "ui-btn",
                events: {
                    click: function () {
                        this.popupWindow.close(); // закрытие окна

                    }
                }
            })
        ]
    });
    popup.setContent(BX('addDeal'));
    popup.show();
};

/**
 * Открывает окно с краткими данными по сделке: имя и фамилия ответственного, стоимость сделки
 * @param {string} assignedName имя ответственного за сделку
 * @param {string} assignedLastName фамилия ответственного за сделку
 * @param {number} assignedId id ответственного за сделку
 * @param {number} opportunity сумма сделки
 * @param {object} target объект привязки
 * @return void ничего не возвращает
 */
function dealViewInfo(assignedName, assignedLastName, assignedId, opportunity, target) {
    const createNew = BX.PopupWindowManager.create("dealView", target, {
        width: 400,
        height: 150,
        content: BX.create('div', {
            props: {className: 'dealInfo'},
            html:   '<div id="dealInfoContent">' +
                        '<span id="deal_view_assigned" class="deal_view_assigned"></span>' +
                        '<span id="deal_view_opportunity" class="deal_view_opportunity"></span>' +
                    '</div>'
        }),
        closeIcon: {right: "8px", top: "8px", transform: 'scale(1.5)'},
        zIndex: 0,
        closeByEsc: true,
        events: {
            onPopupClose: function () {
                this.destroy();
            },
            onAfterPopupShow: function () {
                var assignedNameLink = `Ответственный: <a class="grid-deal-view" target="_top" href="/company/personal/user/${assignedId}/">${assignedName+' '+assignedLastName}</a>`;
                BX('deal_view_assigned').innerHTML = assignedNameLink
                BX('deal_view_opportunity').innerHTML = 'Сумма заказа: '+opportunity;
            }
        },
        offsetLeft: -100,
        offsetTop: -200,
        draggable: true,
        autoHide: true,
    });
    createNew.show();
}

BX.ready(function () {
    BX.addCustomEvent("onPullEvent", function (module_id, command, params) {
        if (module_id == "test" && command == 'check') {
            popupExcel.setContent('<div class="popup__text"><a href="' + params.path + 
                                                            '" target="_blank" data-id="'+ params.idFile + 
                                                            '" data-path="' + params.filePath +
                                                            '" id="link__file">Скачать файл</a></div>');
        }
    });
});
