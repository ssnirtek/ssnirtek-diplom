<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Управление акциями';
$this->registerJsFile('@web/js/admin.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="admin-promo-index">
    <div class="card">
        <div class="card-header bg-primary1 text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
            <?= Html::a('<i class="fas fa-plus"></i> Создать акцию', ['create'], ['class' => 'btn btn-primary2']) ?>
        </div>
        <div class="card-body">
            
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>
            
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'image_promo', 
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model->image_promo ? Html::img($model->image_promo, ['style' => 'width: 60px; height: 60px; object-fit: cover; border-radius: 5px;']) : '';
                        }
                    ],
                    
                    'title',
                    
                    [
                        'attribute' => 'description',
                        'format' => 'ntext',
                        'contentOptions' => ['style' => 'max-width: 300px;'],
                    ],
                    
                    [
                        'attribute' => 'is_active',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::a(
                                $model->is_active ? '<span class="badge bg-success">Активно</span>' : '<span class="badge bg-secondary">Неактивно</span>',
                                ['toggle-active', 'id_promo' => $model->id_promo], // ИСПРАВЛЕНО: id_promo вместо id
                                [
                                    'class' => 'toggle-active-link',
                                    'data-method' => 'post',
                                ]
                            );
                        }
                    ],
                    
                    'sort_order',
                   [
    'class' => ActionColumn::class,
    'template' => '{update} {delete}',
    'header' => 'Действия',
    'headerOptions' => ['style' => 'text-align: center;'],
    'contentOptions' => ['style' => 'text-align: center;'],
    'buttons' => [
        'update' => function($url, $model) {
            return Html::a('Редактировать', ['update', 'id_promo' => $model->id_promo], [
                'class' => 'btn-action btn-edit',
                'title' => 'Редактировать акцию'
            ]);
        },
        'delete' => function($url, $model) {
            return Html::a('Удалить', ['delete', 'id_promo' => $model->id_promo], [
                'class' => 'btn-action btn-delete',
                'title' => 'Удалить акцию',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить эту акцию?',
                    'method' => 'post',
                ]
            ]);
        },
    ],
],
                ],
            ]); ?>
            
        </div>
    </div>
</div>