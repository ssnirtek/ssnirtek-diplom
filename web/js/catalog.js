/**
 * catalog.js — AJAX-фильтрация каталога
 */
$(function () {
    var filterForm = $('#filter-form');
    var productsContainer = $('#products-container');
    if (!filterForm.length || !productsContainer.length) return;

    function notify(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, 3000, type === 'error' ? 'error' : 'success');
            return;
        }
        $('#toast-minimal').text(message).addClass('show');
        setTimeout(function () { $('#toast-minimal').removeClass('show'); }, 3000);
    }

    function loadProducts() {
        var formData = filterForm.serialize();
        var submitBtn = $('#filter-submit');
        productsContainer.addClass('loading');
        if (submitBtn.length) submitBtn.prop('disabled', true).text('Загрузка...');

        $.ajax({
            url: filterForm.attr('action') || window.location.pathname,
            type: 'GET',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success && response.html) {
                    productsContainer.html(response.html);
                    var newUrl = window.location.pathname + (formData ? '?' + formData : '');
                    window.history.pushState({ path: newUrl }, '', newUrl);
                } else {
                    notify(response.message || 'Ошибка при загрузке', 'error');
                }
            },
            error: function () {
                notify('Ошибка соединения', 'error');
            },
            complete: function () {
                productsContainer.removeClass('loading');
                if (submitBtn.length) submitBtn.prop('disabled', false).text('Применить');
            }
        });
    }

    $('#filter-category, #filter-sort, #filter-discount').on('change', loadProducts);
    $('#filter-submit').on('click', function (e) {
        e.preventDefault();
        loadProducts();
    });

    $(document).on('click', 'a.filter-minimal-reset', function (e) {
        e.preventDefault();
        $('#filter-category').val('');
        $('#filter-sort').val('');
        $('#filter-discount').prop('checked', false);
        loadProducts();
    });

    window.addEventListener('popstate', function () {
        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.html) {
                    productsContainer.html(response.html);
                    var params = new URLSearchParams(window.location.search);
                    $('#filter-category').val(params.get('category_id') || '');
                    $('#filter-sort').val(params.get('sort') || '');
                    $('#filter-discount').prop('checked', params.get('discount_only') === '1');
                }
            }
        });
    });
});
