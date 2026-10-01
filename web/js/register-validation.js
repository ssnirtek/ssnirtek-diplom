/**
 * register-validation.js — live validation for registration form
 */
$(function () {
    function markInvalid($field, message) {
        $field.removeClass('is-valid').addClass('is-invalid');
        var $err = $field.siblings('.login-minimal-error');
        if (!$err.length) {
            $field.after('<div class="login-minimal-error">' + message + '</div>');
        } else {
            $err.text(message).show();
        }
    }

    function validateField($el, ok) {
        if ($el.val().length === 0) {
            $el.removeClass('is-valid is-invalid');
            return;
        }
        $el.toggleClass('is-valid', ok).toggleClass('is-invalid', !ok);
    }

    $('#user-email').on('blur input', function () {
        validateField($(this), /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test($(this).val()));
    });

    $('#user-password_hash').on('blur input', function () {
        validateField($(this), $(this).val().length >= 8);
    });

    $('#user-password_repetition').on('blur input', function () {
        var pwd = $('#user-password_hash').val();
        validateField($(this), $(this).val() === pwd && pwd.length >= 8);
    });

    $('#user-full_name').on('blur input', function () {
        var v = $(this).val();
        validateField($(this), /^[А-Яа-яЁё\s-]+$/.test(v) && v.length >= 5);
    });

    $('#user-phone').on('blur input', function () {
        var digits = $(this).val().replace(/\D/g, '');
        validateField($(this), digits.length === 0 || digits.length === 11);
    });

    $('#user-date_born').on('blur input', function () {
        validateField($(this), /^\d{2}-\d{2}-\d{4}$/.test($(this).val()));
    });

    $('#user-form').on('beforeSubmit', function () {
        var isValid = true;
        var firstError = null;
        var fields = [
            { id: '#user-email', regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/ },
            { id: '#user-password_hash', minLength: 8 },
            { id: '#user-password_repetition', matchWith: '#user-password_hash' },
            { id: '#user-full_name', regex: /^[А-Яа-яЁё\s-]+$/, minLength: 5 },
            { id: '#user-phone', phoneLength: 11 },
            { id: '#user-date_born', regex: /^\d{2}-\d{2}-\d{4}$/ }
        ];

        fields.forEach(function (field) {
            var $field = $(field.id);
            if (!$field.length) return;
            var value = $field.val().trim();

            if (field.phoneLength === undefined && value === '') {
                markInvalid($field, 'Поле обязательно');
                if (!firstError) firstError = $field;
                isValid = false;
                return;
            }
            if (field.phoneLength) {
                var digits = value.replace(/\D/g, '');
                if (digits.length > 0 && digits.length !== field.phoneLength) {
                    markInvalid($field, 'Введите полный номер');
                    if (!firstError) firstError = $field;
                    isValid = false;
                }
                return;
            }
            if (field.regex && !field.regex.test(value)) {
                markInvalid($field, 'Некорректный формат');
                if (!firstError) firstError = $field;
                isValid = false;
                return;
            }
            if (field.minLength && value.length < field.minLength) {
                markInvalid($field, 'Минимум ' + field.minLength + ' символов');
                if (!firstError) firstError = $field;
                isValid = false;
                return;
            }
            if (field.matchWith && value !== $(field.matchWith).val()) {
                markInvalid($field, 'Пароли не совпадают');
                if (!firstError) firstError = $field;
                isValid = false;
                return;
            }
            $field.removeClass('is-invalid').addClass('is-valid');
        });

        if (!$('#user-agree').is(':checked')) {
            alert('Необходимо согласиться на обработку персональных данных');
            isValid = false;
        }

        if (!isValid && firstError) {
            firstError.focus();
            return false;
        }
        return true;
    });

    $('.login-minimal-input').on('focus', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.login-minimal-error').hide();
    });
});
