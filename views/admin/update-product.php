<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Category;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = 'Редактирование товара: ' . $model->name;
?>


<div class="admin-products">
    <div class="admin-header">
        <h1 class="admin-title"><?= Html::encode($this->title) ?></h1>
        <div class="admin-header-actions">
            <?= Html::a('← Назад к товарам', ['products'], ['class' => 'admin-nav-btn']) ?>
        </div>
    </div>

    <div class="products-card">
        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data', 'class' => 'product-form'],
        ]); ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Основная информация -->
                <div class="form-section">
                    <h3>Основная информация</h3>
                    
                    <div class="form-group">
                        <?= $form->field($model, 'name')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Название товара',
                            'class' => 'form-control'
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 6,
                            'placeholder' => 'Описание товара',
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
                                    'class' => 'form-control'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'quantity')->textInput([
                                    'type' => 'number',
                                    'min' => 0,
                                    'class' => 'form-control'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'category_id')->dropDownList(
                                    ArrayHelper::map(Category::find()->all(), 'id_category', 'category_name'),
                                    ['prompt' => 'Выберите категорию', 'class' => 'form-control']
                                ) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->field($model, 'is_active')->checkbox([
                                    'class' => 'form-check-input'
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
                                ])->label('Товар со скидкой') ?>
                            </div>
                        </div>
                        <div class="col-md-6" id="old_price_field">
                            <div class="form-group">
                                <?= $form->field($model, 'old_price')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => 0,
                                    'class' => 'form-control',
                                    'placeholder' => 'Старая цена'
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
                    
                    <div class="current-image">
                        <?php if ($model->image_product): ?>
                            <p>Текущее изображение:</p>
                            <?= Html::img('/images/' . $model->image_product, [
                                'class' => 'product-preview',
                                'alt' => $model->name
                            ]) ?>
                            <br>
                            <label class="checkbox-inline">
                                <?= Html::checkbox('remove_image', false) ?> Удалить текущее изображение
                            </label>
                        <?php else: ?>
                            <p>Изображение не загружено</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <?= $form->field($model, 'imageFile')->fileInput([
                            'class' => 'form-control',
                            'accept' => 'image/*'
                        ])->label('Загрузить новое изображение') ?>
                        <small class="text-muted">Рекомендуемый размер: 800x800px</small>
                    </div>
                </div>

                <!-- Акции (если есть связь) -->
                <?php if (method_exists($model, 'getPromos')): ?>
                <div class="form-section">
                    <h3>Акции</h3>
                    <div class="form-group">
                        <?= $form->field($model, 'promo_ids')->checkboxList(
                            ArrayHelper::map(\app\models\Promo::find()->all(), 'id_promo', 'title'),
                            [
                                'class' => 'checkbox-list',
                                'separator' => '<br>',
                            ]
                        )->label('Участвует в акциях') ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <?= Html::submitButton('Сохранить изменения', ['class' => 'btn-save']) ?>
            <?= Html::a('Отмена', ['products'], ['class' => 'btn-cancel']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

