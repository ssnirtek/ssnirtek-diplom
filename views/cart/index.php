<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $items */
/** @var app\models\Cart $cart */

$this->registerCssFile('@web/css/cart-page.css');
?>

<div class="cart-page">
<?php if (empty($items)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">
            <img src="<?= Yii::getAlias('@web/images/cab.png') ?>" 
                 alt="Пустая корзина" 
                 class="empty-cart-image">
        </div>
        <p class="cart-empty-text">Ваша корзина пуста</p>
        <p class="cart-empty-subtext">Но это никогда не поздно исправить :)</p>
        <a href="<?= Url::to(['/catalog']) ?>" class="cart-empty-btn">Перейти в каталог</a>
    </div>
<?php else: ?>
        <?php
        $cartSubtotal = $cart->getSubtotalAfterPromos();
        $cartWelcome = $cart->getWelcomeDiscountAmount();
        $cartToPay = $cart->getAmountToPay();
        ?>
        <div class="cart-layout">
            <!-- Список товаров -->
            <div class="cart-items" id="cart-items">
                <?php foreach ($items as $item): ?>
                    <?php $product = $item->product; ?>
                    <div class="cart-item" data-id="<?= $item->id ?>" data-product-id="<?= $product->id_product ?>"
                         data-unit-price="<?= (float) $item->getFinalPrice() ?>">
                        <!-- Бейджи -->
                        <div class="cart-item-badges">
                            <?php 
                            $hasPromo = $product->hasPromoDiscount();
                            $hasRegularDiscount = ($product->is_discount == 1 && $product->old_price > 0);
                            
                            if ($hasPromo): 
                                $discountPercent = $product->getPromoDiscountPercent();
                            ?>
                                <span class="badge badge-promo">Акция -<?= $discountPercent ?>%</span>
                            <?php elseif ($hasRegularDiscount): 
                                $discountPercent = round((1 - $product->price / $product->old_price) * 100);
                            ?>
                                <span class="badge badge-sale">Скидка <?= $discountPercent ?>%</span>
                            <?php endif; ?>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="cart-item-actions">
                            <button class="action-btn favorite-btn <?= \app\models\Favorite::isFavorite($product->id_product) ? 'active' : '' ?>" 
                                    data-id="<?= $product->id_product ?>" 
                                    title="В избранное">
                                <span class="heart">♥</span>
                            </button>
                            <button class="action-btn remove-item" 
                                    data-id="<?= $product->id_product ?>" 
                                    title="Удалить">
                                <span class="remove">×</span>
                            </button>
                        </div>

                        <!-- Контент товара -->
                        <div class="cart-item-content">
                            <!-- Ссылка на товар -->
                            <div class="cart-item-main">
                                <a href="<?= Url::to(['product/view', 'id' => $product->id_product]) ?>" class="cart-item-link">
                                    <div class="cart-item-image">
                                        <?= Html::img('/images/' . ($product->image_product ?: 'no-image.jpg'), [
                                            'alt' => $product->name
                                        ]) ?>
                                    </div>
                                    <div class="cart-item-name"><?= Html::encode($product->name) ?></div>
                                </a>
                            </div>
                            
                            <!-- Цена - ИСПРАВЛЕННЫЙ БЛОК -->
                            <div class="cart-item-price">
                                <?php 
                                $hasPromo = $product->hasPromoDiscount();
                                $hasRegularDiscount = ($product->is_discount == 1 && $product->old_price > 0);
                                
                                if ($hasPromo): 
                                    // Получаем правильные цены из модели
                                    $promoInfo = $product->getPromoPriceInfo();
                                    
                                    if ($promoInfo) {
                                        $basePrice = $promoInfo['base_price'];
                                        $finalPrice = $promoInfo['final_price'];
                                        $discountPercent = $promoInfo['discount_percent'];
                                    } else {
                                        $basePrice = $hasRegularDiscount ? $product->old_price : $product->price;
                                        $discountPercent = $product->getPromoDiscountPercent();
                                        $finalPrice = $basePrice * (100 - $discountPercent) / 100;
                                    }
                                ?>
                                    <div class="price-block">
                                        <span class="old-price"><?= number_format($basePrice, 0, '', ' ') ?> ₽</span>
                                        <span class="new-price"><?= number_format($finalPrice, 0, '', ' ') ?> ₽</span>
                                        <span class="discount-badge">-<?= $discountPercent ?>%</span>
                                    </div>
                                    
                                <?php elseif ($hasRegularDiscount): 
                                    $discountPercent = round((1 - $product->price / $product->old_price) * 100);
                                ?>
                                    <div class="price-block">
                                        <span class="old-price"><?= number_format($product->old_price, 0, '', ' ') ?> ₽</span>
                                        <span class="new-price"><?= number_format($product->price, 0, '', ' ') ?> ₽</span>
                                        <span class="discount-badge">-<?= $discountPercent ?>%</span>
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="price-block">
                                        <span class="regular-price"><?= number_format($product->price, 0, '', ' ') ?> ₽</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Управление количеством -->
                            <div class="cart-item-controls">
                                <div class="quantity-selector">
                                    <button class="quantity-btn minus" data-id="<?= $item->id ?>">−</button>
                                    <input type="number" class="quantity-input" 
                                           value="<?= $item->quantity ?>" 
                                           min="1" 
                                           max="<?= $product->quantity ?>" 
                                           data-id="<?= $item->id ?>"
                                           readonly>
                                    <button class="quantity-btn plus" data-id="<?= $item->id ?>">+</button>
                                </div>
                                
                                <div class="cart-item-total">
                                    <span class="total-label">Итого:</span>
                                    <span class="total-value item-total" id="item-total-<?= $item->id ?>">
                                        <?= number_format($item->getTotalPrice(), 0, '', ' ') ?> ₽
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Боковая панель с итогами -->
            <div class="cart-sidebar">
                <div class="cart-summary-card"
                     id="cart-summary-root"
                     data-welcome-active="<?= $cart->isWelcomeDiscountApplicable() ? '1' : '0' ?>"
                     data-welcome-percent="<?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>">
                    <h3 class="summary-title">Ваш заказ</h3>

                    <div class="summary-row summary-row-goods">
                        <span class="summary-label">Товары (<?= $cart->getTotalCount() ?> шт.)</span>
                        <span class="summary-value" id="cart-subtotal"><?= number_format($cartSubtotal, 0, '', ' ') ?> ₽</span>
                    </div>

                    <?php if ($cart->isWelcomeDiscountApplicable()): ?>
                        <div class="summary-row summary-welcome" id="cart-welcome-row">
                            <span class="summary-label">Скидка <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_PERCENT ?>% (первые <?= (int) \app\models\User::WELCOME_ORDER_DISCOUNT_MAX_ORDERS ?> заказа)</span>
                            <span class="summary-value welcome-value" id="cart-welcome-discount">− <?= number_format($cartWelcome, 0, '', ' ') ?> ₽</span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-total">
                        <span>Итого к оплате</span>
                        <span class="total-amount" id="total-sum">
                            <?= number_format($cartToPay, 0, '', ' ') ?> ₽
                        </span>
                    </div>

                    <a href="<?= Url::to(['/orders/create']) ?>" class="checkout-btn">
                        Оформить заказ
                        <span class="btn-arrow">→</span>
                    </a>

                    <div class="payment-info">
                        <span>Принимаем к оплате</span>
                        <div class="payment-icons">
                            <span class="payment-icon visa">Visa</span>
                            <span class="payment-icon mastercard">Mastercard</span>
                            <span class="payment-icon mir">Мир</span>
                        </div>
                    </div>
                </div>

                <a href="<?= Url::to(['/catalog']) ?>" class="continue-shopping">
                    <span class="arrow">←</span>
                    Продолжить покупки
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
