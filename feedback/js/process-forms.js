'use strict';

var ProcessForm = function (settings) {
    this._settings = {
        selector: '#email-form', // дефолтный селектор
        isUseDefaultSuccessMessage: true // отображать дефолтное сообщение об успешной отправки формы
    };

    for (var propName in settings) {
        if (settings.hasOwnProperty(propName)) {
            this._settings[propName] = settings[propName];
        }
    }
    this._form = $(this._settings.selector).eq(0);
};

ProcessForm.prototype = function () {
    // переключить во включенное или выключенное состояние кнопку submit
    var _changeStateSubmit = function (_this, state) {
        // 
        _this._form.find('[type="submit"]').prop('disabled', state);
        if(state) {
            _this._form.find('[type="submit"]').prop('value', 'Отправляется');
        } else {
            _this._form.find('[type="submit"]').html('value', 'Оставить заявку');
        }
    };

    var _showForm = function (_this) {
        _this._form[0].reset();
    };


    // собираем данные для отправки на сервер
    var _collectData = function (_this) {
        var output;
        output = new FormData(_this._form[0]);
        return output;
    };

    // отправка формы
    var _sendForm = function (_this) {
        $(document).trigger('beforeSubmit', [_this._form]);

        if (!_this._form.find('.form-error').hasClass('d-none')) {
            _this._form.find('.form-error').addClass('d-none');
        }

        $.ajax({
            context: _this,
            type: "POST",
            url: _this._form.attr('action'),
            data: _collectData(_this), // данные для отправки на сервер
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () {
                _changeStateSubmit(_this, true);
            },
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                
                return myXhr;
            }
        })
            .done(_success)
            .fail(_error)
    };

    // при получении успешного ответа от сервера
    var _success = function (data) {
        var _this = this;
        // при успешной отправки формы
        if (data.result === "success") {
            $(document).trigger('pf_success', {data: this});
            if (_this._settings.isUseDefaultSuccessMessage) {
                _this._form.css("display", "none");
                _this._form.parent().find('.success-message').css("display", "block");
            }
            return;
        }
        
        // если произошли ошибки при отправке
        _this._form.css("display", "none");
        _this._form.parent().find('.w-form-fail').css("display", "block");
        _changeStateSubmit(this, false);

        // выводим ошибки которые прислал сервер
        for (var error in data) {
            if (!data.hasOwnProperty(error)) {
                continue;
            }
            switch (error) {
                case 'log':
                    $.each(data[error], function (key, value) {
                        console.log(value);
                    });
                    break;
                default:
                     console.log("всё плохо");
            }
        }
    };

    // если не получили успешный ответ от сервера
    var _error = function () {
        _this._form.css("display", "none");
        _this._form.parent().find('.w-form-fail').css("display", "block");
    };

    // функция для инициализации
    var _init = function () {
        _setupListener(this);
    };

    var _reset = function () {
        _showForm(this);
    };

    // устанавливаем обработчики событий
    var _setupListener = function (_this) {
        $(document).on('submit', _this._settings.selector, function (e) {
            e.preventDefault();
            _sendForm(_this);
        });
    };
    return {
        init: _init,
        reset: _reset
    }
}();
