<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ReviewSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Модерация отзывов';
?>

<div class="admin-reviews">
    <!-- Заголовок -->
    <div class="admin-header">
        <h1 class="admin-title"><?= Html::encode($this->title) ?></h1>
        <div class="admin-header-actions">
            <?= Html::a('← Назад', ['index'], ['class' => 'admin-nav-btn']) ?>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="filters-card">
        <div class="filters-header">
            <h3>Фильтры</h3>
            <span class="filters-toggle">▼</span>
        </div>
        <div class="filters-content">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['reviews'],
                'options' => ['class' => 'filters-form'],
            ]); ?>

            <div class="filters-grid">
                <div class="filter-group">
                    <?= $form->field($searchModel, 'product_name')->textInput([
                        'placeholder' => 'Название товара',
                        'class' => 'filter-input'
                    ])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'user_name')->textInput([
                        'placeholder' => 'Имя пользователя',
                        'class' => 'filter-input'
                    ])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'rating')->dropDownList([
                        '' => 'Любая оценка',
                        '5' => '5 ★',
                        '4' => '4 ★',
                        '3' => '3 ★',
                        '2' => '2 ★',
                        '1' => '1 ★',
                    ], ['class' => 'filter-input'])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'is_approved')->dropDownList([
                        '' => 'Все статусы',
                        '1' => 'Одобренные',
                        '0' => 'На модерации',
                    ], ['class' => 'filter-input'])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'created_at')->input('date', [
                        'class' => 'filter-input',
                        'placeholder' => 'Дата'
                    ])->label(false) ?>
                </div>
            </div>

            <div class="filters-actions">
                <?= Html::submitButton('Применить фильтры', ['class' => 'filter-btn apply']) ?>
                <?= Html::a('Сбросить', ['reviews'], ['class' => 'filter-btn reset']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Таблица отзывов -->
    <div class="reviews-card">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'reviews-table'],
            'layout' => "{items}\n<div class='pagination-wrapper'>{pager}</div>",
            'columns' => [
                [
                    'attribute' => 'id_review',
                    'label' => 'ID',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-id'],
                ],
                [
                    'attribute' => 'product_name',
                    'label' => 'Товар',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-product'],
                    'value' => function($model) {
                        return $model->product ? Html::encode($model->product->name) : '<span class="text-muted">Товар удален</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'user_name',
                    'label' => 'Пользователь',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-user'],
                    'value' => function($model) {
                        return $model->user ? Html::encode($model->user->full_name) : '<span class="text-muted">Пользователь удален</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'rating',
                    'label' => 'Оценка',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-rating'],
                    'format' => 'raw',
                    'value' => function($model) {
                        $stars = '';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $model->rating) {
                                $stars .= '★';
                            } else {
                                $stars .= '☆';
                            }
                        }
                        return Html::tag('span', $stars, ['class' => 'rating-stars']);
                    },
                ],
                [
                    'attribute' => 'text',
                    'label' => 'Отзыв',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-text'],
                    'value' => function($model) {
                        return Html::encode($model->text);
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Дата',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-date'],
                    'format' => ['datetime', 'php:d.m.Y H:i'],
                ],
                [
                    'attribute' => 'is_approved',
                    'label' => 'Статус',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-status'],
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->is_approved) {
                            return Html::tag('span', 'Одобрен', [
                                'class' => 'status-badge status-approved'
                            ]);
                        } else {
                            return Html::tag('span', 'На модерации', [
                                'class' => 'status-badge status-pending'
                            ]);
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{approve} {reject} {delete}',
                    'header' => 'Действия',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-actions'],
                    'buttons' => [
                        'approve' => function($url, $model) {
                            if (!$model->is_approved) {
                                return Html::button('✓', [
                                    'class' => 'action-btn approve-btn',
                                    'data-id' => $model->id_review,
                                    'title' => 'Одобрить отзыв',
                                    'onclick' => 'approveReview(' . $model->id_review . ')'
                                ]);
                            }
                            return '';
                        },
                        'reject' => function($url, $model) {
                            if ($model->is_approved) {
                                return Html::button('✗', [
                                    'class' => 'action-btn reject-btn',
                                    'data-id' => $model->id_review,
                                    'title' => 'Отклонить отзыв',
                                    'onclick' => 'rejectReview(' . $model->id_review . ')'
                                ]);
                            }
                            return '';
                        },
                        'delete' => function($url, $model) {
                            return Html::button('🗑', [
                                'class' => 'action-btn delete-btn',
                                'data-id' => $model->id_review,
                                'title' => 'Удалить отзыв',
                                'onclick' => 'deleteReview(' . $model->id_review . ')'
                            ]);
                        },
                    ],
                ],
            ],
            'pager' => [
                'class' => \yii\widgets\LinkPager::class,
                'options' => ['class' => 'pagination'],
                'linkOptions' => ['class' => 'page-link'],
                'activePageCssClass' => 'active',
                'prevPageLabel' => '‹',
                'nextPageLabel' => '›',
            ],

            'rowOptions' => function ($model) {
    return [
        'data-review-id' => $model->id_review,
        'class' => 'review-row'
    ];
},
        ]); ?>
    </div>
</div>

<!-- Уведомление -->
<div id="notification" class="notification" style="display: none;"></div>