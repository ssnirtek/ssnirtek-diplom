<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

?>

<div class="product-minimal-grid">
    <?php foreach ($dataProvider->models as $product): ?>
        <div class="product-minimal-card">

            <!-- Бейдж скидки -->
            <?php if ($product->hasPromoDiscount()): ?>
                <div class="discount-minimal-badge">
                    -<?= $product->getPromoDiscountPercent() ?>%
                </div>
            <?php endif; ?>

            <!-- Кнопка избранного -->
            <button class="favorite-minimal-btn <?= \app\models\Favorite::isFavorite($product->id_product) ? 'active' : '' ?>"
                    data-id="<?= $product->id_product ?>">
                ♥
            </button>

            <!-- Ссылка на товар -->
            <a href="<?= Url::to(['product/view', 'id' => $product->id_product]) ?>"
               class="product-minimal-link">

                <div class="product-minimal-image">
                    <img src="/images/<?= Html::encode($product->image_product ?: 'no-image.jpg') ?>"
                         alt="<?= Html::encode($product->name) ?>">
                </div>

                <div class="product-minimal-name">
                    <?= Html::encode($product->name) ?>
                </div>
                
                <?= $this->render('//product/_price_block', ['product' => $product]) ?>

            </a>

            <!-- Кнопка корзины -->
            <?php if ($product->quantity > 0): ?>
                <button class="cart-minimal-btn add-to-cart"
                        data-id="<?= $product->id_product ?>">
                    В корзину
                </button>
            <?php else: ?>
                <button class="cart-minimal-btn disabled" disabled>
                    Нет в наличии
                </button>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>

<?php if ($dataProvider->pagination->getPageCount() > 1): ?>
<div class="pagination-wrapper">
    <?= LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'options' => [
            'class' => 'pagination-minimal',
            'role' => 'navigation',
            'aria-label' => 'Навигация по страницам каталога',
        ],
        'linkOptions' => ['class' => 'pagination-minimal__link'],
        'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'pagination-minimal__link pagination-minimal__link--disabled'],
        'pageCssClass' => 'pagination-minimal__page',
        'prevPageLabel' => '‹',
        'nextPageLabel' => '›',
        'maxButtonCount' => 7,
        'hideOnSinglePage' => true,
    ]) ?>
</div>
<?php endif; ?>