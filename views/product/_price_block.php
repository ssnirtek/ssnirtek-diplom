<?php
/** @var app\models\Product $product */
?>
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
    <span class="discount-badge">-<?= round((1 - $product->price / $product->old_price) * 100) ?>%</span>
<?php else: ?>
    <span class="regular-price"><?= number_format($product->price, 0, '', ' ') ?> ₽</span>
<?php endif; ?>
</div>
