<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\Promo[] $promos */

$this->registerCssFile('@web/css/about-page.css');
?>

<div class="site-about">
    <section class="about-hero" aria-labelledby="about-hero-title">
        <div class="about-hero__bg" aria-hidden="true"></div>
        <div class="about-hero__overlay" aria-hidden="true"></div>
        <div class="about-hero__inner">
            <header class="about-hero__header">
                <h1 id="about-hero-title" class="about-main-title">Businka</h1>
                <p class="about-slogan">Украшения из бисера ручной работы</p>
            </header>

            <div class="about-hero__intro about-description">
                <p>Добро пожаловать в <strong>Businka</strong> — магазин уникальных украшений из бисера ручной работы.
                    Мы создаём изделия с душой и любовью, используя только качественные материалы: чешский и японский бисер,
                    натуральные камни, фурнитуру с покрытием под золото и серебро.</p>
                <p>В нашем ассортименте вы найдёте серьги, браслеты, колье, кольца и другие аксессуары на любой вкус.
                    Каждое изделие уникально и существует в единственном экземпляре.</p>
            </div>

            <div class="about-features-grid">
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/ryka.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Ручная работа</h3>
                        <p class="about-feature__text">Каждое украшение создаётся вручную с любовью и вниманием к деталям</p>
                    </div>
                </article>
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/bril.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Качественные материалы</h3>
                        <p class="about-feature__text">Чешский и японский бисер, натуральные камни, гипоаллергенная фурнитура</p>
                    </div>
                </article>
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/dis.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Уникальный дизайн</h3>
                        <p class="about-feature__text">Авторские коллекции, которые не найти в масс-маркете</p>
                    </div>
                </article>
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/present.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Подарочная упаковка</h3>
                        <p class="about-feature__text">Каждое украшение упаковано в стильную коробочку, готовую для подарка</p>
                    </div>
                </article>
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/deliv.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Доставка по всей России</h3>
                        <p class="about-feature__text">Быстрая и надёжная доставка в любой уголок страны</p>
                    </div>
                </article>
                <article class="about-feature">
                    <span class="about-feature__icon" aria-hidden="true">
                        <img src="/images/icon/heart.png" alt="" width="22" height="22" decoding="async">
                    </span>
                    <div class="about-feature__body">
                        <h3 class="about-feature__title">Индивидуальный подход</h3>
                        <p class="about-feature__text">Создадим украшение специально для вас по вашему эскизу</p>
                    </div>
                </article>
            </div>

            <div class="about-stats" aria-label="Ключевые цифры">
                <div class="stat-item">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">созданных украшений</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">5+</span>
                    <span class="stat-label">лет опыта</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">довольных клиентов</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">ручная работа</span>
                </div>
            </div>
        </div>
    </section>

    <section class="about-page-panel about-page-panel--quote" aria-label="Цитата">
        <div class="about-page-wrap">
            <blockquote class="about-quote">
                <span class="about-quote__mark" aria-hidden="true"><i class="fas fa-quote-left"></i></span>
                <p class="about-quote__text">Мы верим, что украшения — это не просто аксессуары, а способ выразить свою индивидуальность.
                    Каждая бусина, каждый камень в наших изделиях подобраны с особым вниманием, чтобы вы чувствовали
                    себя уверенно и неповторимо.</p>
                <footer class="about-quote__footer"><cite class="quote-author">— Основательница Businka</cite></footer>
            </blockquote>
        </div>
    </section>

    <div style="text-align: center; margin: 15px 0;">
    <a href="<?= Url::to(['/site/contact', '#' => 'delivery-rules']) ?>" class="delivery-btn">
        Связаться с нами →
    </a>
</div>   

    <section class="contact-map-fullwidth" aria-labelledby="about-contact-heading">
        <div class="contact-map-container">
            <h2 id="about-contact-heading" class="visually-hidden">Контакты и карта</h2>
            <div class="contact-grid">
                <div class="contact-card">
                        <h3 class="contact-title">Контакты</h3>
                        <ul class="contact-list">
                            <li>
                                <span class="about-list-icon" aria-hidden="true"><img src="/images/icon/adress.png" alt="" width="20" height="20" decoding="async"></span>
                                <span>Санкт-Петербург, Приморский район, ул. Оптиков 37</span>
                            </li>
                            <li>
                                <span class="about-list-icon" aria-hidden="true"><img src="/images/icon/deliv.png" alt="" width="20" height="20" decoding="async"></span>
                                <span>м. Беговая (около 20 минут пешком)</span>
                            </li>
                            <li>
                                <span class="about-list-icon" aria-hidden="true"><img src="/images/icon/phone1.png" alt="" width="20" height="20" decoding="async"></span>
                                <a href="tel:+78123456789" class="contact-link">+7 (812) 345-67-89</a>
                            </li>
                            <li>
                                <span class="about-list-icon" aria-hidden="true"><img src="/images/icon/email.png" alt="" width="20" height="20" decoding="async"></span>
                                <a href="mailto:info@Businka" class="contact-link">info@Businka</a>
                            </li>
                            <li>
                                <span class="about-list-icon" aria-hidden="true"><img src="/images/icon/clock.png" alt="" width="20" height="20" decoding="async"></span>
                                <span>Пн–Пт: 10:00–20:00, Сб–Вс: 11:00–19:00</span>
                            </li>
                        </ul>
                </div>
                <div class="map-card">
                        <h3 class="map-title">Как нас найти</h3>
                        <div class="map-container">
                            <iframe
                                src="https://yandex.ru/map-widget/v1/?um=constructor%3Afe1e0c997688632420ee797a7dd0566002e091607abbb0fdfa967a21e4327c32&amp;source=constructor"
                                loading="lazy"
                                title="Карта проезда к Businka"></iframe>
                        </div>
                </div>
            </div>
        </div>
    </section>

    <section class="last-promo-section" aria-labelledby="about-promos-heading">
        <div class="last-promo-container last-promo-container--multi">
            <?php if (!empty($promos)): ?>
                <header class="last-promo-head">
                    <h2 id="about-promos-heading" class="last-promo-head__title">Акции и спецпредложения</h2>
                    <p class="last-promo-head__lead">Актуальные предложения магазина Businka</p>
                </header>
                <?= $this->render('_promo_cards', ['promos' => $promos]) ?>
                <div class="last-promo-cta">
                    <h2 class="cta-title">Больше акций в нашем каталоге</h2>
                    <p class="cta-text">Откройте для себя интересные предложения и выгодные скидки</p>
                    <a href="<?= Url::to(['/catalog/index']) ?>" class="cta-button">Перейти в каталог →</a>
                </div>
            <?php else: ?>
                <div class="last-promo-placeholder">
                    <div class="placeholder-content">
                        <h3 class="promo-title">Акции скоро появятся</h3>
                        <p class="promo-description">Следите за обновлениями в каталоге</p>
                        <a href="<?= Url::to(['/catalog/index']) ?>" class="cta-button">Перейти в каталог →</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="delivery-rules" class="delivery-rules-fullwidth" aria-labelledby="delivery-rules-title">
        <div class="delivery-rules-container">
            <header class="delivery-rules-header">
                <h2 id="delivery-rules-title" class="delivery-rules-title">Правила доставки</h2>
                <p class="delivery-rules-lead">Самовывоз в Санкт-Петербурге и отправка по России — кратко о вариантах и сроках</p>
            </header>

            <div class="delivery-rules-grid">
                <article class="rule-card">
                    <div class="rule-icon">
                        <img src="/images/icon/heart.png" alt="" class="rule-icon__img" width="48" height="48" decoding="async">
                    </div>
                    <h3 class="rule-title">Самовывоз</h3>
                    <div class="rule-content">
                        <p><strong>Адрес:</strong> Санкт-Петербург, Приморский район, ул. Оптиков 37</p>
                        <p><strong>График:</strong> Пн–Пт 10:00–20:00, Сб–Вс 11:00–19:00</p>
                        <p><strong>Стоимость:</strong> бесплатно</p>
                        <p class="rule-note">Предварительно согласуйте время визита</p>
                    </div>
                </article>
                <article class="rule-card">
                    <div class="rule-icon">
                        <img src="/images/icon/phone.png" alt="" class="rule-icon__img" width="48" height="48" decoding="async">
                    </div>
                    <h3 class="rule-title">Доставка по Санкт-Петербургу</h3>
                    <div class="rule-content">
                        <p><strong>Стоимость:</strong> 300 ₽</p>
                        <p><strong>Бесплатно:</strong> при заказе от 2500 ₽</p>
                        <p><strong>Сроки:</strong> 1–2 рабочих дня</p>
                        <p><strong>Время доставки:</strong> 10:00–20:00</p>
                    </div>
                </article>
                <article class="rule-card">
                    <div class="rule-icon">
                        <img src="/images/icon/ryka.png" alt="" class="rule-icon__img" width="48" height="48" decoding="async">
                    </div>
                    <h3 class="rule-title">Доставка по России</h3>
                    <div class="rule-content">
                        <p><strong>Способы:</strong> Почта России, СДЭК</p>
                        <p><strong>Стоимость:</strong> рассчитывается индивидуально</p>
                        <p><strong>Сроки:</strong> 3–10 рабочих дней</p>
                        <p><strong>Бесплатно:</strong> при заказе от 5000 ₽</p>
                    </div>
                </article>
            </div>

            <aside class="delivery-info" aria-label="Важная информация по доставке">
                <h3 class="info-title">Важная информация</h3>
                <ul class="info-list">
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Отправка заказов — в течение 1–2 рабочих дней после оформления</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>После отправки вы получите трек-номер для отслеживания посылки</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>При заказе от 2500 ₽ доставка по Санкт-Петербургу бесплатная</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>Возможен срочный заказ — согласуйте с менеджером</span></li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i><span>При получении проверьте целостность упаковки и изделий</span></li>
                </ul>
            </aside>
        </div>
    </section>

    <section class="benefits-fullwidth" aria-labelledby="benefits-title">
        <div class="benefits-container">
            <div class="benefits-wrapper">
                <div class="benefits-content">
                    <h2 id="benefits-title" class="benefits-title">Преимущества</h2>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Ручная работа высокого качества</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Индивидуальный подход к каждому клиенту</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Возможность создания украшений на заказ</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Доставка по Санкт-Петербургу и всей России</li>
                        <li><i class="fas fa-check-circle" aria-hidden="true"></i> Подарочная упаковка каждого изделия</li>
                    </ul>
                </div>
                <div class="benefits-image" id="benefitsImage">
                    <img src="/images/benefits.jpg" alt="Украшения Businka">
                    <div class="benefits-overlay">
                        <h3>Об уходе за украшениями</h3>
                        <ul>
                            <li>Снимайте украшения перед водными процедурами</li>
                            <li>Избегайте контакта с парфюмерией и косметикой</li>
                            <li>Храните в сухом месте</li>
                            <li>Очищайте мягкой тканью</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews-fullwidth" aria-labelledby="reviews-full-title">
        <div class="reviews-container">
            <h2 id="reviews-full-title" class="reviews-full-title">Отзывы наших клиентов</h2>
            <?php if (!empty($latestReviews)): ?>
                <div class="reviews-full-grid">
                    <?php foreach (array_slice($latestReviews, 0, 5) as $review): ?>
                        <article class="review-full-card">
                            <div class="review-full-image">
                                <?php if ($review->product && $review->product->image_product): ?>
                                    <img src="/images/<?= Html::encode($review->product->image_product) ?>"
                                         alt="<?= Html::encode($review->product->name) ?>">
                                <?php else: ?>
                                    <img src="/images/default-product.jpg" alt="Нет фото товара">
                                <?php endif; ?>
                                <div class="review-full-overlay"></div>
                            </div>
                            <div class="review-full-content">
                                <div class="review-full-stars"><?= $review->getStarsHtml() ?></div>
                                <p class="review-full-text">«<?= Html::encode($review->text) ?>»</p>
                                <p class="review-full-author">— <?= Html::encode($review->getAuthorName()) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-reviews-full">Пока нет отзывов</p>
            <?php endif; ?>
        </div>
    </section>
</div>
