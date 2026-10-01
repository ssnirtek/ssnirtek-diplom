<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 */

$this->title = 'Восстановление пароля';
$this->registerCssFile('@web/css/auth.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
]);
?>

<section class="auth-boutique auth-boutique--narrow" aria-labelledby="auth-reset-request-heading">
    <div class="auth-boutique__ambient" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--1" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--2" aria-hidden="true"></div>

    <div class="auth-boutique__shell auth-boutique__shell--single">
        <div class="auth-boutique__showcase auth-boutique__showcase--compact">
            <p class="auth-boutique__eyebrow">Businka</p>
            <h2 class="auth-boutique__headline" id="auth-showcase-reset-req">Доступ<br>к аккаунту</h2>
            <p class="auth-boutique__lede">
                Мы отправим ссылку для сброса пароля на указанный адрес — спокойно и безопасно.
            </p>
            <div class="auth-boutique__ornament" aria-hidden="true">
                <span class="auth-boutique__gem"></span>
                <span class="auth-boutique__line"></span>
            </div>
        </div>

        <div class="auth-boutique__panel">
            <div class="auth-boutique__card">
                <header class="auth-boutique__card-head">
                    <h1 class="auth-boutique__title" id="auth-reset-request-heading">Забыли пароль?</h1>
                    <p class="auth-boutique__subtitle">Введите email, указанный при регистрации</p>
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

                <?php $form = ActiveForm::begin([
                    'action' => ['user/request-password-reset'],
                    'method' => 'post',
                    'options' => ['class' => 'auth-boutique__form'],
                ]); ?>

                <div class="auth-boutique__field">
                    <label class="auth-boutique__label visually-hidden" for="reset-email">Email</label>
                    <input type="email"
                           id="reset-email"
                           name="email"
                           class="login-minimal-input"
                           placeholder="Электронная почта"
                           required
                           autofocus
                           autocomplete="email">
                </div>

                <?= Html::submitButton('Отправить ссылку', [
                    'class' => 'login-minimal-btn auth-boutique__submit',
                ]) ?>

                <?php ActiveForm::end(); ?>

                <p class="auth-boutique__back">
                    <?= Html::a('← Назад ко входу', ['site/login'], ['class' => 'auth-boutique__text-link']) ?>
                </p>
            </div>
        </div>
    </div>
</section>
