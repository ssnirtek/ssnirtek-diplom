<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Регистрация';
$this->registerCssFile('@web/css/auth.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
]);
$this->registerJsFile('@web/js/register-validation.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);
?>

<section class="auth-boutique auth-boutique--register" aria-labelledby="auth-register-heading">
    <div class="auth-boutique__ambient" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--1" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--2" aria-hidden="true"></div>

    <div class="auth-boutique__shell">
        <div class="auth-boutique__showcase">
            <p class="auth-boutique__eyebrow">Businka</p>
            <h2 class="auth-boutique__headline" id="auth-showcase-register">Ваша история<br>с украшениями</h2>
            <p class="auth-boutique__lede">
                Создайте аккаунт, чтобы сохранять избранное, оформлять заказы и получать персональные рекомендации.
            </p>
            <div class="auth-boutique__ornament" aria-hidden="true">
                <span class="auth-boutique__gem"></span>
                <span class="auth-boutique__line"></span>
            </div>
        </div>

        <div class="auth-boutique__panel">
            <div class="auth-boutique__card auth-boutique__card--wide">
                <header class="auth-boutique__card-head">
                    <h1 class="auth-boutique__title" id="auth-register-heading">Регистрация</h1>
                    <p class="auth-boutique__subtitle">Заполните поля ниже — это займёт пару минут</p>
                </header>

                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="auth-boutique__alert auth-boutique__alert--success" role="status">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="auth-boutique__alert auth-boutique__alert--error" role="alert">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <?php if ($model->hasErrors()): ?>
                    <div class="auth-boutique__alert auth-boutique__alert--error" role="alert">
                        <strong>Пожалуйста, исправьте ошибки:</strong>
                        <ul class="auth-boutique__error-list">
                            <?php foreach ($model->errors as $field => $errors): ?>
                                <li><?= Html::encode($model->getAttributeLabel($field)) ?> — <?= Html::encode(implode(', ', $errors)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id' => 'user-form',
                    'options' => ['class' => 'user-form-content auth-boutique__form'],
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => true,
                    'validateOnBlur' => true,
                    'validateOnChange' => true,
                    'fieldConfig' => [
                        'template' => "{label}\n<div class=\"auth-boutique__field\">{input}\n{hint}\n{error}</div>",
                        'labelOptions' => ['class' => 'user-form-label auth-boutique__label'],
                        'inputOptions' => ['class' => 'login-minimal-input'],
                        'hintOptions' => ['class' => 'user-form-hint'],
                        'errorOptions' => ['class' => 'login-minimal-error'],
                    ],
                ]); ?>

                <div class="user-form-row">
                    <div class="user-form-group full-width">
                        <?= $form->field($model, 'email')->textInput([
                            'placeholder' => 'name@example.com',
                            'autofocus' => true,
                            'type' => 'email',
                            'autocomplete' => 'email',
                            'id' => 'user-email',
                        ]) ?>
                    </div>
                </div>

                <div class="user-form-row double">
                    <div class="user-form-group half">
                        <div class="password-field auth-boutique__field">
                            <?= $form->field($model, 'password_hash', [
                                'template' => "{label}\n{input}\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Не менее 8 символов',
                                'autocomplete' => 'new-password',
                                'class' => 'login-minimal-input',
                                'id' => 'user-password_hash',
                            ]) ?>
                        </div>
                    </div>
                    <div class="user-form-group half">
                        <div class="password-field auth-boutique__field">
                            <?= $form->field($model, 'password_repetition', [
                                'template' => "{label}\n{input}\n{error}",
                            ])->passwordInput([
                                'placeholder' => 'Повторите пароль',
                                'autocomplete' => 'new-password',
                                'class' => 'login-minimal-input',
                                'id' => 'user-password_repetition',
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group full-width">
                        <?= $form->field($model, 'full_name')->textInput([
                            'placeholder' => 'Иванов Иван Иванович',
                            'autocomplete' => 'name',
                            'id' => 'user-full_name',
                        ]) ?>
                    </div>
                </div>

                <div class="user-form-row double">
                    <div class="user-form-group half">
                        <?= $form->field($model, 'phone')->widget(MaskedInput::class, [
                            'mask' => '+7 (999) 999-99-99',
                            'options' => [
                                'class' => 'login-minimal-input',
                                'placeholder' => '+7 (___) ___-__-__',
                                'autocomplete' => 'tel',
                                'id' => 'user-phone',
                            ],
                        ]) ?>
                    </div>
                    <div class="user-form-group half">
                        <?= $form->field($model, 'date_born')->widget(MaskedInput::class, [
                            'mask' => '99-99-9999',
                            'options' => [
                                'class' => 'login-minimal-input',
                                'placeholder' => 'ДД-ММ-ГГГГ',
                                'autocomplete' => 'bday',
                                'id' => 'user-date_born',
                            ],
                        ]) ?>
                    </div>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group full-width">
                        <?= $form->field($model, 'agree')->checkbox([
                            'template' => "<div class=\"auth-boutique__consent login-minimal-checkbox\">{input} {label}</div>\n{error}",
                            'label' => 'Я согласен(на) на обработку персональных данных в соответствии с политикой конфиденциальности',
                            'labelOptions' => ['class' => 'login-minimal-checkbox-label'],
                            'inputOptions' => [
                                'class' => 'login-minimal-checkbox-input',
                                'id' => 'user-agree',
                            ],
                        ]) ?>
                    </div>
                </div>

                <div class="user-form-actions">
                    <?= Html::submitButton('Зарегистрироваться', [
                        'class' => 'login-minimal-btn auth-boutique__submit',
                        'name' => 'register-button',
                    ]) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <p class="auth-boutique__switch login-minimal-register">
                    <span class="login-minimal-text">Уже есть аккаунт?</span>
                    <?= Html::a('Войти', ['site/login'], ['class' => 'login-minimal-link auth-boutique__accent-link']) ?>
                </p>
            </div>
        </div>
    </div>
</section>
