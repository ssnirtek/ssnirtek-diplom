<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var string $token
 */

$this->title = 'Новый пароль';
$this->registerCssFile('@web/css/auth.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
]);
?>

<section class="auth-boutique auth-boutique--narrow" aria-labelledby="auth-reset-password-heading">
    <div class="auth-boutique__ambient" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--1" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--2" aria-hidden="true"></div>

    <div class="auth-boutique__shell auth-boutique__shell--single">
        <div class="auth-boutique__showcase auth-boutique__showcase--compact">
            <p class="auth-boutique__eyebrow">Businka</p>
            <h2 class="auth-boutique__headline" id="auth-showcase-reset-pw">Новый ключ<br>к витрине</h2>
            <p class="auth-boutique__lede">
                Придумайте надёжный пароль — так же бережно, как мы относимся к каждому украшению в коллекции.
            </p>
            <div class="auth-boutique__ornament" aria-hidden="true">
                <span class="auth-boutique__gem"></span>
                <span class="auth-boutique__line"></span>
            </div>
        </div>

        <div class="auth-boutique__panel">
            <div class="auth-boutique__card">
                <header class="auth-boutique__card-head">
                    <h1 class="auth-boutique__title" id="auth-reset-password-heading">Новый пароль</h1>
                    <p class="auth-boutique__subtitle">Не менее 8 символов</p>
                </header>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="auth-boutique__alert auth-boutique__alert--error" role="alert">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'action' => ['user/reset-password', 'token' => $token],
                    'method' => 'post',
                    'options' => ['class' => 'auth-boutique__form'],
                ]); ?>

                <div class="password-field auth-boutique__field">
                    <label class="auth-boutique__label visually-hidden" for="new-password">Новый пароль</label>
                    <input type="password"
                           id="new-password"
                           name="password"
                           class="login-minimal-input"
                           placeholder="Новый пароль"
                           required
                           autofocus
                           autocomplete="new-password"
                           minlength="8">
                </div>

                <div class="password-field auth-boutique__field">
                    <label class="auth-boutique__label visually-hidden" for="new-password-repeat">Повтор пароля</label>
                    <input type="password"
                           id="new-password-repeat"
                           name="password_confirm"
                           class="login-minimal-input"
                           placeholder="Повторите пароль"
                           required
                           autocomplete="new-password"
                           minlength="8">
                </div>

                <?= Html::submitButton('Сохранить пароль', [
                    'class' => 'login-minimal-btn auth-boutique__submit',
                ]) ?>

                <?php ActiveForm::end(); ?>

                <p class="auth-boutique__back">
                    <?= Html::a('← Ко входу', ['site/login'], ['class' => 'auth-boutique__text-link']) ?>
                </p>
            </div>
        </div>
    </div>
</section>
