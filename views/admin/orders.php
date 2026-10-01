<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Управление заказами';
?>

<div class="admin-orders">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('← К панели управления', ['index'], ['class' => 'back-link']) ?>
    </div>

    <div class="orders-container">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{pager}",
            'tableOptions' => ['class' => 'orders-table'],
            'pager' => [
                'class' => 'yii\bootstrap5\LinkPager',
                'options' => ['class' => 'pagination'],
            ],
            'columns' => [
                [
                    'attribute' => 'id_orders',
                    'label' => '№',
                    'headerOptions' => ['class' => 'col-id'],
                    'contentOptions' => ['class' => 'col-id'],
                ],
                [
                    'attribute' => 'full_name',
                    'label' => 'Клиент',
                    'value' => fn($model) => $model->full_name ?: '—',
                ],
                [
                    'attribute' => 'phone',
                    'label' => 'Телефон',
                    'value' => fn($model) => $model->phone ?: '—',
                ],
                [
                    'attribute' => 'total_amount',
                    'label' => 'Сумма',
                    'format' => 'currency',
                    'headerOptions' => ['class' => 'col-amount'],
                    'contentOptions' => ['class' => 'col-amount'],
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Статус',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::tag('span', $model->getStatusLabel(), [
                            'class' => 'status-badge ' . $model->getStatusClass(),
                            'data-id' => $model->id_orders,
                            'data-status' => $model->status,
                            'onclick' => 'showStatusModal(' . $model->id_orders . ', \'' . $model->status . '\')',
                        ]);
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Дата',
                    'format' => ['datetime', 'php:d.m.Y H:i'],
                    'headerOptions' => ['class' => 'col-date'],
                    'contentOptions' => ['class' => 'col-date'],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'headerOptions' => ['class' => 'col-action'],
                    'contentOptions' => ['class' => 'col-action'],
                    'buttons' => [
                        'view' => fn($url, $model) => Html::a('Просмотр', ['order-view', 'id' => $model->id_orders], [
                            'class' => 'view-btn'
                        ]),
                    ],
                ],
            ],
        ]) ?>
    </div>
</div>

<!-- Модальное окно -->
<div class="modal-overlay" id="statusModal">
    <div class="modal-window">
        <div class="modal-header">
            <h3>Изменение статуса</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="order-info">Заказ №<span id="modalOrderId"></span></p>
            <div class="form-field">
                <label for="modalStatus">Новый статус</label>
                <select id="modalStatus" class="status-select">
                    <option value="pending">Ожидает</option>
                    <option value="processing">В обработке</option>
                    <option value="completed">Выполнен</option>
                    <option value="cancelled">Отменен</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeStatusModal()">Отмена</button>
            <button class="btn-save" onclick="saveStatus()">Сохранить</button>
        </div>
    </div>
</div>