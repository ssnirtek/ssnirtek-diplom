<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Promo $model */
/** @var yii\widgets\ActiveForm $form */
$this->registerJsFile('@web/js/admin.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="promo-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary1 text-white">
                    <h5 class="mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Название акции -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'title')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Введите название акции',
                            'class' => 'form-control'
                        ]) ?>
                    </div>

                    <!-- Описание акции -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 5,
                            'placeholder' => 'Подробное описание акции',
                            'class' => 'form-control'
                        ]) ?>
                    </div>

                    <!-- Поле для скидки (новое) -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'discount_percent')->widget(MaskedInput::class, [
                            'mask' => '9{1,3}',
                            'options' => [
                                'class' => 'form-control',
                                'placeholder' => 'Например: 15'
                            ]
                        ])->label('Скидка (%)') ?>
                        <small class="text-muted">Укажите размер скидки в процентах (0-100)</small>
                    </div>

                    <!-- Условия акции (новое) -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'conditions')->textarea([
                            'rows' => 3,
                            'placeholder' => 'Условия участия в акции',
                            'class' => 'form-control'
                        ])->label('Условия акции') ?>
                    </div>

                    <!-- Дата начала и окончания (новое) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <?= $form->field($model, 'start_date')->input('date', [
                                    'class' => 'form-control'
                                ])->label('Дата начала') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <?= $form->field($model, 'end_date')->input('date', [
                                    'class' => 'form-control'
                                ])->label('Дата окончания') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Сортировка -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'sort_order')->textInput([
                            'type' => 'number',
                            'min' => 0,
                            'class' => 'form-control'
                        ])->label('Порядок сортировки') ?>
                        <small class="text-muted">Чем меньше число, тем выше позиция</small>
                    </div>

                    <!-- Активность -->
                    <div class="form-group mb-3">
                        <?= $form->field($model, 'is_active')->checkbox([
                            'class' => 'form-check-input'
                        ])->label('Активно') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Загрузка изображения -->
            <div class="card mb-3">
                <div class="card-header bg-primary1 text-white">
                    <h5 class="mb-0">Изображение акции</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($model->image_promo && !$model->isNewRecord): ?>
                        <div class="current-image mb-3">
                            <p class="text-muted mb-2">Текущее изображение:</p>
                            <?= Html::img($model->image_promo, [
                                'style' => 'max-width: 100%; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <?= $form->field($model, 'imageFile')->fileInput([
                            'class' => 'form-control',
                            'accept' => 'image/*'
                        ])->label('Загрузить новое изображение') ?>
                        <small class="text-muted">Рекомендуемый размер: 800x400px</small>
                    </div>
                </div>
            </div>

            <!-- Применяемые товары (опционально) -->
            <div class="card">
                <div class="card-header bg-primary1 text-white">
                    <h5 class="mb-0">Товары по акции</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <?= $form->field($model, 'product_ids')->checkboxList(
                            \yii\helpers\ArrayHelper::map(\app\models\Product::find()->all(), 'id_product', 'name'),
                            [
                                'class' => 'product-checkbox-list',
                                'style' => 'max-height: 300px; overflow-y: auto; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'
                            ]
                        )->label('Выберите товары, участвующие в акции') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4 text-center">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary1 btn-lg px-5']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary btn-lg px-5']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

