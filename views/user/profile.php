<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var app\models\User $model */
/** @var app\models\Orders[] $lastOrders */
/** @var app\models\UserUpdateForm $updateForm */
/** @var app\models\Product[] $reviewableProducts */

$this->title = 'Личный кабинет';
$this->registerCssFile('@web/css/user-profile.css');
?>

<div class="user-profile">
    <div class="row">
        <div class="col-md-3">
            <div class="profile-sidebar">
                <div class="sidebar-header">
                    <h5 class="sidebar-title">Меню</h5>
                </div>
                <div class="sidebar-menu">
                    <?= Html::a('Личный кабинет', ['profile'], ['class' => 'menu-item active']) ?>
                    <?= Html::a('Мои заказы', ['orders'], ['class' => 'menu-item']) ?>
                    <?= Html::a('Новый заказ', ['catalog/index'], ['class' => 'menu-item new-order']) ?>
                </div>
                <div class="user-info">
                    <div class="info-content">
                        <p><span>Регистрация:</span> <?= Yii::$app->formatter->asDate($model->created_at) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert-success-message"><?= Yii::$app->session->getFlash('success') ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert-error-message"><?= Yii::$app->session->getFlash('error') ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('info')): ?>
                <div class="alert-info-message"><?= Yii::$app->session->getFlash('info') ?></div>
            <?php endif; ?>

            <?php if (!empty($reviewableProducts)): ?>
                <div class="reviewable-products-card">
                    <h4 class="reviewable-products-title">Оставить отзыв</h4>
                    <p class="reviewable-products-lead">Товары из ваших заказов, на которые ещё нет вашего отзыва:</p>
                    <ul class="reviewable-products-list">
                        <?php foreach ($reviewableProducts as $rp): ?>
                            <li class="reviewable-products-item">
                                <a href="<?= Url::to(['/product/view', 'id' => $rp->id_product, '#' => 'product-reviews']) ?>" class="reviewable-products-link">
                                    <span class="reviewable-products-thumb">
                                        <?= Html::img('/images/' . ($rp->image_product ?: 'no-image.jpg'), [
                                            'alt' => '',
                                            'width' => 48,
                                            'height' => 48,
                                            'loading' => 'lazy',
                                        ]) ?>
                                    </span>
                                    <span class="reviewable-products-name"><?= Html::encode($rp->name) ?></span>
                                    <span class="reviewable-products-cta">Оставить отзыв</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->user->identity->email_confirm_token !== null): ?>
                <div class="email-confirm-warning">
                    <p class="warning-text">Ваш email ещё не подтверждён. Некоторые функции могут быть недоступны.</p>
                    <?= Html::a('Отправить подтверждение повторно', ['user/resend-confirmation'], ['class' => 'email-confirm-resend-btn']) ?>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <h4 class="card-title">Мой профиль</h4>

                <div class="info-table profile-readonly">
                    <div class="info-row">
                        <span class="info-label">ФИО:</span>
                        <span class="info-value"><?= Html::encode($model->full_name) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Дата рождения:</span>
                        <span class="info-value"><?= $model->date_born ? Yii::$app->formatter->asDate($model->date_born) : '—' ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= Html::encode($model->email) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Телефон:</span>
                        <span class="info-value"><?= Html::encode($model->phone ?: '—') ?></span>
                    </div>
                </div>

                <div class="profile-edit-block">
                    <h5 class="profile-edit-title">Изменить email и телефон</h5>
                    <p class="profile-edit-lead">Укажите актуальные контакты — они используются при оформлении заказов.</p>

                    <?php $form = ActiveForm::begin([
                        'action' => ['update-profile'],
                        'method' => 'post',
                        'options' => ['class' => 'edit-form'],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'form-label'],
                            'inputOptions' => ['class' => 'form-input'],
                            'errorOptions' => ['class' => 'form-error'],
                        ],
                    ]); ?>

                    <?= $form->field($updateForm, 'email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'example@mail.ru',
                        'autocomplete' => 'email',
                    ]) ?>

                    <?= $form->field($updateForm, 'phone')->textInput([
                        'type' => 'tel',
                        'placeholder' => '+7 (999) 123-45-67',
                        'autocomplete' => 'tel',
                    ]) ?>

                    <div class="profile-edit-actions">
                        <?= Html::submitButton('Сохранить изменения', ['class' => 'submit-btn']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="new-order-block">
                <?= Html::a('Оформить новый заказ', ['catalog/index'], ['class' => 'new-order-btn']) ?>
            </div>

            <div class="orders-card">
                <div class="orders-header">
                    <h5 class="orders-title">Мои последние заказы</h5>
                    <?= Html::a('Все заказы →', ['orders'], ['class' => 'all-orders-link']) ?>
                </div>
                <div class="orders-content">
                    <?php if (empty($lastOrders)): ?>
                        <div class="no-orders">
                            У вас пока нет заказов.
                            <?= Html::a('Сделать первый заказ', ['catalog/index']) ?>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>№ заказа</th>
                                        <th>Дата</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lastOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order->id_orders ?></td>
                                            <td><?= Yii::$app->formatter->asDatetime($order->created_at, 'php:d.m.Y H:i') ?></td>
                                            <td class="order-amount"><?= Yii::$app->formatter->asCurrency($order->total_amount, 'RUB') ?></td>
                                            <td>
                                                <span class="status-badge <?= $order->getStatusClass() ?>"><?= Html::encode($order->getStatusLabel()) ?></span>
                                            </td>
                                            <td>
                                                <?= Html::a('Просмотр', ['order-view', 'id' => $order->id_orders], ['class' => 'view-link']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
