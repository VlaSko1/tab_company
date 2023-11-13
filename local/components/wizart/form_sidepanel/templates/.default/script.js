BX.ready(function() {
  let form = document.getElementById('form');
  form.onsubmit = formSubmit;

  /* Подключение маски для поля ввода телефона */
  const telInput = document.getElementById('tel');
  telInput.oninput = mask;
  telInput.onfocus = mask;
  telInput.onblur = mask;

  /*Подключаем библиотеку datepicker для выбора даты  */
  const datepicker = new Datepicker('#birthday', {
    max: (function(){
      const date = new Date();
      date.setDate(date.getDate());
      return date;
    })(),
    onChange: (date) => {
      if (date !== undefined) {
        let dateInput = date.toLocaleString("ru", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric"
        });
        validateField('birthday', dateInput);
        writeDateToStorage('birthday', dateInput);
      } 
    }
  });

  /* Подключение плагина kladr-api*/
  $(function () {
    $('[name="city"]').fias({
		type: $.fias.type.city,
      change: function (obj) {
        var address = $.fias.getAddress('.js-form-address');
        
        $('#address').text(address);
      },
      withParents: false,
      verify: true,
      select: function (obj) {
        validateField('city', obj.name);
      },
      check: function (obj) {
        if (obj) {
          validateField('city', obj.name);
        } else {
          validateField('city', obj);
        }
      }
    });

  });

  /* Создание объекта экземпляров tippy */
  objTippy = createTippyInstance();

  /* Реализация запуска валидации после того как пользователь убрал фокус с поля  */
  hangsEventHandlers();
  loadDateFromStorage();

});

/* Константы */
let objTippy = {};
const objValidation = {
  'name': /^[А-Яа-я_-ё]{2,20}$/,
  'surname': /^[А-Яа-я_-ё]{2,30}$/,
  'email': /^([a-zA-Z0-9_\-\.]+)@([a-z0-9_\-\.]+)\.([a-z]{2,6})$/,
  'birthday': /^\d{2}\.\d{2}\.\d{4}$/,
  'tel': /^\+7\s\(\d{3}\)\d{3}-\d{2}-\d{2}$/,
  'city': /^[А-Яа-я_ё-\s]{2,35}$/,
  'file': /^[^<>"?|]+\.(jpg|png|jpeg)$/,
};
const arrField = [
  'name',
  'surname',
  'email',
  'birthday',
  'tel',
  'city',
  'file',
];

const objBtnActivity = {
  'name': true,
  'surname': true,
  'email': true,
  'birthday': true,
  'tel': true,
  'city': true,
  'file': true,
};
const objFieldError = {
  'name': 'Поле "Имя" должно содержать от 2 до 20 символов кирилицы, может содержать подчёркивание или дефис.',
  'surname': 'Поле "Фамилия" должно содержать от 2 до 30 символов кирилицы, может содержать подчёркивание или дефис.',
  'email': 'Поле "Email" должно содержать адрес электронной почты.',
  'birthday': 'Поле "Дата рождения" должно содержать дату рождения в формате "дд.мм.гггг", регистрирующийся человек ' + 
              'должен быть не моложе текущего дня.',
  'tel': 'В поле "Номер телефона" нужно ввести 10 цифр номера телефона российского сотового оператора, ' +
          '+7 или 8 вводить не требуется.',
  'city': 'В названии города могут содержаться от 2 до 35 букв кирилицы, знак подчёркивания или дефиса. Город должен реально существовать.',
  'file': 'Файл портфолио должен иметь расширение "jpg", "jpeg" или  "png" и не должен быть больше 20 Мбайт.',
}

/**
 * Функция задаёт обработчики события "blur" на все поля input, а так же задаёт обработчика событий для частных случаев полей input,
 * задаёт событие нажатия на кнопку закрытия модального окна
 * @returns {void}
 */
function hangsEventHandlers() {
  Object.keys(objValidation).forEach(key => {
    if (key === "file") {
      document.getElementById(key).addEventListener('change', getNameValue);
    } else {
      document.getElementById(key).addEventListener("blur", (e) => {
        getNameValue(e); 
        document.getElementById(key).addEventListener('input', getNameValue);
      }, {once: true});
    }
    
    
  });
  document.getElementById('modal-close').addEventListener('click', () => {
    hideModal(document.getElementById('modal'));
  })
}

/**
 * Получает ключ и значение из входящего события, вызывает функции сохранения данных в хранилище браузера, а так же валидации данных. 
 * @param {object} e событие взаимодействия с интересующи элементом (тегом <input>).
 */
function getNameValue(e) {
  let {name, value } = e.target;
  writeDateToStorage(name, value);
  validateField(name, value); 
}

/**
 * Валидируем конкретное поле по его имени и значению
 * @param {string} nameField имя поля для валидации
 * @param {string} valueField строковое значение введенное в поле (для файла это строка с его названием)
 * @returns возвращает true если поле прошло валидацию и false если не прошло
 */
function validateField(nameField, valueField) {
  let stateValid = true;
  if(objValidation[nameField].test(valueField)) {
    //let errorBlock = document.getElementById(`${nameField}-error`);
    objTippy[nameField].hide();
    
    //errorBlock.style.display = 'none';
    if (nameField === 'birthday') {
      
      const dateNow = new Date();
      const dateBirthday = getObjDate(valueField);
      if ((dateNow - dateBirthday) < 0  ) {
        stateValid = false;
        //errorBlock.style.display = 'block';
        objTippy[nameField].show();
      }
    }
    if (nameField === 'city') {
      let elem = document.getElementById('city');
      let idKladr = elem.dataset.kladrId;
      if (! idKladr) {
        
        stateValid = false;
        objTippy[nameField].show();
        //errorBlock.style.display = 'block';
        sessionStorage.removeItem('idCity');
      } else {
        sessionStorage.setItem('idCity', idKladr)
      }
     
    }
  } else {
    stateValid = false;
    //let errorBlock = document.getElementById(`${nameField}-error`);
    objTippy[nameField].show();
    //errorBlock.style.display = 'block';
    if (nameField === 'city') {
      sessionStorage.removeItem('idCity');
    }
  }
  controlBtnActivity(nameField, stateValid);
  return stateValid;
}

/**
 * Преобразует строковый формат даты из поля дата вида "дд.мм.гггг" в объект даты
 * @param {string} strDate дата записанная в формате строке вида "дд.мм.гггг"
 * @returns возвращает объект даты на основании входного строкового значения
 */
function getObjDate(strDate) {
  const objDate = new Date;
  let [day, mounth, year] = strDate.split('.');
  objDate.setFullYear(year, mounth - 1, day);
  return objDate;
}

/**
 * Устанавливает значения полей (input), которые были до перезагрузки страницы в соответствующие поля после перезагрузки
 */
function loadDateFromStorage() {
  if (sessionStorage.length) {
    for (let i = 0; i < arrField.length; i++) {
      if (arrField[i] === 'city') {
        const elemForm = document.getElementById(arrField[i]);
        elemForm.value = sessionStorage.getItem(arrField[i]);
        if (sessionStorage.getItem('idCity')) {
          const elemForm = document.getElementById(arrField[i]);
          elemForm.setAttribute("data-kladr-id", sessionStorage.getItem('idCity'));
        }
        validateField(arrField[i], elemForm.value);
        continue;
      }
      if (arrField[i] === 'file') {
        continue;
      }
      document.getElementById(arrField[i]).value = sessionStorage.getItem(arrField[i]);
      validateField(arrField[i], sessionStorage.getItem(arrField[i]));
    }
  }
}

/**
 * Сохраняет значения по ключу в хранилище сессии
 * @param {string} name имя, которое будет записано в качестве значения ключа в хранилище сессии
 * @param {string} value значение, которое будет присвоено соответствующему ключу хранилища сессии
 */
function writeDateToStorage(name, value) {
  sessionStorage.setItem(name, value); 
}

/**
 * Отключает, активирует кнопку отправления формы в зависимости от введённых значений в поля формы
 * @param {string} key имя ключа соответствующего названию конкретного поля формы
 * @param {boolean} bool значение логического типа соответствует факту прохождения (true) или непрохождения (false) данным полем валидации 
 */
function controlBtnActivity(key, bool) {
  objBtnActivity[key] = bool;
  if (Object.entries(objBtnActivity).every((el) => el[1] === true)) {
    document.getElementById('sbm').removeAttribute('disabled');
  } else {
    document.getElementById('sbm').setAttribute('disabled', 'disabled');
  }
}

/**
 * Данная функция осуществляет отправку формы (если валидация каждого поля формы прошла) или отменяет отправку формы 
 * @param {object} e событие отправки формы
 */
 async function formSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const elemData = {};
  [...e.target.elements].forEach((el) => elemData[el.name] = el.value);
  const formData = new FormData(form);
  let idCity = sessionStorage.getItem('idCity');
  if (idCity) {
    formData.append('idCity', idCity);
  }

  if (validateForm(elemData)) {
    let urlForm = document.location.origin + '/form/validate.php';
    let response = await fetch(urlForm, {
      method: 'POST',
      body: formData,
    });
    
    let data = await response.json();

    if (data.result) {
      document.getElementById('modal-image').classList.remove('modal-view__image_bad');
      document.getElementById('modal-title').innerText = "Успешно!";
      document.getElementById('modal-text').innerText = "Ваша заявка успешно принята!";
      showModal();
      clearForm();
    } else {
      document.getElementById('modal-image').classList.add('modal-view__image_bad');
      document.getElementById('modal-title').innerText = "Ошибка!";
      document.getElementById('modal-text').innerText = data.message;
      showModal();
    }
  }
}

/**
 * Данная функция валидирует форму и отображает (скрывает) ошибку для полей непрошедших (прошедших) валидацию
 * @param {object} objForValid объект, ключи которого - названия полей формы, значения - значения полей формы
 * @returns {boolean} возвращает true, если валидация формы прошла успешно и false - в противном случае
 */
function validateForm(objForValid) {
  let stateValid = true; 
  Object.keys(objValidation).forEach((key) => {
    if (!validateField(key, objForValid[key])) {
      stateValid = false;
    }
  }); 

  return stateValid;
}

// Валидация поля для ввода телефона 

/**
 * Устанавливает курсор в нужную позицию 
 * @param {number} pos индекс текущей позиции курсора
 * @param {object} elem HTML элемент (input) 
 */
function setCursorPosition(pos, elem) {
  elem.focus();
  if (elem.setSelectionRange) {
    elem.setSelectionRange(pos, pos);
  } else if (elem.createTextRange) {
    let range = elem.createTextRange();
    range.collapse(true);
    range.moveEnd("character", pos);
    range.moveStart("character", pos);
    range.select()
  }
}

/**
 * Функция отображает или удаляет введенные символы соответствующие регулярном выражению с учётом маски matrix
 * @param {object} event событие происходящее с элементом input
 */
function mask(event) {
  let matrix = "+7 (___)___-__-__",
  i = 0,
  def = matrix.replace(/\D/g, ""),
  val = this.value.replace(/\D/g, "");

  if (def.length >= val.length) {
    val = def;
  } 
  this.value = matrix.replace(/./g, function(a) {
    return /[_\d]/.test(a) && i < val.length ? val.charAt(i++) : i >= val.length ? "" : a});
  if (event.type == "blur") {
    if (this.value.length == 2) this.value = ""
  } else {
    setCursorPosition(this.value.length, this)
  }
}

/**
 * Отображает модальное окно в случае успешной отправки формы и валидации её на сервере
 */
function showModal() {
  const modal = document.getElementById('modal');
  modal.style.display = 'flex';
}

/**
 * Функция скрывает из элемент разметки 
 * @param {object} elem html-элемент, которому данная фнукция присваиват значение display: none
 */
function hideModal(elem) {
  elem.style.display = 'none';
}

/**
 * Очищает форму и хранилище сессии в случе успешной отправки формы
 */
function clearForm() {
  let form = document.getElementById('form');
  form.reset();
  sessionStorage.clear();
}

/**
 * Возвращает объект ключи которого - названия полей ввода, а значения экземпляры библиотеки tippy.js
 * @returns {object} объект ключи которого - названия полей ввода, а значения экземпляры библиотеки tippy.js
 */
function createTippyInstance() {
  const objTippyInstance = {};
  for (let i = 0; i < arrField.length; i++) {
    if (arrField[i] === 'file') {
      objTippyInstance[arrField[i]] = getTippyElem('file-err', objFieldError[arrField[i]]);
      continue;
    }
    objTippyInstance[arrField[i]] = getTippyElem(arrField[i], objFieldError[arrField[i]]);
  }
  return objTippyInstance;
}

/**
 * Создаёт экземпляр сущности tippy для поля ввода input с id nameField
 * @param {string} nameField id поля ввода input формы
 * @param {string} textError текст ошибки для соответствующего поля ввода
 * @returns 
 */
function getTippyElem(nameField, textError) {
  return tippy(document.getElementById(nameField), {
    content: textError,
    hideOnClick: false,
    placement: 'bottom',
    theme: 'error',
    trigger: 'manual',
   });
}
