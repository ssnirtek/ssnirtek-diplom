<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Политика конфиденциальности';
$this->registerCssFile('@web/css/pages-info.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
]);
?>

<div class="policy-page">
    <section class="policy-hero" aria-labelledby="policy-title">
        <div class="policy-hero__ambient" aria-hidden="true"></div>
        <div class="policy-hero__inner">
            <p class="policy-hero__eyebrow">Businka</p>
            <h1 id="policy-title" class="policy-hero__title">Политика конфиденциальности</h1>
            <p class="policy-hero__meta">Дата публикации: <?= Html::encode(date('d.m.Y')) ?></p>
        </div>
    </section>

    <div class="policy-page__wrap">
        <article class="policy-card">
            <section class="policy-section">
                <h2>1. Общие положения</h2>
                <p>
                    Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных
                    пользователей интернет-магазина «Businka» (далее — Сайт). Используя Сайт, регистрируясь или
                    оформляя заказ, вы соглашаетесь с условиями данной Политики.
                </p>
            </section>

            <section class="policy-section">
                <h2>2. Какие данные мы собираем</h2>
                <ul class="policy-list">
                    <li>адрес электронной почты, ФИО, телефон, дата рождения — при регистрации;</li>
                    <li>адрес доставки и комментарий к заказу — при оформлении покупки;</li>
                    <li>история заказов, содержимое корзины и избранного;</li>
                    <li>тексты отзывов и оценки товаров;</li>
                    <li>технические данные: IP-адрес, файлы cookie, данные сессии браузера.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>3. Цели обработки данных</h2>
                <ul class="policy-list">
                    <li>регистрация и авторизация пользователей;</li>
                    <li>обработка и доставка заказов;</li>
                    <li>информирование о статусе заказа и акциях;</li>
                    <li>подтверждение email и восстановление пароля;</li>
                    <li>улучшение работы Сайта и качества обслуживания;</li>
                    <li>соблюдение требований законодательства Российской Федерации.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>4. Файлы cookie и сессии</h2>
                <p>
                    Сайт использует файлы cookie и серверные сессии для корректной работы корзины, авторизации
                    и сохранения настроек. При выборе «Запомнить меня» создаётся cookie для автоматического входа.
                    Согласие на использование cookie фиксируется в локальном хранилище браузера после нажатия
                    кнопки «Принять» в уведомлении на Сайте.
                </p>
            </section>

            <section class="policy-section">
                <h2>5. Защита персональных данных</h2>
                <p>
                    Пароли пользователей хранятся в виде криптографических хешей и не передаются третьим лицам
                    в открытом виде. Передача данных по сети защищается протоколом HTTPS (при развёртывании
                    на production-сервере). Доступ к административным функциям ограничен ролью администратора.
                </p>
            </section>

            <section class="policy-section">
                <h2>6. Передача данных третьим лицам</h2>
                <p>
                    Персональные данные не передаются третьим лицам, за исключением случаев, необходимых для
                    исполнения заказа (службы доставки, платёжные операторы — при подключении), а также случаев,
                    предусмотренных законодательством РФ.
                </p>
            </section>

            <section class="policy-section">
                <h2>7. Права пользователя</h2>
                <p>Вы вправе:</p>
                <ul class="policy-list">
                    <li>запросить информацию о своих персональных данных;</li>
                    <li>изменить данные профиля в личном кабинете;</li>
                    <li>отозвать согласие на обработку данных, обратившись к администратору Сайта;</li>
                    <li>потребовать удаления учётной записи при отсутствии незавершённых заказов.</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>8. Контакты</h2>
                <p>
                    По вопросам обработки персональных данных обращайтесь через
                    <?= Html::a('страницу контактов', ['/site/contact'], ['class' => 'policy-link']) ?>
                    или на email:
                    <?= Html::a('info@Businka', 'mailto:info@Businka', ['class' => 'policy-link']) ?>.
                </p>
            </section>

            <footer class="policy-card__footer">
                <?= Html::a('На главную', Url::to(['/site/index']), ['class' => 'policy-btn policy-btn--primary']) ?>
                <?= Html::a('Каталог', Url::to(['/catalog/index']), ['class' => 'policy-btn policy-btn--ghost']) ?>
            </footer>
        </article>
    </div>
</div>
