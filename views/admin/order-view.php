<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Orders $order */
/** @var array $items */

$this->title = 'Заказ #' . $order->id_orders;
$this->registerCsrfMetaTags();
?>

<div class="admin-order-view">
    <!-- Скрытое поле с ID заказа -->
    <input type="hidden" id="orderId" value="<?= $order->id_orders ?>">
    
    <!-- Заголовок -->
    <div class="order-header">
        <div class="header-left">
            <h1 class="order-title"><?= Html::encode($this->title) ?></h1>
            <div class="order-status-wrapper">
                <span class="status-badge <?= $order->getStatusClass() ?>" id="currentStatusBadge">
                    <?= $order->getStatusLabel() ?>
                </span>
            </div>
        </div>
        <div class="header-actions">
            <?= Html::a('← Назад к заказам', ['orders'], ['class' => 'back-btn']) ?>
            <button class="edit-status-btn" onclick="showStatusModal()">
                Изменить статус
            </button>
        </div>
    </div>

    <!-- Информация о заказе -->
    <div class="order-info-grid">
        <!-- Основная информация -->
        <div class="info-card">
            <h3>Основная информация</h3>
            <div class="info-row">
                <span class="info-label">Номер заказа:</span>
                <span class="info-value">#<?= $order->id_orders ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата заказа:</span>
                <span class="info-value"><?= Yii::$app->formatter->asDatetime($order->created_at, 'php:d.m.Y H:i') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Статус:</span>
                <span class="info-value">
                    <span class="status-badge <?= $order->getStatusClass() ?>">
                        <?= $order->getStatusLabel() ?>
                    </span>
                </span>
            </div>
            <?php if ($order->hasAttribute('subtotal_amount')): ?>
            <div class="info-row">
                <span class="info-label">Сумма товаров:</span>
                <span class="info-value"><?= number_format($order->subtotal_amount, 0, '', ' ') ?> ₽</span>
            </div>
            <?php if ($order->hasAttribute('welcome_discount_amount') && (float) $order->welcome_discount_amount > 0): ?>
            <div class="info-row">
                <span class="info-label">Скидка <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>% (первые заказы):</span>
                <span class="info-value" style="color:#2e7d32;">− <?= number_format($order->welcome_discount_amount, 0, '', ' ') ?> ₽</span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">К оплате:</span>
                <span class="info-value total-amount"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</span>
            </div>
            <?php else: ?>
            <div class="info-row">
                <span class="info-label">Сумма заказа:</span>
                <span class="info-value total-amount"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Информация о клиенте -->
        <div class="info-card">
            <h3>Информация о клиенте</h3>
            <div class="info-row">
                <span class="info-label">ФИО:</span>
                <span class="info-value"><?= Html::encode($order->full_name ?: 'Не указано') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Телефон:</span>
                <span class="info-value">
                    <?php if ($order->phone): ?>
                        <a href="tel:<?= Html::encode($order->phone) ?>" class="contact-link">
                            <?= Html::encode($order->phone) ?>
                        </a>
                    <?php else: ?>
                        Не указано
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">
                    <?php if ($order->email): ?>
                        <a href="mailto:<?= Html::encode($order->email) ?>" class="contact-link">
                            <?= Html::encode($order->email) ?>
                        </a>
                    <?php else: ?>
                        Не указано
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">ID пользователя:</span>
                <span class="info-value"><?= $order->user_id ?></span>
            </div>
        </div>

        <!-- Адрес доставки -->
        <div class="info-card full-width">
            <h3>Адрес доставки</h3>
            <div class="info-row">
                <span class="info-label">Адрес:</span>
                <span class="info-value address-text"><?= Html::encode($order->address ?: 'Не указан') ?></span>
            </div>
            <?php if ($order->comment): ?>
            <div class="info-row">
                <span class="info-label">Комментарий:</span>
                <span class="info-value comment-text"><?= nl2br(Html::encode($order->comment)) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Состав заказа -->
    <div class="order-items-card">
        <h3>Состав заказа</h3>
        
        <div class="items-table-wrapper">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php $product = $item->product; ?>
                        <tr>
                            <td class="item-image">
                                <?php if ($product && $product->image_product): ?>
                                    <?= Html::img('/images/' . $product->image_product, [
                                        'alt' => $product->name,
                                        'class' => 'product-thumb'
                                    ]) ?>
                                <?php else: ?>
                                    <div class="no-image">📷</div>
                                <?php endif; ?>
                            </td>
                            <td class="item-name">
                                <?php if ($product): ?>
                                    <?= Html::encode($product->name) ?>
                                    <br>
                                    <small class="product-id">ID: <?= $item->product_id ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Товар удален</span>
                                    <br>
                                    <small class="product-id">ID: <?= $item->product_id ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="item-price"><?= number_format($item->price, 0, '', ' ') ?> ₽</td>
                            <td class="item-quantity"><?= $item->quantity ?> шт.</td>
                            <td class="item-total"><?= number_format($item->price * $item->quantity, 0, '', ' ') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="total-label">Итого:</td>
                        <td class="total-value"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
<!-- Модальное окно для изменения статуса -->
<div class="modal" id="statusModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Изменить статус заказа</h3>
            <button class="modal-close">×</button> <!-- Убираем onclick -->
        </div>
        <div class="modal-body">
            <p class="modal-order-info">Заказ #<?= $order->id_orders ?></p>
            <div class="form-group">
                <label for="orderStatus">Выберите новый статус:</label>
                <select id="orderStatus" class="status-select">
                    <?php foreach (\app\models\Orders::getStatusOptions() as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $order->status == $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel">Отмена</button> <!-- Убираем onclick -->
            <button class="modal-btn save">Сохранить</button> <!-- Убираем onclick -->
        </div>
    </div>
</div>

<!-- Уведомление -->
<div id="notification" class="notification" style="display: none;"></div>