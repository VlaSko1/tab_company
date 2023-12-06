/*BX.ready(function() {
    let wrapperPagg = document.getElementsByClassName('main-ui-pagination')[0];
    console.log(wrapperPagg);
})*/
BX.ready(function () {
    BX.SidePanel.Instance.bindAnchors({
      rules: [
        {
          condition: [
            new RegExp('/company/order/', 'i')
          ],
          handler: function (event, link) {
            event.preventDefault();
            const arLink = link.url.split('*');
            let stringData = arLink[arLink.length - 1];
            BX.SidePanel.Instance.open("wizart:order_grid" + ID, { // TODO Укажи уникальный идентификатор
              cacheable: true,
              contentCallback: function (slider) {
                return new Promise(function (resolve, reject) {
                  BX.ajax.runComponentAction('wizart:view_sidepanel', 'showSidepanel', { // TODO напиши компонент для отображения страницы с данными заказа
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
      ]
    });
  });