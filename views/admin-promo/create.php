<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Promo $model */

$this->title = 'Создание акции';
$this->params['breadcrumbs'][] = ['label' => 'Акции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="promo-create">
    <div class="card">
        <div class="card-header bg-primary1 text-white">
            <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>