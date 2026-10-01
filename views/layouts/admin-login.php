<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;

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
$this->registerCssFile('@web/css/tokens.css');
$this->registerCssFile('@web/css/admin.css');
$this->registerJsFile('@web/js/admin.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> | Businka</title>
    <?php $this->head() ?>
</head>
<body class="admin-login-body">
<?php $this->beginBody() ?>

<div class="admin-login-page">
    <div class="admin-login-card">
        <?php
        foreach (Yii::$app->session->getAllFlashes(true) as $type => $messages) {
            foreach ((array) $messages as $message) {
                $safeType = preg_replace('/[^a-z0-9_-]/i', '', (string) $type) ?: 'info';
                echo Html::tag('div', Html::encode($message), [
                    'class' => 'admin-login-flash admin-login-flash--' . $safeType,
                    'role' => 'alert',
                ]);
            }
        }
        ?>
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
