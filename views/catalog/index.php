<?php

/** @var app\models\Promo[] $promos */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\assets\AppAsset;

$categories = ArrayHelper::map($categories, 'id_category', 'category_name');

$this->registerCssFile('@web/css/catalog-page.css');
$this->registerJsFile('@web/js/catalog-pagesize.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('@web/js/catalog.js', ['depends' => [AppAsset::class]]);
?>

<div class="catalog-minimal">
    
    <!-- Промо-блок (бегущая строка) -->
    <div class="promo-banner1">
        <div class="promo-scroll1">
            <div class="promo-track1">
                <?php for ($i = 0; $i < 15; $i++): ?>
                    <span class="promo-text1">Украшения созданные с любовью</span>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <!-- ФИЛЬТР + СОРТИРОВКА -->
    <div class="filter-minimal">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['class' => 'filter-minimal-form', 'id' => 'filter-form'],
        ]); ?>

        <!-- Категории -->
        <?= Html::dropDownList(
            'category_id',
            Yii::$app->request->get('category_id'),
            $categories,
            [
                'prompt' => 'Все категории',
                'class' => 'filter-minimal-input',
                'id' => 'filter-category',
            ]
        ) ?>

        <!-- Сортировка (выпадающий список) -->
        <?= Html::dropDownList(
            'sort',
            Yii::$app->request->get('sort'),
            [
                'created_at_desc' => 'По новизне ↓',
                'created_at_asc'  => 'По новизне ↑',
                'price_asc'       => 'Цена ↑',
                'price_desc'      => 'Цена ↓',
                'name_asc'        => 'А-Я',
                'name_desc'       => 'Я-А',
            ],
            [
                'prompt' => 'Сортировка',
                'class' => 'filter-minimal-input',
                'id' => 'filter-sort',
            ]
        ) ?>

        <!-- Чекбокс "Только со скидкой" -->
        <label class="filter-minimal-checkbox">
            <input type="checkbox"
                   name="discount_only"
                   value="1"
                   id="filter-discount"
                   <?= Yii::$app->request->get('discount_only') ? 'checked' : '' ?>>
            <span>Только со скидкой</span>
        </label>

        <!-- Кнопки -->
        <div class="filter-minimal-actions">
            <?= Html::a('Сбросить', ['index'], ['class' => 'filter-minimal-reset']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    

    <!-- КОНТЕЙНЕР ДЛЯ ТОВАРОВ -->
    <div id="products-container">
        <?= $this->render('_products_grid', ['dataProvider' => $dataProvider]) ?>
    </div>

    <!-- Блок со всеми активными акциями -->
    <div class="last-promo-section" aria-labelledby="catalog-promos-heading">
        <div class="last-promo-container last-promo-container--multi">
            <?php if (!empty($promos)): ?>
                <header class="last-promo-head">
                    <h2 id="catalog-promos-heading" class="last-promo-head__title">Акции и спецпредложения</h2>
                    <p class="last-promo-head__lead">Условия участия — ниже; товары по акции отмечены в каталоге</p>
                </header>
                <?= $this->render('@app/views/site/_promo_cards', ['promos' => $promos]) ?>
                
            <?php else: ?>
                <div class="last-promo-placeholder">
                    <div class="placeholder-content">
                        <h3>Акции скоро появятся!</h3>
                        <p>Следите за обновлениями</p>
                        <a href="<?= Url::to(['/catalog/index']) ?>" class="cta-button">Перейти в каталог →</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

