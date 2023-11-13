BX.ready(function () {
    BX.addCustomEvent("CrmProgressControlBeforeSave", setStopClick);
    stopClick();
});

function setStopClick(event, event2) {
    //console.log(event2.VALUE);
    //console.log(event2);
    //console.log(event);
    //console.log(event._previousStepId);
    let newStatus = event._currentStepId;
    if (newStatus === 'EXECUTING' || newStatus === 'FINAL_INVOICE' || newStatus === 'WON') {
        event._currentStepId = event._previousStepId;
        try {
            event._currentStepId = event._previousStepId;
            throw new Error();
        } catch {
            event._currentStepId = event._previousStepId;
            BX.UI.Dialogs.MessageBox.alert(
                'Стадия сделки не может быть перемещена на "в работе" и на стадии после неё, если пользователь не является администратором', 
                "Ошибка", 
                (messageBox, button, event) => {
                    messageBox.close();
            }, "Продолжить");
            return false;
        }
    }
}


function stopClick() {
    
   /* let elemsExecutin = document.getElementsByClassName('crm-stage-executing');
    for (let i = 0; i < elemsExecutin.length; i++) {
        //elemsExecutin[i].addEventListener('click', notClick);
        elemsExecutin[i].parentElement.addEventListener('click', notClick);

    }
    function notClick(e) {
        console.log(e.target.parentElement);
        e.stopPropagation();
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    }*/

    BX.bindDelegate(
        document.body, 'click', {className: 'crm-stage-executing' },
        function(e){
            if(!e) {
                e = window.event;
            }
            console.log(e.target);
            return BX.PreventDefault(e);
        }
    );
     
}
