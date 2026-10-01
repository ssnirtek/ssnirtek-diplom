<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\HttpException;

$statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
$is404 = $statusCode === 404;

$this->title = $is404 ? 'Страница не найдена' : 'Ошибка';
$this->registerCssFile('@web/css/pages-info.css');
$this->registerLinkTag([
    'rel' => 'stylesheet',
    'href' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
]);

$headline = $is404 ? 'Страница не найдена' : 'Что-то пошло не так';
$lede = $is404
    ? 'Запрашиваемая страница не существует, была перемещена или временно недоступна. Проверьте адрес или вернитесь на главную.'
    : 'При обработке запроса произошла ошибка. Попробуйте обновить страницу или вернуться на главную. Если проблема повторяется, свяжитесь с нами.';

$codeLabel = $is404 ? '404' : (string) $statusCode;
?>

<section class="error-page" aria-labelledby="error-page-title">
    <div class="error-page__ambient" aria-hidden="true"></div>
    <div class="error-page__glow error-page__glow--1" aria-hidden="true"></div>
    <div class="error-page__glow error-page__glow--2" aria-hidden="true"></div>

    <div class="error-page__shell">
        <div class="error-page__card">
            <p class="error-page__code" aria-hidden="true"><?= Html::encode($codeLabel) ?></p>
            <p class="error-page__eyebrow">Businka</p>
            <h1 id="error-page-title" class="error-page__title"><?= Html::encode($headline) ?></h1>
            <p class="error-page__lede"><?= Html::encode($lede) ?></p>

            <?php if (YII_DEBUG && !$is404 && $message !== ''): ?>
                <div class="error-page__debug" role="alert">
                    <strong>Отладка:</strong>
                    <?= nl2br(Html::encode($message)) ?>
                </div>
            <?php endif; ?>

            <div class="error-page__actions">
                <?= Html::a('На главную', Url::to(['/site/index']), ['class' => 'error-page__btn error-page__btn--primary']) ?>
                <?= Html::a('Каталог', Url::to(['/catalog/index']), ['class' => 'error-page__btn error-page__btn--ghost']) ?>
                <?php if ($is404): ?>
                    <?= Html::a('О нас', Url::to(['/site/about']), ['class' => 'error-page__btn error-page__btn--ghost']) ?>
                <?php else: ?>
                    <?= Html::a('Контакты', Url::to(['/site/contact']), ['class' => 'error-page__btn error-page__btn--ghost']) ?>
                <?php endif; ?>
            </div>

            <p class="error-page__hint">
                Нужна помощь?
                <?= Html::a('Напишите нам', ['/site/contact'], ['class' => 'error-page__link']) ?>
            </p>
        </div>

        <div class="error-page__ornament" aria-hidden="true">
            <span class="error-page__gem"></span>
            <span class="error-page__line"></span>
        </div>
    </div>
</section>
