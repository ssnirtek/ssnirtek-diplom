<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Orders $order */
/** @var app\models\OrderItem[] $items */

$this->title = 'Заказ #' . str_pad($order->id_orders, 6, '0', STR_PAD_LEFT);
$this->registerCssFile('@web/css/user-order.css');
?>

<div class="order-view">
    <div class="row">
        <!-- Боковое меню -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Меню</h5>
                </div>
                <div class="list-group">
                    <?= Html::a('Личный кабинет', ['profile'], ['class' => 'list-group-item']) ?>
                    <?= Html::a('Мои заказы', ['orders'], ['class' => 'list-group-item active']) ?>
                    <?= Html::a('Новый заказ', ['/catalog'], ['class' => 'list-group-item bg-success']) ?>
                </div>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="col-md-9">
            <!-- Карточка заказа -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><?= Html::encode($this->title) ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Детали заказа -->
                        <div class="col-md-6">
                            <div class="detail-view-container">
                                <h6>Детали заказа</h6>
                                <table class="detail-view">
                                    <tr>
                                        <th>Номер заказа</th>
                                        <td><strong>#<?= str_pad($order->id_orders, 6, '0', STR_PAD_LEFT) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Дата заказа</th>
                                        <td><?= Yii::$app->formatter->asDatetime($order->created_at, 'php:d.m.Y H:i') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Статус</th>
                                        <td>
                                            <span class="status-badge <?= $order->getStatusClass() ?>">
                                                <?= Html::encode($order->getStatusLabel()) ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Информация о получателе -->
                        <div class="col-md-6">
                            <div class="detail-view-container">
                                <h6>Получатель</h6>
                                <table class="detail-view">
                                    <?php if ($order->full_name): ?>
                                    <tr>
                                        <th>ФИО</th>
                                        <td><?= Html::encode($order->full_name) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order->phone): ?>
                                    <tr>
                                        <th>Телефон</th>
                                        <td><?= Html::encode($order->phone) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order->email): ?>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= Html::encode($order->email) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order->address): ?>
                                    <tr>
                                        <th>Адрес доставки</th>
                                        <td><?= Html::encode($order->address) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Сумма заказа -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="detail-view-container">
                                <h6>Итого</h6>
                                <table class="detail-view">
                                    <?php if ($order->hasAttribute('welcome_discount_amount') && (float) $order->welcome_discount_amount > 0): ?>
                                    <tr>
                                        <th>Сумма товаров</th>
                                        <td><?= number_format($order->subtotal_amount, 0, '', ' ') ?> ₽</td>
                                    </tr>
                                    <tr>
                                        <th>Скидка <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>% (первые заказы)</th>
                                        <td class="text-success">− <?= number_format($order->welcome_discount_amount, 0, '', ' ') ?> ₽</td>
                                    </tr>
                                    <tr>
                                        <th>К оплате</th>
                                        <td class="total-amount"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</td>
                                    </tr>
                                    <?php else: ?>
                                    <tr>
                                        <th>Сумма заказа</th>
                                        <td class="total-amount"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Товары в заказе -->
            <?php if (isset($items) && !empty($items)): ?>
            <div class="products-section">
                <h5 class="products-title">Состав заказа</h5>
                
                <div class="table-responsive">
                    <table class="products-table">
                        <thead>
                            <tr>
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
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div class="product-image">
                                                <?= Html::img('/images/' . ($product->image_product ?: 'no-image.jpg'), [
                                                    'alt' => $product->name
                                                ]) ?>
                                            </div>
                                            <span class="product-name"><?= Html::encode($product->name) ?></span>
                                        </div>
                                    </td>
                                    <td class="product-price"><?= number_format($item->price, 0, '', ' ') ?> ₽</td>
                                    <td><?= $item->quantity ?> шт.</td>
                                    <td class="product-total"><?= number_format($item->price * $item->quantity, 0, '', ' ') ?> ₽</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="order-summary">
                    <?php if ($order->hasAttribute('welcome_discount_amount') && (float) $order->welcome_discount_amount > 0): ?>
                    <div class="order-summary-line">
                        <span>Сумма товаров</span>
                        <span><?= number_format($order->subtotal_amount, 0, '', ' ') ?> ₽</span>
                    </div>
                    <div class="order-summary-line order-summary-discount">
                        <span>Скидка <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>%</span>
                        <span>− <?= number_format($order->welcome_discount_amount, 0, '', ' ') ?> ₽</span>
                    </div>
                    <?php endif; ?>
                    <span class="total-label">К оплате:</span>
                    <span class="total-value"><?= number_format($order->total_amount, 0, '', ' ') ?> ₽</span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Кнопки действий -->
            <div class="text-center">
                <?= Html::a('← Вернуться к заказам', ['orders'], ['class' => 'btn btn-secondary']) ?>
                <?= Html::a('🛒 Новый заказ', ['/catalog'], ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>
</div>