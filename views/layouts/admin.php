<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag([
    'name' => 'viewport',
    'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no',
]);
$this->registerLinkTag([
    'rel' => 'preconnect',
    'href' => 'https://fonts.googleapis.com',
]);
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
]);
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
]);

$this->registerCssFile('@web/css/tokens.css');
$this->registerCssFile('@web/css/components.css');
$this->registerCssFile('@web/css/admin.css');
$this->registerCssFile('@web/css/admin-pages.css');
$this->registerJsFile('@web/js/admin.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
$email = $user ? Html::encode($user->email) : '';

$navItems = [
    ['label' => 'Дашборд', 'url' => ['/admin/index'], 'icon' => 'fa-chart-line', 'routes' => ['admin/index']],
    ['label' => 'Заказы', 'url' => ['/admin/orders'], 'icon' => 'fa-bag-shopping', 'routes' => ['admin/orders', 'admin/order-view']],
    ['label' => 'Товары', 'url' => ['/admin/products'], 'icon' => 'fa-box-open', 'routes' => ['admin/products', 'admin/update-product']],
    ['label' => 'Отзывы', 'url' => ['/admin/reviews'], 'icon' => 'fa-comments', 'routes' => ['admin/reviews']],
    ['label' => 'Акции', 'url' => ['/admin-promo/index'], 'icon' => 'fa-tags', 'routes' => ['admin-promo/index', 'admin-promo/create', 'admin-promo/update']],
];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> | Админ-панель Businka</title>
    <?php $this->head() ?>
</head>
<body class="businka-admin">
<?php $this->beginBody() ?>

<div class="admin-app">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar__brand">
            <a href="<?= Url::to(['/admin/index']) ?>" class="admin-sidebar__brand-link">
                <span class="admin-sidebar__brand-text">Businka</span><span class="admin-sidebar__brand-dot" aria-hidden="true">.</span>
            </a>
            <button type="button" class="admin-sidebar__close d-md-none" id="admin-sidebar-close" aria-label="Закрыть меню">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <nav class="admin-sidebar__nav" aria-label="Админ-меню">
            <?php
            $currentRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
            foreach ($navItems as $item):
                $active = in_array($currentRoute, $item['routes'], true);
                ?>
                <a href="<?= Url::to($item['url']) ?>"
                   class="admin-sidebar__link<?= $active ? ' is-active' : '' ?>">
                    <i class="fa-solid <?= Html::encode($item['icon']) ?> admin-sidebar__icon" aria-hidden="true"></i>
                    <span><?= Html::encode($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar__footer d-none d-md-block">
            <span class="admin-sidebar__muted">Панель управления</span>
        </div>
    </aside>

    <div class="admin-sidebar__backdrop" id="admin-sidebar-backdrop" hidden></div>

    <div class="admin-main-wrap">
        <header class="admin-topbar">
            <button type="button" class="admin-topbar__burger d-md-none" id="admin-sidebar-open" aria-label="Открыть меню">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="admin-topbar__spacer d-none d-md-block"></div>
            <div class="admin-topbar__user">
                <?php if ($email): ?>
                    <span class="admin-topbar__email"><i class="fa-regular fa-user admin-topbar__email-icon" aria-hidden="true"></i><?= $email ?></span>
                <?php endif; ?>
                <?= Html::a('<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> На сайт', ['/site/index'], [
                    'class' => 'admin-btn admin-btn--secondary admin-btn--sm',
                    'encode' => false,
                ]) ?>
                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'admin-topbar__logout']) ?>
                <?= Html::submitButton('<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Выход', [
                    'class' => 'admin-btn admin-btn--ghost admin-btn--sm',
                    'encode' => false,
                ]) ?>
                <?= Html::endForm() ?>
            </div>
        </header>

        <main class="admin-content">
            <?php
            foreach (Yii::$app->session->getAllFlashes(true) as $type => $messages) {
                foreach ((array) $messages as $message) {
                    $safeType = preg_replace('/[^a-z0-9_-]/i', '', (string) $type) ?: 'info';
                    echo Html::tag('div', Html::encode($message), [
                        'class' => 'admin-flash admin-flash--' . $safeType,
                        'role' => 'alert',
                    ]);
                }
            }
            ?>
            <?= $content ?>
        </main>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
