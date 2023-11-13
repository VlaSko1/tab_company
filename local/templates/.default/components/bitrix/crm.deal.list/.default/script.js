BX.ready(function () {
  BX.SidePanel.Instance.bindAnchors({
    rules: [
      {
        condition: [
          new RegExp('/application/linked/', 'i')
        ],
        handler: function (event, link) {
          event.preventDefault();
          const arLink = link.url.split('/');
          let ID = arLink[arLink.length - 1];
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
            width: 800
          });


        }

      },
      {
        condition: [
          new RegExp('/application/notlinked/', 'i')
        ],
        handler: function (event, link) {
          const span = document.getElementById('bind_id');
          event.preventDefault();
          const arLink = link.url.split('/');
          let strWithId = arLink[arLink.length - 1];
          let arWithId = strWithId.split('_');
          let idDeal = arWithId.pop();
          let idGrid = 'CRM_DEAL_LIST_V12';  // id грида сделок (можно посмотреть в id таблицы на странице со сделками )

          span.innerText = idDeal;

          const popupDeal = BX.PopupWindowManager.create("popupDeal", null, {
            content: 'AppDeal',
            autoHide: false,
            closeByEsc: true,
            width: 480,
            height: 400,
            closeIcon: { right: "8px", top: "8px", transform: 'scale(1.5)' },
            overlay: {
              backgroundColor: 'black', opacity: '80'
            },
            events: {
              onPopupClose: function () {
                //this.destroy();
              },
            },
            buttons: [
              new BX.PopupWindowButton({
                text: "Сохранить",
                className: "ui-btn ui-btn-success",
                events: {
                  click: function () {
                    let statusStartReqBackend = false;
                    idBlockEl = document.getElementById('applications_search_input').value;
                    if (idBlockEl === '') {
                      let message_title = 'Окно с диалогом';
                      let message = "Пожалуйста, выберите заявку из списка ниже."
                      BX.UI.Dialogs.MessageBox.alert(message, message_title, (messageBox, button, event) => {
                        messageBox.close();
                      });
                    } else {
                      let start = idBlockEl.search(/\[/);
                      let end = idBlockEl.search(/\]/);
                      idBlockEl = idBlockEl.substring(start + 1, end);
                      if (!arWithId.some((el) => el === idBlockEl)) {
                        let message_title = 'Окно с диалогом';
                        let message = `Пожалуйста, выберите не связанную со сделкой заявку из списка ниже, идентификатор которых равен: ${arWithId}`;
                        if (arWithId.length === 1 && Number(arWithId[0]) === 0 || arWithId.length === 0) message = 'Не свободных заявок для привязки к сделке';
                        BX.UI.Dialogs.MessageBox.alert(message, message_title, (messageBox, button, event) => {
                          messageBox.close();
                        });
                      } else {
                        statusStartReqBackend = true;
                        arWithId = arWithId.filter((el) => el !== idBlockEl);
                      }
                    }
                    
                    if (statusStartReqBackend) {
                      return new Promise(function (resolve, reject) {
                        idDeal = document.getElementById('bind_id').innerText;
                        BX.ajax.runComponentAction('wizart:workspace', 'bindDeal', {
                          mode: 'class',
                          data: {
                            sessid: BX.message('bitrix_sessid'),
                            idBlockEl: idBlockEl,
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
                            popupDeal.close();
                            BX.Main.gridManager.getInstanceById(idGrid).reloadTable();
                          });
                        }, function (response) {
                          message_title = 'Ошибка';
                          message = response.errors[0].message + ` Код ошибки: ${response.errors[0].code}`;
                          BX.UI.Dialogs.MessageBox.alert(message_title, message, (messageBox, button, event) => {
                            messageBox.close();
                            popupDeal.close();
                            BX.Main.gridManager.getInstanceById(idGrid).reloadTable();
                          });
                          //сюда будут приходить все ответы, у которых status !== 'success'
                          console.log(response);
                        });
                      })
                    } else {
                      return;
                    }

                  }
                }
              }),
              new BX.PopupWindowButton({
                text: "Отмена",
                className: "ui-btn",
                events: {
                  click: function () {
                    popupDeal.close();
                  }
                }
              })
            ]

          });
          popupDeal.setContent(BX('addApp'));
          popupDeal.show();
        }
      }
    ]
  });
});
