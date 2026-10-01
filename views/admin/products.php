<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $categories */

$this->title = 'Управление товарами';
?>

<div class="admin-products">
    <!-- Заголовок -->
    <div class="admin-header">
        <h1 class="admin-title"><?= Html::encode($this->title) ?></h1>
        <div class="admin-header-actions">
            <?= Html::a('← Назад', ['index'], ['class' => 'admin-nav-btn']) ?>
            <?= Html::a('Добавить товар', ['/product/create'], ['class' => 'admin-nav-btn success']) ?>
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
                'action' => ['products'],
                'options' => ['class' => 'filters-form'],
            ]); ?>

            <div class="filters-grid">
                <div class="filter-group">
                    <?= $form->field($searchModel, 'name')->textInput([
                        'placeholder' => 'Название товара',
                        'class' => 'filter-input'
                    ])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'category_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map($categories, 'id_category', 'category_name'),
                        ['prompt' => 'Все категории', 'class' => 'filter-input']
                    )->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'is_active')->dropDownList([
                        '' => 'Все статусы',
                        '1' => 'Активные',
                        '0' => 'Неактивные',
                    ], ['class' => 'filter-input'])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'is_discount')->dropDownList([
                        '' => 'Все товары',
                        '1' => 'Со скидкой',
                        '0' => 'Без скидки',
                    ], ['class' => 'filter-input'])->label(false) ?>
                </div>

                <div class="filter-group">
                    <?= $form->field($searchModel, 'quantity')->dropDownList([
                        '' => 'Любое количество',
                        '0' => 'Нет в наличии',
                        '1-5' => 'Мало (1-5)',
                        '5-10' => 'Средне (5-10)',
                        '10' => 'Много (>10)',
                    ], ['class' => 'filter-input'])->label(false) ?>
                </div>
            </div>

            <div class="filters-actions">
                <?= Html::submitButton('Применить фильтры', ['class' => 'filter-btn apply']) ?>
                <?= Html::a('Сбросить', ['products'], ['class' => 'filter-btn reset']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Таблица товаров -->
    <div class="products-card">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'products-table'],
            'layout' => "{items}\n<div class='pagination-wrapper'>{pager}</div>",
            'columns' => [
               
                [
                    'attribute' => 'image_product',
                    'label' => 'Фото',
                    'format' => 'raw',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-image'],
                    'value' => function($model) {
                        return Html::img('/images/' . ($model->image_product ?: 'no-image.jpg'), [
                            'class' => 'product-thumb',
                            'alt' => $model->name
                        ]);
                    },
                    'filter' => false,
                ],
                [
                    'attribute' => 'name',
                    'label' => 'Название',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-name'],
                ],
                [
                    'attribute' => 'category_name',
                    'label' => 'Категория',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-category'],
                    'value' => function($model) {
                        return $model->category ? $model->category->category_name : '-';
                    },
                ],
                [
                    'attribute' => 'price',
                    'label' => 'Цена',
                    'format' => 'raw',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-price'],
                    'value' => function($model) {
                        if ($model->is_discount && $model->old_price) {
                            return Html::tag('span', number_format($model->price, 0, '', ' ') . ' ₽', ['class' => 'price-current']) .
                                   Html::tag('span', number_format($model->old_price, 0, '', ' ') . ' ₽', ['class' => 'price-old']);
                        }
                        return Html::tag('span', number_format($model->price, 0, '', ' ') . ' ₽', ['class' => 'price-regular']);
                    },
                ],
                [
                    'attribute' => 'quantity',
                    'label' => 'Остаток',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-quantity'],
                    'format' => 'raw',
                    'value' => function($model) {
                        $class = 'stock-badge ';
                        if ($model->quantity <= 0) {
                            $class .= 'stock-zero';
                        } elseif ($model->quantity < 5) {
                            $class .= 'stock-low';
                        } elseif ($model->quantity < 10) {
                            $class .= 'stock-medium';
                        } else {
                            $class .= 'stock-high';
                        }
                        
                        return Html::tag('span', $model->quantity, [
                            'class' => $class,
                            'data-id' => $model->id_product,
                            'onclick' => 'showQuantityModal(' . $model->id_product . ', "' . Html::encode($model->name) . '", ' . $model->quantity . ')',
                            'style' => 'cursor: pointer;'
                        ]);
                    },
                ],
                [
                    'attribute' => 'is_active',
                    'label' => 'Статус',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-status'],
                    'format' => 'raw',
                    'value' => function($model) {
                        $class = $model->is_active ? 'status-badge status-active' : 'status-badge status-inactive';
                        $text = $model->is_active ? 'Активен' : 'Неактивен';
                        return Html::tag('span', $text, [
                            'class' => $class,
                            'data-id' => $model->id_product,
                            'onclick' => 'toggleProductStatus(' . $model->id_product . ')',
                            'style' => 'cursor: pointer;'
                        ]);
                    },
                    'filter' => Html::activeDropDownList($searchModel, 'is_active', [
                        '' => 'Все',
                        '1' => 'Активные',
                        '0' => 'Неактивные',
                    ], ['class' => 'filter-input']),
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}',
                    'header' => 'Действия',
                    'headerOptions' => ['class' => 'table-header'],
                    'contentOptions' => ['class' => 'col-actions'],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return Html::a('👁️', ['/product/view', 'id' => $model->id_product], [
                                'class' => 'action-btn view-btn',
                                'title' => 'Просмотр',
                                'target' => '_blank'
                            ]);
                        },
                        'update' => function($url, $model) {
                            return Html::a('✏️', ['update-product', 'id' => $model->id_product], [
                                'class' => 'action-btn edit-btn',
                                'title' => 'Редактировать'
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
        ]); ?>
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
            <p id="modalProductName" class="modal-product-name"></p>
            <div class="form-group">
                <label for="modalQuantity">Количество на складе:</label>
                <input type="number" id="modalQuantity" class="modal-input" min="0" value="0">
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn cancel" onclick="closeQuantityModal()">Отмена</button>
            <button class="modal-btn save" onclick="saveQuantity()">Сохранить</button>
        </div>
    </div>
</div>
