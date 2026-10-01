<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'Вход в админ-панель';
?>

<h1 class="admin-login-title">Админ-панель Businka</h1>
<p class="admin-login-lead">Войдите под учётной записью администратора</p>

<?php $form = ActiveForm::begin([
    'id' => 'admin-login-form',
    'options' => ['class' => 'admin-login-form'],
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}",
        'labelOptions' => ['class' => 'admin-form-label'],
        'inputOptions' => ['class' => 'admin-form-control'],
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ],
]); ?>

<?= $form->field($model, 'email')->textInput([
    'autofocus' => true,
    'autocomplete' => 'username',
    'placeholder' => 'Email',
]) ?>

<?= $form->field($model, 'password')->passwordInput([
    'autocomplete' => 'current-password',
    'placeholder' => 'Пароль',
]) ?>

<?= $form->field($model, 'rememberMe')->checkbox([
    'template' => "<div class=\"admin-login-checkbox\">{input} {label}</div>\n{error}",
]) ?>

<div class="admin-login-actions">
    <?= Html::submitButton('Войти', ['class' => 'admin-btn admin-btn--primary admin-btn--block', 'name' => 'login-button']) ?>
</div>

<?php ActiveForm::end(); ?>

<p class="admin-login-footer">
    <?= Html::a('← На сайт', ['/site/index'], ['class' => 'admin-login-link']) ?>
</p>
