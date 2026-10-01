<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->registerCssFile('@web/css/favorite-page.css');

$n = count($favorites);
if ($n % 10 === 1 && $n % 100 !== 11) {
    $favoritesCountLabel = $n . ' товар';
} elseif (in_array($n % 10, [2, 3, 4], true) && !in_array($n % 100, [12, 13, 14], true)) {
    $favoritesCountLabel = $n . ' товара';
} else {
    $favoritesCountLabel = $n . ' товаров';
}
?>
<div class="favorites-page">
    <header class="favorites-header">
        <h1>Избранное</h1>
        <?php if (!empty($favorites)): ?>
            <p class="favorites-subtitle"><?= Html::encode($favoritesCountLabel) ?></p>
        <?php endif; ?>
    </header>

    <?php if (empty($favorites)): ?>
        <div class="empty-favorites">
            <p>У вас пока нет избранных товаров</p>
            <a href="<?= Url::to(['/catalog']) ?>" class="btn">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $favorite): ?>
                <?php $product = $favorite->product; ?>
                <?php if ($product): ?>
                    <article class="favorite-item" data-id="<?= $product->id_product ?>">
                        <button
                            type="button"
                            class="remove-favorite"
                            data-id="<?= $product->id_product ?>"
                            aria-label="Удалить из избранного"
                        >×</button>

                        <a href="<?= Url::to(['product/view', 'id' => $product->id_product]) ?>">
                            <div class="product-image">
                                <?= Html::img('/images/' . ($product->image_product ?: 'no-image.jpg'), [
                                    'alt' => $product->name,
                                    'loading' => 'lazy',
                                ]) ?>
                            </div>

                            <div class="product-name"><?= Html::encode($product->name) ?></div>

                            <div class="price-block">
                                <?php
                                $hasPromo = $product->hasPromoDiscount();
                                $hasRegularDiscount = ($product->is_discount == 1 && $product->old_price > 0);

                                if ($hasPromo):
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
                                    <span class="old-price"><?= number_format($basePrice, 0, '', ' ') ?> ₽</span>
                                    <span class="new-price"><?= number_format($finalPrice, 0, '', ' ') ?> ₽</span>
                                    <span class="discount-badge">-<?= $discountPercent ?>%</span>
                                <?php elseif ($hasRegularDiscount): ?>
                                    <span class="old-price"><?= number_format($product->old_price, 0, '', ' ') ?> ₽</span>
                                    <span class="new-price"><?= number_format($product->price, 0, '', ' ') ?> ₽</span>
                                    <?php
                                    $discountPercent = round((1 - $product->price / $product->old_price) * 100);
                                    ?>
                                    <span class="discount-badge">-<?= $discountPercent ?>%</span>
                                <?php else: ?>
                                    <span class="regular-price"><?= number_format($product->price, 0, '', ' ') ?> ₽</span>
                                <?php endif; ?>
                            </div>
                        </a>

                        <?php if ($product->quantity > 0): ?>
                            <button type="button" class="add-to-cart" data-id="<?= $product->id_product ?>">В корзину</button>
                        <?php else: ?>
                            <button type="button" class="disabled" disabled>Нет в наличии</button>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
