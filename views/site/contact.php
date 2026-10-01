<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Контакты';
$this->registerCssFile('@web/css/auth.css');
$this->registerCssFile('@web/css/pages-info.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
]);
?>

<section class="auth-boutique auth-boutique--contact" aria-labelledby="contact-page-title">
    <div class="auth-boutique__ambient" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--1" aria-hidden="true"></div>
    <div class="auth-boutique__glow auth-boutique__glow--2" aria-hidden="true"></div>

    <div class="auth-boutique__shell auth-boutique__shell--contact">
        <aside class="auth-boutique__showcase auth-boutique__showcase--compact contact-showcase">
            <p class="auth-boutique__eyebrow">Businka</p>
            <h2 class="auth-boutique__headline" id="contact-page-title">Свяжитесь<br>с нами</h2>
            <p class="auth-boutique__lede">
                Вопросы по заказу, доставке или индивидуальному украшению — напишите, и мы ответим в ближайшее время.
            </p>

            <ul class="contact-showcase__list">
                <li>
                    <span class="contact-showcase__icon" aria-hidden="true">
                        <img src="/images/icon/adress.png" alt="" width="20" height="20" decoding="async">
                    </span>
                    <span>Санкт-Петербург, Приморский район, ул. Оптиков 37</span>
                </li>
                <li>
                    <span class="contact-showcase__icon" aria-hidden="true">
                        <img src="/images/icon/phone1.png" alt="" width="20" height="20" decoding="async">
                    </span>
                    <a href="tel:+78123456789" class="contact-showcase__link">+7 (812) 345-67-89</a>
                </li>
                <li>
                    <span class="contact-showcase__icon" aria-hidden="true">
                        <img src="/images/icon/email.png" alt="" width="20" height="20" decoding="async">
                    </span>
                    <a href="mailto:info@Businka" class="contact-showcase__link">info@Businka</a>
                </li>
                <li>
                    <span class="contact-showcase__icon" aria-hidden="true">
                        <img src="/images/icon/clock.png" alt="" width="20" height="20" decoding="async">
                    </span>
                    <span>Пн–Пт: 10:00–20:00, Сб–Вс: 11:00–19:00</span>
                </li>
            </ul>

            <div class="contact-showcase__map" aria-label="Карта проезда">
                <iframe
                    src="https://yandex.ru/map-widget/v1/?um=constructor%3Afe1e0c997688632420ee797a7dd0566002e091607abbb0fdfa967a21e4327c32&amp;source=constructor"
                    loading="lazy"
                    title="Карта проезда к Businka"></iframe>
            </div>

            <div class="auth-boutique__ornament" aria-hidden="true">
                <span class="auth-boutique__gem"></span>
                <span class="auth-boutique__line"></span>
            </div>
        </aside>

        <div class="auth-boutique__panel contact-panel">
            <div class="auth-boutique__card auth-boutique__card--wide contact-card">
                <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>
                    <div class="contact-success" role="status">
                        <div class="contact-success__icon" aria-hidden="true">✓</div>
                        <h1 class="auth-boutique__title">Сообщение отправлено</h1>
                        <p class="auth-boutique__subtitle contact-success__text">
                            Спасибо за обращение! Мы получили ваше сообщение и ответим в ближайшее время.
                        </p>
                        <?php if (YII_DEBUG && Yii::$app->mailer->useFileTransport): ?>
                            <p class="contact-success__debug">
                                Режим разработки: письмо сохранено в
                                <code><?= Html::encode(Yii::getAlias(Yii::$app->mailer->fileTransportPath)) ?></code>.
                            </p>
                        <?php endif; ?>
                        <div class="contact-success__actions">
                            <?= Html::a('На главную', ['/site/index'], ['class' => 'login-minimal-btn auth-boutique__submit']) ?>
                            <?= Html::a('Каталог', ['/catalog/index'], ['class' => 'contact-btn-ghost']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <header class="auth-boutique__card-head">
                        <h1 class="auth-boutique__title">Напишите нам</h1>
                        <p class="auth-boutique__subtitle">Заполните форму — мы свяжемся с вами по email</p>
                    </header>

                    <?php $form = ActiveForm::begin([
                        'id' => 'contact-form',
                        'options' => ['class' => 'auth-boutique__form contact-form'],
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"auth-boutique__field\">{input}\n{error}</div>",
                            'labelOptions' => ['class' => 'contact-form__label'],
                            'inputOptions' => ['class' => 'login-minimal-input'],
                            'errorOptions' => ['class' => 'login-minimal-error'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'name')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Иванов Иван',
                        'autocomplete' => 'name',
                    ]) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'placeholder' => 'name@example.com',
                        'type' => 'email',
                        'autocomplete' => 'email',
                    ]) ?>

                    <?= $form->field($model, 'subject')->textInput([
                        'placeholder' => 'Тема сообщения',
                    ]) ?>

                    <?= $form->field($model, 'body')->textarea([
                        'rows' => 5,
                        'placeholder' => 'Ваш вопрос или комментарий',
                        'class' => 'login-minimal-input contact-form__textarea',
                    ]) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="contact-captcha">{image}{input}</div>',
                        'options' => [
                            'class' => 'login-minimal-input contact-captcha__input',
                            'placeholder' => 'Код с картинки',
                            'autocomplete' => 'off',
                        ],
                        'imageOptions' => ['class' => 'contact-captcha__image', 'alt' => 'Капча'],
                    ]) ?>

                    <div class="contact-form__actions">
                        <?= Html::submitButton('Отправить сообщение', [
                            'class' => 'login-minimal-btn auth-boutique__submit',
                            'name' => 'contact-button',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
