<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use app\models\Cart;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body
    class="d-flex flex-column h-100"
    data-user-is-guest="<?= Yii::$app->user->isGuest ? '1' : '0' ?>"
>
<?php $this->beginBody() ?>

<!-- Промо-баннер -->
<div class="promo-banner">
    <div class="container-fluid px-0">
        <div class="promo-text">
            ПРИ РЕГИСТРАЦИИ - СКИДКА 15% НА ПЕРВЫЕ 2 ЗАКАЗА
        </div>
    </div>
</div>

<header id="header">
    <?php
    /** @var \app\models\User|null $identity */
    $identity = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
    $isAdmin = $identity && $identity->isIsAdminAdmin();

    $cartCount = 0;
    if ($identity && !$isAdmin) {
        $currentCart = Cart::findOne(['user_id' => Yii::$app->user->id]);
        if ($currentCart) {
            $cartCount = (int) $currentCart->getTotalCount();
        }
    }

    NavBar::begin([
        'brandLabel' => Html::img('/log934.png', ['class' => 'logo', 'alt' => 'Logo'])
            . '<span class="ms-2 ms-md-3 logo-text">' . Html::encode(Yii::$app->name ?: 'Businka') . '</span>',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md fixed-top site-navbar',
        ],
        'innerContainerOptions' => ['class' => 'container-fluid px-3'],
        'brandOptions' => ['class' => 'navbar-brand d-flex align-items-center flex-shrink-0 site-navbar-brand'],
    ]);

    if ($isAdmin) {
        $this->registerCss(<<<'CSS'
.site-navbar .nav-link-admin-dashboard {
    background-color: #ff4081 !important;
    color: #fff !important;
    border: 1px solid #ff4081 !important;
    font-weight: 600;
    border-radius: 999px;
    padding: 0.4rem 1rem !important;
    margin-inline: 0.25rem;
    transition: filter 0.2s ease, box-shadow 0.2s ease;
}
.site-navbar .nav-link-admin-dashboard:hover,
.site-navbar .nav-link-admin-dashboard:focus {
    color: #fff !important;
    filter: brightness(0.95);
    box-shadow: 0 2px 10px rgba(255, 64, 129, 0.35);
}
CSS
        );

        $menuItems = [
            ['label' => 'Главная', 'url' => ['/site/index']],
            ['label' => 'Каталог', 'url' => ['/catalog/index']],
            ['label' => 'О нас', 'url' => ['/site/about']],
            [
                'label' => 'Админ-панель',
                'url' => ['/admin/index'],
                'linkOptions' => [
                    'class' => 'nav-link nav-link-admin-dashboard',
                    'title' => 'Перейти в панель управления',
                ],
            ],
            '<li class="nav-item">'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex h-100 align-items-center'])
                . Html::submitButton('Выйти', ['class' => 'nav-link btn btn-link logout'])
                . Html::endForm()
                . '</li>',
        ];
    } else {
        $menuItems = [
            ['label' => 'Главная', 'url' => ['/site/index']],
            [
                'label' => 'Магазин <span class="nav-link-caret" aria-hidden="true"></span>',
                'encode' => false,
                'dropdownOptions' => ['class' => 'dropdown-menu dropdown-menu-end shadow-sm'],
                'items' => array_values(array_filter([
                    ['label' => 'Каталог', 'url' => ['/catalog/index']],
                    Yii::$app->user->isGuest ? null : ['label' => 'Избранное', 'url' => ['/favorite/index']],
                ])),
            ],
            ['label' => 'О нас', 'url' => ['/site/about']],
        ];

        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
            $menuItems[] = ['label' => 'Регистрация', 'url' => ['/user/create']];
        } else {
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;

            $badgeHtml = $cartCount > 0
                ? Html::tag('span', $cartCount > 99 ? '99+' : (string) $cartCount, ['class' => 'cart-nav-badge'])
                : '';

            $menuItems[] = [
                'label' => '<span class="cart-nav-icon-wrap"><i class="fa fa-shopping-cart" aria-hidden="true"></i>' . $badgeHtml . '</span>',
                'url' => ['/cart/index'],
                'encode' => false,
                'linkOptions' => [
                    'class' => 'nav-link cart-nav-link',
                    'title' => 'Корзина',
                    'aria-label' => 'Корзина, товаров: ' . $cartCount,
                ],
                'options' => ['class' => 'nav-item cart-nav-item'],
            ];

            $menuItems[] = [
                'label' => 'Профиль <span class="nav-link-caret" aria-hidden="true"></span>',
                'encode' => false,
                'dropdownOptions' => ['class' => 'dropdown-menu dropdown-menu-end shadow-sm'],
                'items' => [
                    ['label' => 'Личный кабинет', 'url' => ['/user/profile']],
                    ['label' => 'Мои заказы', 'url' => ['/user/orders']],
                ],
            ];

            $menuItems[] = '<li class="nav-item">'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex h-100 align-items-center'])
                . Html::submitButton(
                    'Выйти (' . Html::encode($user->full_name ?: $user->email) . ')',
                    ['class' => 'nav-link btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
        }
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto align-items-center flex-wrap gap-md-1'],
        'items' => $menuItems,
        'encodeLabels' => false,
    ]);

    NavBar::end();
    ?>
</header>

<main id="main" class="flex-grow-1 flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="site-footer mt-auto flex-shrink-0" role="contentinfo">
    <div class="site-footer__ribbon" aria-hidden="true"></div>

    <div class="site-footer__body">
        <div class="container px-3 px-sm-4">
            <div class="row site-footer__grid g-4 g-lg-5 align-items-start">
                <div class="col-12 col-lg-4 col-xl-3 site-footer__brand">
                    <?= Html::a(
                        Html::img('/log934.png', ['class' => 'site-footer__logo', 'alt' => 'Логотип Businka', 'width' => 48, 'height' => 48])
                        . '<span class="ms-2 ms-md-3 logo-text site-footer__brand-name">Businka</span>',
                        Url::to(['/site/index']),
                        ['class' => 'site-footer__brand-link', 'encode' => false]
                    ) ?>
                    <p class="site-footer__tagline">
                        Украшения ручной работы из бисера и натуральных материалов — для тех, кто ценит характер и качество.
                    </p>
                    <div class="site-footer__pills">
                        <span class="site-footer__pill">Ручная работа</span>
                        <span class="site-footer__pill">Санкт-Петербург</span>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <h2 class="site-footer__heading">Покупателям</h2>
                    <nav class="site-footer__nav" aria-label="Покупателям">
                        <ul class="site-footer__list">
                            <li><?= Html::a('Каталог', Url::to(['/catalog/index']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Главная', Url::to(['/site/index']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Корзина', Url::to(['/cart/index']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Избранное', Url::to(['/favorite/index']), ['class' => 'site-footer__link']) ?></li>
                        </ul>
                    </nav>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <h2 class="site-footer__heading">О бренде</h2>
                    <nav class="site-footer__nav" aria-label="О бренде">
                        <ul class="site-footer__list">
                            <li><?= Html::a('О нас', Url::to(['/site/about']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Контакты', Url::to(['/site/contact']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Политика конфиденциальности', Url::to(['/site/policy']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Вход', Url::to(['/site/login']), ['class' => 'site-footer__link']) ?></li>
                            <li><?= Html::a('Регистрация', Url::to(['/user/create']), ['class' => 'site-footer__link']) ?></li>
                        </ul>
                    </nav>
                </div>

                <div class="col-12 col-md-12 col-lg-4 col-xl-5">
                    <div class="site-footer__map-card">
                        <div class="site-footer__map-card-head">
                            <h2 class="site-footer__map-title">Как нас найти</h2>
                            <p class="site-footer__map-address">
                                Санкт-Петербург, Приморский район, ул. Оптиков, 37
                            </p>
                        </div>
                        <div class="site-footer__map-frame">
                            <?= Html::tag('iframe', '', [
                                'class' => 'site-footer__map-iframe',
                                'src' => 'https://yandex.ru/map-widget/v1/?ll=30.2123%2C60.0020&z=16&l=map&pt=30.2123%2C60.0020%2Cpm2rdm',
                                'loading' => 'lazy',
                                'referrerpolicy' => 'no-referrer-when-downgrade',
                                'title' => 'Карта: ул. Оптиков, 37, Санкт-Петербург',
                                'allowfullscreen' => true,
                            ]) ?>
                        </div>
                        <div class="site-footer__map-actions">
                            <?= Html::a(
                                'Открыть в Яндекс.Картах',
                                'https://yandex.ru/maps/?text=' . rawurlencode('Санкт-Петербург, Приморский район, ул. Оптиков, 37'),
                                [
                                    'class' => 'site-footer__map-cta',
                                    'target' => '_blank',
                                    'rel' => 'noopener noreferrer',
                                ]
                            ) ?>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>

    <div class="site-footer__bar">
        <div class="container site-footer__bar-inner px-3 px-sm-4">
            <p class="site-footer__copy">
                &copy; <?= date('Y') ?> Businka. Все права защищены.
            </p>
            <nav class="site-footer__legal" aria-label="Юридическая информация">
                <?= Html::a('Восстановление пароля', Url::to(['/user/request-password-reset']), ['class' => 'site-footer__legal-link']) ?>
            </nav>
            <div class="site-footer__social">
                <?= Html::a('<i class="fa-brands fa-vk" aria-hidden="true"></i><span class="visually-hidden">ВКонтакте</span>', 'https://vk.com/', [
                    'class' => 'site-footer__social-link',
                    'encode' => false,
                    'aria-label' => 'ВКонтакте',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                ]) ?>
                <?= Html::a('<i class="fa-brands fa-telegram" aria-hidden="true"></i><span class="visually-hidden">Telegram</span>', 'https://t.me/', [
                    'class' => 'site-footer__social-link',
                    'encode' => false,
                    'aria-label' => 'Telegram',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                ]) ?>
            </div>
        </div>
    </div>
</footer>

<div id="toast-minimal" class="site-toast" role="status" aria-live="polite" aria-atomic="true"></div>
<?php $this->endBody() ?>
<?= $this->render('_cookie_notice') ?>
</body>
</html>
<?php $this->endPage() ?>