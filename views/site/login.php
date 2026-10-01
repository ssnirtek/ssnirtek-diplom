<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Вход';
$this->registerCssFile('@web/css/auth.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
]);
?>

<section class="auth-boutique" aria-labelledby="auth-login-heading">
    <div class="auth-boutique__ambient" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--1" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--2" aria-hidden="true"></div>

    <div class="auth-boutique__shell">
        <div class="auth-boutique__showcase">
            <p class="auth-boutique__eyebrow">Businka</p>
            <h2 class="auth-boutique__headline" id="auth-showcase-login">Украшения,<br>которые остаются</h2>
            <p class="auth-boutique__lede">
                Войдите, чтобы продолжить покупки и следить за заказами — сдержанная эстетика и внимание к деталям в каждом изделии.
            </p>
            <div class="auth-boutique__ornament" aria-hidden="true">
                <span class="auth-boutique__gem"></span>
                <span class="auth-boutique__line"></span>
            </div>
        </div>

        <div class="auth-boutique__panel">
            <div class="auth-boutique__card">
                <header class="auth-boutique__card-head">
                    <h1 class="auth-boutique__title" id="auth-login-heading">Добро пожаловать</h1>
                    <p class="auth-boutique__subtitle">Вход в личный кабинет</p>
                </header>

                <?php $form = ActiveForm::begin([
                    'id' => 'login-form-minimal',
                    'fieldConfig' => [
                        'template' => "<div class=\"auth-boutique__field\">{input}\n{error}</div>",
                        'inputOptions' => ['class' => 'login-minimal-input'],
                        'errorOptions' => ['class' => 'login-minimal-error'],
                    ],
                ]); ?>

                <?= $form->field($model, 'email')->textInput([
                    'autofocus' => true,
                    'placeholder' => 'Электронная почта',
                    'autocomplete' => 'email',
                ]) ?>

                <div class="password-field auth-boutique__field">
                    <?= $form->field($model, 'password', [
                        'template' => "{input}\n{error}",
                    ])->passwordInput([
                        'placeholder' => 'Пароль',
                        'class' => 'login-minimal-input',
                        'autocomplete' => 'current-password',
                    ]) ?>
                </div>

                <div class="auth-boutique__toolbar login-minimal-options">
                    <label class="login-minimal-checkbox auth-boutique__remember">
                        <?= Html::activeCheckbox($model, 'rememberMe', [
                            'label' => false,
                            'class' => 'login-minimal-checkbox-input',
                        ]) ?>
                        <span class="login-minimal-checkbox-text">Запомнить меня</span>
                    </label>
                    <?= Html::a('Забыли пароль?', ['user/request-password-reset'], [
                        'class' => 'auth-boutique__text-link',
                    ]) ?>
                </div>

                <?= Html::submitButton('Войти', [
                    'class' => 'login-minimal-btn auth-boutique__submit',
                    'name' => 'login-button',
                ]) ?>

                <?php ActiveForm::end(); ?>

                <div class="auth-boutique__divider login-minimal-divider" role="presentation">
                    <span></span>
                    <span>или</span>
                    <span></span>
                </div>

                <p class="auth-boutique__switch login-minimal-register">
                    <span class="login-minimal-text">Нет аккаунта?</span>
                    <?= Html::a('Создать аккаунт', ['user/create'], [
                        'class' => 'login-minimal-link auth-boutique__accent-link',
                    ]) ?>
                </p>
            </div>
        </div>
    </div>
</section>
