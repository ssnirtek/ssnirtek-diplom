<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Promo $model */

$this->title = 'Редактирование акции: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Акции', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id_promo' => $model->id_promo]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="promo-update">
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