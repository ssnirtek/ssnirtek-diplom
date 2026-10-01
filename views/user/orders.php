<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Мои заказы';
$this->registerCssFile('@web/css/order-page.css');
?>

<div class="orders-page">
    <div class="orders-header">
        <h1 class="orders-title"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['/catalog']) ?>" class="orders-catalog-link">Перейти в каталог</a>
    </div>

    <div class="orders-content">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class='orders-pagination'>{pager}</div>",
            'tableOptions' => ['class' => 'orders-table'],
            'columns' => [
                [
                    'attribute' => 'id_orders',
                    'label' => '№ заказа',
                    'headerOptions' => ['class' => 'orders-table-header' ,
            'style' => 'color: var(--dark);'],
                    'contentOptions' => ['class' => 'orders-table-cell order-number'],
                    'value' => function($model) {
                        return '#' . str_pad($model->id_orders, 6, '0', STR_PAD_LEFT);
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Дата',
                    'headerOptions' => ['class' => 'orders-table-header',
            'style' => 'color: var(--dark);'],
                    'contentOptions' => ['class' => 'orders-table-cell order-date'],
                    'format' => ['datetime', 'php:d.m.Y H:i'],
                ],
                [
                    'attribute' => 'total_amount',
                    'label' => 'Сумма',
                    'headerOptions' => ['class' => 'orders-table-header',
            'style' => 'color: var(--dark);'],
                    'contentOptions' => ['class' => 'orders-table-cell order-amount'],
                    'value' => function($model) {
                        return number_format($model->total_amount, 0, '', ' ') . ' ₽';
                    }
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Статус',
                    'headerOptions' => ['class' => 'orders-table-header',
            'style' => 'color: var(--dark);'],
                    'contentOptions' => ['class' => 'orders-table-cell order-status'],
                    'format' => 'raw',
                    'value' => fn($model) => Html::tag(
                        'span',
                        $model->getStatusLabel(),
                        ['class' => 'order-status-badge ' . $model->getStatusClass()]
                    ),
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'header' => 'Действия',
                    'headerOptions' => ['class' => 'orders-table-header'],
                    'contentOptions' => ['class' => 'orders-table-cell order-actions'],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return Html::a('Просмотреть', ['order-view', 'id' => $model->id_orders], [
                                'class' => 'order-view-link',
                                'title' => 'Просмотреть детали заказа'
                            ]);
                        }
                    ],
                ],
            ],
            'summary' => '<div class="orders-summary">Показано <span>{begin}</span> - <span>{end}</span> из <span>{totalCount}</span> заказов</div>',
            'emptyText' => '<div class="orders-empty">
                <p>У вас пока нет заказов</p>
                <a href="' . Url::to(['/catalog']) . '" class="orders-empty-btn">Перейти в каталог</a>
            </div>',
            'emptyTextOptions' => ['class' => 'orders-empty-container'],
        ]); ?>
    </div>
</div>