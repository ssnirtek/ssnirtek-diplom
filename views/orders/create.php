<?php

use yii\helpers\Html;

use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Orders $order */
/** @var app\models\Cart $cart */
/** @var app\models\CartItem[] $cartItems */

$this->title = 'Оформление заказа';
$this->registerCssFile('@web/css/order-create.css');

$checkoutSubtotal = $cart->getSubtotalAfterPromos();
$checkoutWelcome = $cart->getWelcomeDiscountAmount();
$checkoutToPay = $cart->getAmountToPay();
?>

<div class="order-page">
    <div class="order-header">
        <h1>Оформление заказа</h1>
        <a href="<?= Url::to(['/cart']) ?>" class="back-link">← Вернуться в корзину</a>
    </div>

    <div class="order-container">
        <!-- Список товаров -->
        <div class="order-products">
            <h2 class="order-section-title">Ваш заказ</h2>
            
            <div class="order-items-list">
                <?php foreach ($cartItems as $item): ?>
                    <?php $product = $item->product; ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <?= Html::img('/images/' . ($product->image_product ?: 'no-image.jpg'), [
                                'alt' => $product->name
                            ]) ?>
                        </div>
                        <div class="order-item-info">
                            <div class="order-item-name"><?= Html::encode($product->name) ?></div>
                            <div class="order-item-price">
                                <?= number_format($product->getFinalPrice(), 0, '', ' ') ?> ₽ × <?= $item->quantity ?>
                            </div>
                        </div>
                        <div class="order-item-total">
                            <?= number_format($item->quantity * $product->getFinalPrice(), 0, '', ' ') ?> ₽
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-totals-breakdown">
                <div class="order-total-line">
                    <span>Сумма товаров</span>
                    <span><?= number_format($checkoutSubtotal, 0, '', ' ') ?> ₽</span>
                </div>
                <?php if ($checkoutWelcome > 0): ?>
                    <div class="order-total-line order-total-discount">
                        <span>Скидка <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>% (первые <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_MAX_ORDERS ?> заказа)</span>
                        <span>− <?= number_format($checkoutWelcome, 0, '', ' ') ?> ₽</span>
                    </div>
                <?php endif; ?>
                <div class="order-total order-total-final">
                    <span>К оплате:</span>
                    <strong><?= number_format($checkoutToPay, 0, '', ' ') ?> ₽</strong>
                </div>
            </div>
        </div>

        <!-- Форма оформления -->
        <div class="order-form">
            <h2 class="order-section-title">Контактные данные</h2>
            
            <?php $form = ActiveForm::begin([
                'id' => 'order-form',
                'options' => ['class' => 'order-form-content'],
            ]); ?>

            <div class="form-group">
                <?= $form->field($order, 'full_name')->textInput([
                    'placeholder' => 'Иванов Иван Иванович',
                    'value' => Yii::$app->user->identity->full_name,
                ])->label('ФИО получателя') ?>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <?= $form->field($order, 'phone')->textInput([
                        'placeholder' => '+7 (999) 999-99-99',
                        'value' => Yii::$app->user->identity->phone,
                    ])->label('Телефон') ?>
                </div>
                <div class="form-group half">
                    <?= $form->field($order, 'email')->textInput([
                        'placeholder' => 'example@mail.com',
                        'value' => Yii::$app->user->identity->email,
                    ])->label('Email') ?>
                </div>
            </div>

            <div class="form-group">
                <?= $form->field($order, 'address')->textarea([
                    'placeholder' => 'Город, улица, дом, квартира',
                    'rows' => 3,
                ])->label('Адрес доставки') ?>
            </div>

            <div class="form-group">
                <?= $form->field($order, 'comment')->textarea([
                    'placeholder' => 'Комментарий к заказу (необязательно)',
                    'rows' => 3,
                ])->label('Комментарий') ?>
            </div>

            <div class="form-group payment-method">
                <label>Способ оплаты</label>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment" value="cash" checked>
                        <span class="payment-option-content">
                            <span class="payment-icon">💵</span>
                            <span class="payment-text">Наличными при получении</span>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="card">
                        <span class="payment-option-content">
                            <span class="payment-icon">💳</span>
                            <span class="payment-text">Картой при получении</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-group delivery-method">
                <label>Способ доставки</label>
                <div class="delivery-options">
                    <label class="delivery-option">
                        <input type="radio" name="delivery" value="courier" checked>
                        <span class="delivery-option-content">
                            <span class="delivery-icon">🚚</span>
                            <span class="delivery-text">
                                <strong>Курьером</strong>
                                <span>по Санкт-Петербургу</span>
                            </span>
                        </span>
                    </label>
                    <label class="delivery-option">
                        <input type="radio" name="delivery" value="pickup">
                        <span class="delivery-option-content">
                            <span class="delivery-icon">🏪</span>
                            <span class="delivery-text">
                                <strong>Самовывоз</strong>
                                <span>м. Беговая, Приморский пр.</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <?= Html::submitButton('Подтвердить заказ', [
                    'class' => 'submit-order-btn',
                    'name' => 'submit-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>