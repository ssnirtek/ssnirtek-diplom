<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var app\models\Category[] $categories */

$this->title = 'Добавление товара';
$this->registerCssFile('@web/css/admin-pages.css');

// Регистрируем jQuery сначала
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', [
    'position' => \yii\web\View::POS_HEAD
]);

// Затем регистрируем ваш JS файл с зависимостью от jQuery
$this->registerJsFile('@web/js/admin.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END
]);
?>

<div class="admin-products">
    <div class="admin-header">
        <h1 class="admin-title"><?= Html::encode($this->title) ?></h1>
        <div class="admin-header-actions">
            <?= Html::a('← Назад к товарам', ['/admin/products'], ['class' => 'admin-nav-btn']) ?>
        </div>
    </div>

    <div class="products-card">
        <?php $form = ActiveForm::begin([
            'options' => [
                'enctype' => 'multipart/form-data', 
                'class' => 'product-form',
                'id' => 'product-form',
            ],
        ]); ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Основная информация -->
                <div class="form-section">
                    <h3>Основная информация</h3>
                    
                    <div class="form-group">
                        <?= $form->field($model, 'name')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Введите название товара',
                            'class' => 'form-control',
                            'autofocus' => true
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 6,
                            'placeholder' => 'Подробное описание товара',
                            'class' => 'form-control'
                        ]) ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'price')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => 0,
                                    'class' => 'form-control',
                                    'placeholder' => '0.00'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'quantity')->textInput([
                                    'type' => 'number',
                                    'min' => 0,
                                    'class' => 'form-control',
                                    'placeholder' => '0'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'category_id')->dropDownList(
                                    ArrayHelper::map($categories, 'id_category', 'category_name'),
                                    [
                                        'prompt' => 'Выберите категорию',
                                        'class' => 'form-control'
                                    ]
                                ) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'is_active')->checkbox([
                                    'class' => 'form-check-input',
                                    'checked' => true
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Скидки -->
                <div class="form-section">
                    <h3>Скидки</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'is_discount')->checkbox([
                                    'class' => 'form-check-input',
                                    'id' => 'is_discount_checkbox'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6" id="old_price_field" style="display: none;">
                            <div class="form-group">
                                <?= $form->field($model, 'old_price')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => 0,
                                    'class' => 'form-control',
                                    'placeholder' => 'Старая цена (должна быть больше текущей)'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Изображение -->
                <div class="form-section">
                    <h3>Изображение</h3>
                    
                    <div class="form-group">
                        <?= $form->field($model, 'imageFile')->fileInput([
                            'class' => 'form-control',
                            'accept' => 'image/*',
                            'id' => 'image_file_input'
                        ]) ?>
                        <small class="text-muted">Рекомендуемый размер: 800x800px. Допустимые форматы: JPG, PNG, WEBP</small>
                    </div>

                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <p>Предпросмотр:</p>
                        <img src="" alt="Preview" class="product-preview" id="preview_img">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <?= Html::submitButton('Создать товар', ['class' => 'btn-save', 'id' => 'submit-button']) ?>
            <?= Html::a('Отмена', ['/admin/products'], ['class' => 'btn-cancel']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

