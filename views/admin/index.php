<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var array $stats */
/** @var yii\data\ActiveDataProvider $recentOrders */
/** @var yii\data\ActiveDataProvider $lowStockProducts */

$this->title = 'Панель управления';

?>

<div class="admin-panel">

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['orders_today'] ?></div>
                <div class="stat-label">Заказов сегодня</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['orders_total'] ?></div>
                <div class="stat-label">Всего заказов</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['products_total'] ?></div>
                <div class="stat-label">Товаров</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['products_low_stock'] ?></div>
                <div class="stat-label">Мало на складе</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['users_total'] ?></div>
                <div class="stat-label">Пользователей</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-value"><?= $stats['promo_active'] ?></div>
                <div class="stat-label">Активных акций</div>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <!-- Последние заказы -->
        <div class="admin-card">
            <div class="card-header">
                <h2>Последние заказы</h2>
                <?= Html::a('Все заказы →', ['orders'], ['class' => 'card-link']) ?>
            </div>
            <div class="card-content">
                <?= GridView::widget([
                    'dataProvider' => $recentOrders,
                    'layout' => "{items}",
                    'tableOptions' => ['class' => 'admin-table'],
                    'columns' => [
                        [
                            'attribute' => 'id_orders',
                            'label' => '№',
                            'contentOptions' => ['class' => 'order-id'],
                        ],
                        [
                            'attribute' => 'full_name',
                            'label' => 'Клиент',
                            'value' => function($model) {
                                return Html::encode($model->full_name ?: 'Не указано');
                            }
                        ],
                        [
                            'attribute' => 'total_amount',
                            'label' => 'Сумма',
                            'format' => 'currency',
                            'contentOptions' => ['class' => 'order-amount'],
                        ],
                        [
                            'attribute' => 'status',
                            'label' => 'Статус',
                            'format' => 'raw',
                            'value' => fn($model) => Html::tag(
                                'span',
                                $model->getStatusLabel(),
                                ['class' => 'status-badge ' . $model->getStatusClass()]
                            ),
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => 'Дата',
                            'format' => ['datetime', 'php:d.m.Y H:i'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function($url, $model) {
                                    return Html::a('Просмотр', ['order-view', 'id' => $model->id_orders], [
                                        'class' => 'table-btn view-btn'
                                    ]);
                                }
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

        <!-- Товары с малым остатком -->
        <div class="admin-card">
            <div class="card-header">
                <h2>Товары с малым остатком</h2>
                <?= Html::a('Все товары →', ['products'], ['class' => 'card-link']) ?>
            </div>
            <div class="card-content">
                <?= GridView::widget([
                    'dataProvider' => $lowStockProducts,
                    'layout' => "{items}",
                    'tableOptions' => ['class' => 'admin-table'],
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'label' => 'Товар',
                            'value' => function($model) {
                                return Html::encode($model->name);
                            }
                        ],
                        [
                            'attribute' => 'quantity',
                            'label' => 'Остаток',
                            'format' => 'raw',
                            'value' => function($model) {
                                $class = $model->quantity <= 0 ? 'stock-zero' : 
                                        ($model->quantity < 3 ? 'stock-low' : 'stock-medium');
                                return Html::tag('span', $model->quantity, ['class' => 'stock-badge ' . $class]);
                            }
                        ],
                        [
                            'attribute' => 'price',
                            'label' => 'Цена',
                            'format' => 'currency',
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'buttons' => [
                                'update' => function($url, $model) {
                                    return Html::button('Изменить', [
                                        'class' => 'table-btn edit-btn',
                                        'onclick' => 'showQuantityModal(' . $model->id_product . ', "' . Html::encode($model->name) . '", ' . $model->quantity . ')'
                                    ]);
                                }
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для изменения количества -->
<div class="modal" id="quantityModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Изменить количество товара</h3>
            <button class="modal-close" onclick="closeQuantityModal()">×</button>
        </div>
        <div class="modal-body">
            <p id="modalProductName"></p>
            <div class="form-group">
                <label>Количество на складе:</label>
                <input type="number" id="modalQuantity" class="modal-input" min="0" value="0">
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeQuantityModal()">Отмена</button>
            <button class="modal-btn save" onclick="saveQuantity()">Сохранить</button>
        </div>
    </div>
</div>