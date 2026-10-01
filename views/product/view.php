<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var bool $canReview Можно ли текущему пользователю оставить отзыв (заказ + ещё нет отзыва) */
/** @var bool $userHasOrderedProduct Пользователь заказывал этот товар */
/** @var bool $userAlreadyReviewed У пользователя уже есть отзыв на товар */

$this->registerCssFile('@web/css/product.css');
?>

<div class="product-page">
    <div class="product-detail">
        <div class="product-gallery">
            <div class="product-main-image">
                <?= Html::img('/images/' . ($product->image_product ?: 'default-product.jpg'), [
                    'alt' => $product->name,
                    'class' => 'main-image'
                ]) ?>
            </div>
        </div>
        
        <div class="product-info">
            <h1><?= Html::encode($product->name) ?></h1>
            
            <div class="product-meta">
                <div class="product-category">
                    Категория: <?= Html::encode($product->category->category_name) ?>
                </div>
                
                <div class="product-availability">
                    <?php if ($product->quantity > 0): ?>
                        <span class="in-stock">В наличии: <?= $product->quantity ?> шт.</span>
                    <?php else: ?>
                        <span class="out-of-stock">Нет в наличии</span>
                    <?php endif; ?>
                </div>
            </div>
            <?= $this->render('_price_block', ['product' => $product]) ?>
            
            <div class="product-actions">
                <?php if ($product->quantity > 0): ?>
                    <button class="add-to-cart-btn add-to-cart" data-id="<?= $product->id_product ?>">
                        В корзину
                    </button>
                <?php else: ?>
                    <button class="add-to-cart-btn disabled" disabled>Нет в наличии</button>
                <?php endif; ?>
                
                <button class="favorite-btn <?= \app\models\Favorite::isFavorite($product->id_product) ? 'active' : '' ?>" 
                        data-id="<?= $product->id_product ?>">
                    ♥
                </button>
            </div>
            
            <div class="product-description">
                <h3>Описание</h3>
                <p><?= nl2br(Html::encode($product->description)) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Отзывы -->
    <div id="product-reviews" class="product-reviews">
        <h2>Отзывы</h2>

        <?php if (!Yii::$app->user->isGuest): ?>
            <?php if (!empty($canReview)): ?>
                <div class="add-review">
                    <h3>Оставить отзыв</h3>
                    <p class="add-review-hint">Вы заказывали этот товар — поделитесь впечатлениями.</p>
                    <?php $form = ActiveForm::begin([
                        'action' => ['product/add-review'],
                        'method' => 'post',
                    ]); ?>

                    <?= Html::hiddenInput('product_id', $product->id_product) ?>

                    <div class="rating-select">
                        <label>Оценка:</label>
                        <select name="rating" class="rating-input">
                            <option value="5">5 ★</option>
                            <option value="4">4 ★</option>
                            <option value="3">3 ★</option>
                            <option value="2">2 ★</option>
                            <option value="1">1 ★</option>
                        </select>
                    </div>

                    <div class="review-text">
                        <label>Отзыв:</label>
                        <textarea name="text" rows="4" class="review-input" required></textarea>
                    </div>

                    <?= Html::submitButton('Отправить', ['class' => 'submit-review']) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            <?php else: ?>
                <div class="add-review add-review--locked">
                    <?php if (!empty($userAlreadyReviewed)): ?>
                        <p class="add-review-locked-text">Вы уже оставляли отзыв на этот товар. После проверки модератором он отображается в списке ниже (если одобрен).</p>
                    <?php elseif (empty($userHasOrderedProduct)): ?>
                        <p class="add-review-locked-text">Отзыв можно оставить только на товар, который вы заказывали. Оформите заказ в каталоге — после этого здесь появится форма отзыва.</p>
                    <?php else: ?>
                        <p class="add-review-locked-text">Сейчас отзыв для этого товара недоступен.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="review-author"><?= Html::encode($review->user->full_name) ?></span>
                        <span class="review-date"><?= Yii::$app->formatter->asDate($review->created_at) ?></span>
                    </div>
                    <div class="review-stars">
                        <?= str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating) ?>
                    </div>
                    <div class="review-text">
                        <?= nl2br(Html::encode($review->text)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<!-- Похожие товары (упрощенный вариант) -->
<?php if (!empty($relatedProducts)): ?>
<div class="related-products product-view-related">
    <h2>Похожие товары</h2>
    <div class="related-grid product-view-related-grid">
        <?php foreach ($relatedProducts as $related): ?>
            <div class="related-item product-view-related-item">
                <a href="<?= Url::to(['product/view', 'id' => $related->id_product]) ?>" class="product-view-related-link">
                    <div class="related-image product-view-related-image">
                        <?= Html::img('/images/' . ($related->image_product ?: 'no-image.jpg'), [
                            'alt' => $related->name
                        ]) ?>
                    </div>
                    <div class="related-name product-view-related-name">
                        <?= Html::encode($related->name) ?>
                    </div>
                </a>
                
                <!-- БЛОК ЦЕНЫ С ИСПОЛЬЗОВАНИЕМ МЕТОДОВ МОДЕЛИ -->
                <div class="product-view-related-price">
                    <?php if ($related->hasAnyDiscount()): ?>
                        <?php if ($related->getDisplayOldPrice()): ?>
                            <span class="product-view-old-price">
                                <?= number_format($related->getDisplayOldPrice(), 0, '', ' ') ?> ₽
                            </span>
                        <?php endif; ?>
                        <span class="product-view-new-price">
                            <?= number_format($related->getFinalPrice(), 0, '', ' ') ?> ₽
                        </span>
                        <?php if ($related->getDiscountPercent() > 0): ?>
                            <span class="product-view-discount-badge">
                                -<?= $related->getDiscountPercent() ?>%
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="product-view-regular-price">
                            <?= number_format($related->getFinalPrice(), 0, '', ' ') ?> ₽
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($related->quantity > 0): ?>
                    <button class="add-to-cart-btn-small add-to-cart" data-id="<?= $related->id_product ?>">
                        В корзину
                    </button>
                <?php else: ?>
                    <button class="add-to-cart-btn-small disabled" disabled>Нет в наличии</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>