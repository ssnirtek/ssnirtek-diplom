<?php

/** @var yii\web\View $this */
/** @var app\models\Promo[] $promos */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\Query;

$this->title = 'Главная';

// Получаем последние 3 товара через QueryBuilder Yii2 с подгрузкой акций
$products = (new Query())
    ->select([
        'p.id_product', 
        'p.name', 
        'p.description', 
        'p.price', 
        'p.image_product', 
        'p.old_price', 
        'p.is_discount',
        'p.quantity',
        'c.category_name'
    ])
    ->from('product p')
    ->leftJoin('category c', 'p.category_id = c.id_category')
    ->where(['p.is_active' => 1])
    ->orderBy(['p.created_at' => SORT_DESC])
    ->limit(3)
    ->all();

// Загружаем активные акции для товаров
$productIds = array_column($products, 'id_product');
$promoProducts = [];

if (!empty($productIds)) {
    $promoProducts = (new Query())
        ->select(['pp.product_id', 'p.discount_percent'])
        ->from('promo_product pp')
        ->leftJoin('promo p', 'pp.promo_id = p.id_promo')
        ->where([
            'pp.product_id' => $productIds,
            'p.is_active' => 1,
            'p.start_date' => date('Y-m-d')
        ])
        ->all();
    
    // Индексируем по product_id
    $promoByProduct = [];
    foreach ($promoProducts as $pp) {
        $promoByProduct[$pp['product_id']] = $pp['discount_percent'];
    }
}

// Получаем последние отзывы
$latestReviews = (new Query())
    ->select([
        'r.id_review', 
        'r.text', 
        'r.rating', 
        'r.created_at', 
        'r.user_id',
        'p.id_product', 
        'p.name as product_name', 
        'p.image_product',
        'u.full_name'
    ])
    ->from('reviews r')
    ->leftJoin('product p', 'r.product_id = p.id_product')
    ->leftJoin('user u', 'r.user_id = u.id_user')
    ->where(['r.is_approved' => 1])
    ->orderBy(['r.created_at' => SORT_DESC])
    ->limit(5)
    ->all();

$this->registerCssFile('@web/css/index-page.css');
$this->registerJs(
    <<<'JS'
(function () {
    var wrap = document.querySelector('.fullscreen-about .about-image');
    if (!wrap) {
        return;
    }
    function reveal() {
        wrap.classList.add('about-image--inview');
    }
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        reveal();
        return;
    }
    if (!('IntersectionObserver' in window)) {
        reveal();
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }
            reveal();
            io.unobserve(wrap);
        });
    }, { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.14 });
    io.observe(wrap);
})();
JS
    ,
    \yii\web\View::POS_END
);
?>

<div class="site-index">
    
    <!-- Блок с изображением на весь экран -->
    <div class="fullscreen-banner">
        <div class="banner-content">
            <a href="<?= Url::to(['catalog/index']) ?>" class="banner-link">
                Перейти в каталог
            </a>
        </div>
    </div>
    
    <!-- Блок новинок товаров -->
    <div class="new-products-section">
        <div class="container">
            <div class="section-header">
                <h2>Новинки</h2>
                <a href="<?= Url::to(['catalog/index']) ?>" class="catalog-link">Перейти в каталог</a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="empty-products">
                        Товары временно отсутствуют
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-product-id="<?= $product['id_product']; ?>">
                            <!-- Вся карточка обернута в ссылку -->
                            <a href="<?= Url::to(['product/view', 'id' => $product['id_product']]) ?>" class="product-card-link">
                                <div class="product-image">
                                    <?php 
                                    $imageName = !empty($product['image_product']) ? $product['image_product'] : 'no-image.jpg';
                                    $imagePath = '/images/' . $imageName;
                                    $fullImagePath = Yii::getAlias('@webroot') . $imagePath;
                                    ?>
                                    
                                    <?php if (!empty($product['image_product']) && file_exists($fullImagePath)): ?>
                                        <img src="<?= Html::encode($imagePath); ?>" 
                                             alt="<?= Html::encode($product['name']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <img src="/images/no-image.jpg" 
                                             alt="Нет изображения"
                                             loading="lazy">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <div class="product-category">
                                        <?= Html::encode($product['category_name'] ?? 'Без категории'); ?>
                                    </div>
                                    
                                    <div class="product-title">
                                        <?= Html::encode($product['name']); ?>
                                    </div>
                                    
                                    <div class="product-description">
                                        <?= Html::encode($product['description']); ?>
                                    </div>
                                </div>
                            </a>
                            
 <!-- УНИВЕРСАЛЬНЫЙ БЛОК ЦЕНЫ -->
<div class="product-price-block">
    <?php 
    // Проверяем наличие акции (из promo_product)
    $hasPromo = isset($promoByProduct[$product['id_product']]);
    // Проверяем наличие обычной скидки
    $hasRegularDiscount = ($product['is_discount'] == 1 && !empty($product['old_price']) && $product['old_price'] > $product['price']);
    
    if ($hasPromo): 
        // Скидка по акции (как на странице товара)
        $discountPercent = $promoByProduct[$product['id_product']];
        $finalPrice = $product['price'] * (100 - $discountPercent) / 100;
        $oldPrice = $product['price'];
    ?>
        <span class="old-price"><?= number_format($oldPrice, 0, '', ' ') ?> ₽</span>
        <span class="new-price"><?= number_format($finalPrice, 0, '', ' ') ?> ₽</span>
        <span class="discount-badge">-<?= $discountPercent ?>%</span>
        
    <?php elseif ($hasRegularDiscount): 
        // Обычная скидка (как на странице товара)
        $discountPercent = round((1 - $product['price'] / $product['old_price']) * 100);
    ?>
        <span class="old-price"><?= number_format($product['old_price'], 0, '', ' ') ?> ₽</span>
        <span class="new-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
        <span class="discount-badge">-<?= $discountPercent ?>%</span>
        
    <?php else: ?>
        <!-- Обычная цена -->
        <span class="regular-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
    <?php endif; ?>
</div>

                            
                            <!-- Кнопка добавления в корзину -->
                            <?php if ($product['quantity'] > 0): ?>
                                <button type="button" class="add-to-cart-btn add-to-cart" data-id="<?= $product['id_product']; ?>">
                                    Добавить в корзину
                                </button>
                            <?php else: ?>
                                <button type="button" class="add-to-cart-btn disabled" disabled>
                                    Нет в наличии
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
   
    <!-- Промо-блок -->
    <div class="promo-banner1">
        <div class="promo-scroll1">
            <div class="promo-track1">
                <?php for ($i = 0; $i < 15; $i++): ?>
                    <span class="promo-text1">Твоё сияние в - каждой бусине</span>
                <?php endfor; ?>
            </div>
        </div>
    </div> 
    
    <!-- Полноэкранный блок "О нас" -->
    <div class="fullscreen-about">
        <div class="fullscreen-about-container">
            <div class="fullscreen-about-wrapper">
                <div class="about-image">
                    <img src="/images/fon.png" alt="Украшения из бисера">
                </div>
                
                <div class="about-content">
                    <h1 class="about-main-title">Businka</h1>
                    <p class="about-slogan">Украшения из бисера ручной работы</p>
                    
                    <div class="about-description">
                        <p>Добро пожаловать в <strong>Businka</strong> — магазин уникальных украшений из бисера ручной работы. 
                        Мы создаем изделия с душой и любовью, уделяя внимание каждой детали и используя только качественные материалы: чешский и японский бисер, 
                        натуральные камни, фурнитуру с покрытием под золото и серебро.</p>
                        
                        <p>Здесь вы найдете серьги, браслеты, колье, кольца и другие аксессуары, которые подчеркнут вашу индивидуальность. Все украшения уникальны и существуют в единственном экземпляре</p>
                    </div>
                    
                    <div class="about-button-wrapper">
                        <a href="<?= Url::to(['/site/about']) ?>" class="about-btn">Подробнее о нас →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Отзывы с фото товара -->
    <div class="reviews-fullwidth">
        <div class="reviews-container">
            <h2 class="reviews-full-title">Отзывы наших клиентов</h2>
            
            <?php if (!empty($latestReviews)): ?>
                <div class="reviews-full-grid">
                    <?php foreach (array_slice($latestReviews, 0, 5) as $review): ?>
                        <div class="review-full-card">
                            <div class="review-full-image">
                                <?php 
                                $imagePath = '';
                                $hasImage = false;
                                
                                if (!empty($review['image_product'])) {
                                    $fullImagePath = Yii::getAlias('@webroot') . '/images/' . $review['image_product'];
                                    if (file_exists($fullImagePath)) {
                                        $hasImage = true;
                                        $imagePath = '/images/' . $review['image_product'];
                                    }
                                }
                                
                                if (!$hasImage) {
                                    $defaultImagePath = Yii::getAlias('@webroot/images/default-product.jpg');
                                    if (file_exists($defaultImagePath)) {
                                        $hasImage = true;
                                        $imagePath = '/images/default-product.jpg';
                                    }
                                }
                                ?>
                                
                                <?php if ($hasImage): ?>
                                    <img src="<?= Html::encode($imagePath) ?>" 
                                         alt="<?= Html::encode($review['product_name'] ?? 'Нет фото') ?>">
                                <?php else: ?>
                                    <div class="no-image">Нет изображения</div>
                                <?php endif; ?>
                                <div class="review-full-overlay"></div>
                            </div>
                            
                            <div class="review-full-content">
                                <div class="review-full-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="review-full-text">"<?= Html::encode($review['text']) ?>"</p>
                                <p class="review-full-author">– <?= Html::encode($review['full_name'] ?? 'Аноним') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-reviews-full">Пока нет отзывов</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Блок со всеми активными акциями -->
    <div class="last-promo-section" aria-labelledby="last-promo-heading">
        <div class="last-promo-container last-promo-container--multi">
            <?php if (!empty($promos)): ?>
                <header class="last-promo-head">
                    <h2 id="last-promo-heading" class="last-promo-head__title">Акции и спецпредложения</h2>
                    <p class="last-promo-head__lead">Все действующие предложения — в каталоге и при оформлении заказа</p>
                </header>
                <?= $this->render('_promo_cards', ['promos' => $promos]) ?>
                
            <?php else: ?>
                <div class="last-promo-placeholder">
                    <div class="placeholder-content">
                        <h3>Акции скоро появятся!</h3>
                        <p>Следите за обновлениями</p>
                        <a href="<?= Url::to(['/catalog/index']) ?>" class="cta-button">Перейти в каталог →</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>



       <!-- Промо-блок -->
<!-- ПРОМО-БАННЕР С БЕГУЩЕЙ СТРОКОЙ -->
<div class="promo-banner2">
    <div class="promo-scroll2">
        <div class="promo-track2">
            <?php for ($i = 0; $i < 15; $i++): ?>
                <span class="promo-text2">
                     Быстрая и удобная доставка по Санкт-Петербургу и всей России.
                </span>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- КНОПКА ПЕРЕХОДА -->
<div style="text-align: center; margin: 15px 0;">
    <a href="<?= Url::to(['/site/about', '#' => 'delivery-rules']) ?>" class="delivery-btn">
        Подробнее о правилах доставки →
    </a>
</div>      
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">