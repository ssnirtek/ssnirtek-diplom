/**
 * admin.js — Businka admin panel
 */
(function () {
    'use strict';

    var currentProductId = null;
    var currentOrderId = null;

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showNotification(message, type) {
        var el = document.getElementById('notification');
        if (el) {
            el.textContent = message;
            el.className = 'notification ' + type;
            el.style.display = 'block';
            setTimeout(function () { el.style.display = 'none'; }, 3000);
            return;
        }
        if (window.jQuery) {
            var $n = window.jQuery('#notification');
            if ($n.length) {
                $n.removeClass('success error').addClass(type).text(message).fadeIn(200);
                setTimeout(function () { $n.fadeOut(200); }, 3000);
            }
        }
    }
    window.showNotification = showNotification;

    /* --- Sidebar & flash --- */
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('admin-sidebar');
        var openBtn = document.getElementById('admin-sidebar-open');
        var closeBtn = document.getElementById('admin-sidebar-close');
        var backdrop = document.getElementById('admin-sidebar-backdrop');

        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('is-open');
            if (backdrop) {
                backdrop.setAttribute('hidden', '');
                backdrop.classList.remove('is-open');
            }
            document.body.style.overflow = '';
        }

        function openSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('is-open');
            if (backdrop) {
                backdrop.removeAttribute('hidden');
                backdrop.classList.add('is-open');
            }
            document.body.style.overflow = 'hidden';
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);

        document.querySelectorAll('.admin-sidebar__link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 768) closeSidebar();
            });
        });

        document.querySelectorAll('.admin-flash, .admin-login-flash').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity 0.35s ease';
                el.style.opacity = '0';
                setTimeout(function () {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 400);
            }, 5000);
        });

        /* Filters (products, reviews) */
        var filtersHeader = document.querySelector('.filters-header');
        var filtersContent = document.querySelector('.filters-content');
        if (filtersHeader && filtersContent) {
            filtersHeader.addEventListener('click', function (e) {
                e.stopPropagation();
                filtersContent.classList.toggle('show');
                filtersHeader.classList.toggle('active');
            });
            var hasActive = false;
            document.querySelectorAll('.filter-input').forEach(function (input) {
                if (input.value) hasActive = true;
            });
            if (hasActive) {
                filtersContent.classList.add('show');
                filtersHeader.classList.add('active');
            }
        }

        /* Product form */
        var discountCheckbox = document.getElementById('is_discount_checkbox');
        var oldPriceField = document.getElementById('old_price_field');
        function toggleOldPrice() {
            if (!discountCheckbox || !oldPriceField) return;
            oldPriceField.style.display = discountCheckbox.checked ? 'block' : 'none';
        }
        toggleOldPrice();
        if (discountCheckbox) discountCheckbox.addEventListener('change', toggleOldPrice);

        var imageInput = document.getElementById('product-imagefile') || document.getElementById('image_file_input');
        var imagePreview = document.getElementById('imagePreview');
        var previewImg = imagePreview ? imagePreview.querySelector('img') : null;
        if (imageInput) {
            imageInput.addEventListener('change', function () {
                var file = this.files[0];
                if (file && previewImg) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        if (imagePreview) imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else if (imagePreview) {
                    imagePreview.style.display = 'none';
                }
            });
        }

        /* Modal overlay clicks */
        document.addEventListener('click', function (e) {
            if (e.target.id === 'quantityModal') window.closeQuantityModal();
            if (e.target.id === 'statusModal') window.closeStatusModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') window.closeStatusModal();
        });

        initOrderDetailPage();
    });

    /* --- Products --- */
    window.showQuantityModal = function (id, name, currentQuantity) {
        currentProductId = id;
        document.getElementById('modalProductName').textContent = 'Товар: ' + name;
        document.getElementById('modalQuantity').value = currentQuantity;
        document.getElementById('quantityModal').style.display = 'flex';
    };

    window.closeQuantityModal = function () {
        document.getElementById('quantityModal').style.display = 'none';
        currentProductId = null;
    };

    window.saveQuantity = function () {
        var quantity = document.getElementById('modalQuantity').value;
        if (!currentProductId) {
            alert('Ошибка: товар не выбран');
            return;
        }
        if (quantity < 0) {
            alert('Количество не может быть отрицательным');
            return;
        }
        var csrf = getCsrfToken();
        fetch('/admin/quick-update-quantity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ id: currentProductId, quantity: quantity, _csrf: csrf })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) location.reload();
                else alert('Ошибка: ' + (data.message || 'Не удалось обновить'));
            })
            .catch(function () { alert('Произошла ошибка при обновлении'); });
    };

    window.toggleProductStatus = function (productId) {
        fetch('/admin/toggle-product-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({ product_id: productId })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showNotification('Статус обновлён', 'success');
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                }
            })
            .catch(function () { showNotification('Ошибка соединения', 'error'); });
    };

    /* --- Orders: list page --- */
    window.showStatusModal = function (orderId, currentStatus) {
        if (document.getElementById('orderId') && arguments.length === 0) {
            openOrderDetailModal();
            return;
        }
        currentOrderId = orderId;
        var modalOrderId = document.getElementById('modalOrderId');
        var modalStatus = document.getElementById('modalStatus');
        var statusModal = document.getElementById('statusModal');
        if (modalOrderId) modalOrderId.textContent = orderId;
        if (modalStatus) modalStatus.value = currentStatus;
        if (statusModal) {
            statusModal.classList.add('active');
            statusModal.style.display = 'flex';
        }
    };

    window.closeStatusModal = function () {
        var statusModal = document.getElementById('statusModal');
        if (statusModal) {
            statusModal.classList.remove('active');
            statusModal.style.display = 'none';
        }
        currentOrderId = null;
    };

    function openOrderDetailModal() {
        var modal = document.getElementById('statusModal');
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.add('active');
        if (window.jQuery) window.jQuery(modal).fadeIn(200);
    }

    window.saveStatus = function () {
        if (!currentOrderId) return;
        var status = document.getElementById('modalStatus') ? document.getElementById('modalStatus').value : '';
        var csrf = getCsrfToken();
        fetch('/admin/update-order-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ order_id: currentOrderId, status: status, _csrf: csrf })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showNotification('Статус заказа обновлён', 'success');
                    window.closeStatusModal();
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                }
            })
            .catch(function () { showNotification('Ошибка соединения', 'error'); });
    };

  /* --- Orders: detail page --- */
    window.updateStatus = function () {
        var orderIdEl = document.getElementById('orderId');
        var statusEl = document.getElementById('orderStatus');
        if (!orderIdEl || !statusEl) return;

        var csrf = getCsrfToken();
        var body = new URLSearchParams({
            order_id: orderIdEl.value,
            status: statusEl.value,
            _csrf: csrf
        });

        fetch('/admin/update-order-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showNotification('Статус заказа обновлён', 'success');
                    document.querySelectorAll('.status-badge').forEach(function (badge) {
                        badge.classList.remove('status-pending', 'status-processing', 'status-completed', 'status-cancelled');
                        badge.classList.add(data.status_class);
                        badge.textContent = data.status_text;
                    });
                    window.closeStatusModal();
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                }
            })
            .catch(function () { showNotification('Ошибка соединения', 'error'); });
    };

    function initOrderDetailPage() {
        if (!document.getElementById('orderId')) return;

        document.querySelectorAll('.modal-close, .modal-btn.cancel').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.closeStatusModal();
            });
        });

        var saveBtn = document.querySelector('.modal-btn.save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.updateStatus();
            });
        }

        if (window.jQuery) {
            window.jQuery(document).on('click', '#statusModal', function (e) {
                if (window.jQuery(e.target).is('#statusModal')) window.closeStatusModal();
            });
        }
    }

    /* --- Reviews moderation --- */
    function postReviewAction(url, reviewId, btn, okLabel) {
        if (!btn) return;
        btn.disabled = true;
        btn.textContent = '⏳';
        var formData = new FormData();
        formData.append('review_id', reviewId);
        formData.append('_csrf', getCsrfToken());

        fetch(url, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showNotification(data.message || 'Готово', 'success');
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    showNotification(data.message || 'Ошибка', 'error');
                    btn.disabled = false;
                    btn.textContent = okLabel;
                }
            })
            .catch(function () {
                showNotification('Ошибка соединения', 'error');
                btn.disabled = false;
                btn.textContent = okLabel;
            });
    }

    window.approveReview = function (reviewId) {
        if (!confirm('Одобрить этот отзыв?')) return;
        postReviewAction('/admin/approve-review', reviewId,
            document.querySelector('.approve-btn[data-id="' + reviewId + '"]'), '✓');
    };

    window.rejectReview = function (reviewId) {
        if (!confirm('Отклонить этот отзыв?')) return;
        postReviewAction('/admin/reject-review', reviewId,
            document.querySelector('.reject-btn[data-id="' + reviewId + '"]'), '✗');
    };

    window.deleteReview = function (reviewId) {
        if (!confirm('Удалить этот отзыв?')) return;
        postReviewAction('/admin/delete-review', reviewId,
            document.querySelector('.delete-btn[data-id="' + reviewId + '"]'), '🗑');
    };
})();
