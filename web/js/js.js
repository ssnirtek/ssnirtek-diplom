/**
 * site.js — Businka storefront (toast, cart, favorites, auth UI)
 */
$(function () {
    /* --- Toast & guest --- */
    window.showToast = function (msg, time, type) {
        time = time || 2600;
        var toast = $('#toast-minimal');
        if (!toast.length) return;
        toast.stop(true, true).removeClass('error success').text(msg);
        if (type === 'error') toast.addClass('error');
        else if (type === 'success') toast.addClass('success');
        toast.addClass('show');
        setTimeout(function () { toast.removeClass('show error success'); }, time);
    };

    window.isGuest = function () {
        var v = $('body').data('user-is-guest');
        return v === true || v === 1 || v === '1';
    };

    var guestMsg = 'Войдите в аккаунт или зарегистрируйтесь, чтобы добавлять товары в корзину и в избранное.';

    window.showGuestAuthToast = function () {
        showToast(guestMsg, 5200, 'error');
        setTimeout(function () { window.location.href = '/site/login'; }, 4200);
    };

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function handleAuthResult(data, btn, originalText, onSuccess) {
        if (data && data.require_auth) {
            showGuestAuthToast();
            if (btn && btn.length) {
                btn.prop('disabled', false);
                if (originalText) btn.text(originalText);
            }
            return;
        }
        if (data && data.success) {
            if (typeof onSuccess === 'function') onSuccess(data);
            return;
        }
        showToast((data && data.message) || 'Ошибка', 3200, 'error');
        if (btn && btn.length) {
            btn.prop('disabled', false);
            if (originalText) btn.text(originalText);
        }
    }

    /* --- Cart add --- */
    $(document).on('click', '.add-to-cart, .add-to-cart-btn:not(.disabled), .cart-minimal-btn.add-to-cart', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if ($(this).closest('a').length) e.stopImmediatePropagation();

        var btn = $(this);
        var productId = btn.data('id');
        if (!productId) {
            showToast('Ошибка: ID товара не найден', 2800, 'error');
            return false;
        }
        if (isGuest()) {
            showGuestAuthToast();
            return false;
        }

        var originalText = btn.text();
        btn.prop('disabled', true).text('...');

        $.ajax({
            url: '/cart/add',
            type: 'POST',
            data: { product_id: productId, _csrf: csrfToken() },
            dataType: 'json',
            success: function (data) {
                handleAuthResult(data, btn, originalText, function () {
                    showToast('Товар добавлен в корзину', 2400, 'success');
                    btn.addClass('added').text('✓');
                    setTimeout(function () {
                        btn.removeClass('added').prop('disabled', false).text(originalText);
                    }, 1500);
                    if (data.cartCount !== undefined) {
                        $('.cart-count').text('(' + data.cartCount + ')');
                    }
                });
            },
            error: function () {
                showToast('Ошибка соединения', 3200, 'error');
                btn.prop('disabled', false).text(originalText);
            }
        });
        return false;
    });

    /* --- Favorites --- */
    $(document).on('click', '.favorite-btn, .favorite-minimal-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var btn = $(this);
        var productId = btn.data('id');
        if (!productId) {
            showToast('Ошибка: ID товара не найден', 2800, 'error');
            return false;
        }
        if (isGuest()) {
            showGuestAuthToast();
            return false;
        }

        btn.prop('disabled', true);
        $.ajax({
            url: '/favorite/toggle',
            type: 'POST',
            data: { product_id: productId, _csrf: csrfToken() },
            dataType: 'json',
            success: function (data) {
                handleAuthResult(data, btn, null, function () {
                    btn.toggleClass('active');
                    showToast(data.message || 'Готово', 2200, 'success');
                    if (data.favoritesCount !== undefined) {
                        $('.favorites-count').text(data.favoritesCount);
                    }
                    btn.addClass('heart-beat');
                    setTimeout(function () { btn.removeClass('heart-beat'); }, 300);
                });
            },
            error: function () {
                showToast('Ошибка соединения', 3200, 'error');
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
        return false;
    });

    $(document).on('click', '.remove-favorite', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var btn = $(this);
        var productId = btn.data('id');
        var card = btn.closest('.favorite-item');
        if (!productId) {
            showToast('Ошибка: товар не найден');
            return false;
        }

        btn.prop('disabled', true);
        $.ajax({
            url: '/favorite/remove',
            type: 'POST',
            data: { product_id: productId, _csrf: csrfToken() },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    showToast(data.message || 'Удалено из избранного');
                    card.fadeOut(180, function () {
                        $(this).remove();
                        if ($('.favorites-grid .favorite-item').length === 0) {
                            window.location.reload();
                        }
                    });
                    if (data.favoritesCount !== undefined) {
                        $('.favorites-count').text(data.favoritesCount);
                    }
                } else {
                    showToast(data.message || 'Не удалось удалить');
                    btn.prop('disabled', false);
                }
            },
            error: function () {
                showToast('Ошибка соединения');
                btn.prop('disabled', false);
            }
        });
        return false;
    });

    $(document).on('submit', '.add-review form', function (e) {
        if (isGuest()) {
            e.preventDefault();
            showGuestAuthToast();
        }
    });

    /* --- Cart page --- */
    function formatNumber(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function getItemPrice($item) {
        var fromData = $item.data('unit-price');
        if (fromData) return parseFloat(fromData);
        var $block = $item.find('.price-block');
        if (!$block.length) return 0;
        var $newPrice = $block.find('.new-price');
        if ($newPrice.length) {
            return parseFloat($newPrice.text().replace(/\s/g, '').replace('₽', '')) || 0;
        }
        var $regular = $block.find('.regular-price');
        if ($regular.length) {
            return parseFloat($regular.text().replace(/\s/g, '').replace('₽', '')) || 0;
        }
        return 0;
    }

    function updateTotalSum() {
        var subtotal = 0;
        $('.cart-item').each(function () {
            var $item = $(this);
            var price = getItemPrice($item);
            var qty = parseInt($item.find('.quantity-input').val(), 10) || 1;
            var itemTotal = price * qty;
            $item.find('.item-total').text(formatNumber(itemTotal) + ' ₽');
            subtotal += itemTotal;
        });

        var $root = $('#cart-summary-root');
        var welcomeActive = $root.length && String($root.data('welcomeActive')) === '1';
        var welcomePct = $root.length ? (parseFloat($root.data('welcomePercent')) || 0) : 0;
        var welcomeAmount = welcomeActive && subtotal > 0 && welcomePct > 0
            ? Math.round(subtotal * welcomePct / 100)
            : 0;
        var toPay = Math.max(0, subtotal - welcomeAmount);

        $('#total-sum').text(formatNumber(toPay) + ' ₽');
        $('#cart-subtotal').text(formatNumber(subtotal) + ' ₽');
        if ($('#cart-welcome-discount').length) {
            $('#cart-welcome-discount').text('− ' + formatNumber(welcomeAmount) + ' ₽');
        }

        var count = $('.cart-item').length;
        var $row = $('.summary-row-goods');
        if ($row.length) {
            $row.find('.summary-label').text('Товары (' + count + ' шт.)');
        }
        return toPay;
    }

    function updateCartCount() {
        $('.cart-count').text('(' + $('.cart-item').length + ')');
    }

    $(document).on('click', '.quantity-btn.plus', function (e) {
        e.preventDefault();
        var cartItemId = $(this).data('id');
        var input = $('.quantity-input[data-id="' + cartItemId + '"]');
        var max = parseInt(input.attr('max'), 10) || 999;
        var current = parseInt(input.val(), 10) || 1;
        if (current < max) {
            input.val(current + 1).trigger('change');
        } else {
            showToast('Достигнуто максимальное количество');
        }
        return false;
    });

    $(document).on('click', '.quantity-btn.minus', function (e) {
        e.preventDefault();
        var cartItemId = $(this).data('id');
        var input = $('.quantity-input[data-id="' + cartItemId + '"]');
        var current = parseInt(input.val(), 10) || 1;
        if (current > 1) input.val(current - 1).trigger('change');
        return false;
    });

    $(document).on('change', '.quantity-input', function (e) {
        e.preventDefault();
        var input = $(this);
        var cartItemId = input.data('id');
        var quantity = parseInt(input.val(), 10) || 1;
        var max = parseInt(input.attr('max'), 10) || 999;
        quantity = Math.max(1, Math.min(quantity, max));
        input.val(quantity);

        var $cartItem = input.closest('.cart-item');
        $cartItem.find('.item-total').text(formatNumber(getItemPrice($cartItem) * quantity) + ' ₽');
        updateTotalSum();

        $.ajax({
            url: '/cart/update',
            type: 'POST',
            data: { id: cartItemId, quantity: quantity, _csrf: csrfToken() },
            dataType: 'json',
            success: function (data) {
                if (!data.success) {
                    showToast(data.message || 'Ошибка обновления');
                    location.reload();
                }
            },
            error: function () {
                showToast('Ошибка соединения');
                location.reload();
            }
        });
        return false;
    });

    $(document).on('click', '.remove-item', function (e) {
        e.preventDefault();
        var btn = $(this);
        var cartItemId = btn.data('id');
        var $cartItem = btn.closest('.cart-item');
        if (!cartItemId) {
            showToast('Ошибка: ID товара не найден');
            return;
        }
        if (!confirm('Удалить товар из корзины?')) return false;

        $.ajax({
            url: '/cart/remove',
            type: 'POST',
            data: { id: cartItemId, _csrf: csrfToken() },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $cartItem.fadeOut(300, function () {
                        $(this).remove();
                        updateTotalSum();
                        updateCartCount();
                        showToast(data.message || 'Товар удалён из корзины');
                        if ($('.cart-item').length === 0) {
                            setTimeout(function () { location.reload(); }, 500);
                        }
                    });
                } else {
                    showToast(data.message || 'Ошибка удаления');
                }
            },
            error: function () {
                showToast('Ошибка соединения');
            }
        });
        return false;
    });

    if ($('.cart-item').length) {
        setTimeout(function () {
            updateTotalSum();
            updateCartCount();
        }, 100);
    }

    /* --- Scroll reveal (about page) --- */
    function scrollReveal(el) {
        if (!el) return;
        function onScroll() {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight - 100) {
                el.classList.add('show');
                window.removeEventListener('scroll', onScroll);
            }
        }
        window.addEventListener('scroll', onScroll);
        onScroll();
    }
    scrollReveal(document.getElementById('benefitsImage'));

    /* --- Auth UI (login, register, reset) --- */
    $('.login-minimal-input').removeClass('is-invalid is-valid');

    $(document).on('focus', '.login-minimal-input', function () {
        $(this).removeClass('is-invalid');
    });

    $('.password-toggle-btn').remove();
    $('.password-field').each(function () {
        var $field = $(this);
        var $input = $field.find('input').attr('type', 'password');
        $field.css('position', 'relative').append(
            $('<span class="password-toggle-btn" tabindex="-1">👁</span>')
        );
    });

    $(document).on('click', '.password-toggle-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $input = $btn.parent().find('input');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $btn.text('🙈').css('color', '#ff4081');
        } else {
            $input.attr('type', 'password');
            $btn.text('👁').css('color', '#8b8c7b');
        }
    });

    $(document).on('click', '.alert .close-btn', function () {
        $(this).closest('.alert').fadeOut(300);
    });

    setTimeout(function () {
        $('.alert-success-message, .alert-error-message').fadeOut(500);
    }, 5000);
});
