<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;
use yii\db\ActiveRecord;
use app\models\Reviews;
use app\models\CartItem;

/**
 * This is the model class for table "product".
 *
 * @property int $id_product
 * @property int $category_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int|null $is_discount
 * @property float|null $old_price
 * @property int|null $quantity
 * @property string|null $image_product
 * @property int|null $is_active
 * @property string|null $created_at
 *
 * @property CartItem[] $cartItems
 * @property Category $category
 */
class Product extends ActiveRecord 
{ 
    public $imageFile;
    private $_activePromo = null;
    private $_finalPrice = null;
    private $_discountPercent = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'name', 'price'], 'required'],
            [['category_id', 'is_discount', 'quantity', 'is_active'], 'integer'],
            [['price', 'old_price'], 'number'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 200],
            [['image_product'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp', 'maxSize' => 5 * 1024 * 1024],
            ['old_price', 'compare', 'compareAttribute' => 'price', 'operator' => '>', 'type' => 'number', 
                'when' => function($model) {
                    return $model->is_discount == 1;
                }, 
                'message' => 'Старая цена должна быть больше текущей цены при включенной скидке'
            ],
            ['price', 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number', 'message' => 'Цена не может быть отрицательной'],
            ['quantity', 'default', 'value' => 0],
            ['is_active', 'default', 'value' => 1],
            ['is_discount', 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'ID товара',
            'category_id' => 'Категория',
            'name' => 'Название',
            'description' => 'Описание',
            'price' => 'Цена',
            'is_discount' => 'Со скидкой',
            'old_price' => 'Старая цена',
            'quantity' => 'Количество',
            'image_product' => 'Изображение',
            'imageFile' => 'Изображение',
            'is_active' => 'Активен',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        
        // Автоматическая установка даты создания
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        
        // Если скидка отключена, очищаем old_price
        if ($this->is_discount != 1) {
            $this->old_price = null;
        }
        
        // Убеждаемся, что old_price не меньше price при включенной скидке
        if ($this->is_discount == 1 && $this->old_price !== null && $this->old_price <= $this->price) {
            $this->old_price = $this->price * 1.1; // Устанавливаем на 10% выше
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->resetCache();
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->resetCache();
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItem::class, ['product_id' => 'id_product']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id_category' => 'category_id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Reviews::class, ['product_id' => 'id_product']);
    }

    /**
     * Gets query for [[Favorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return $this->hasMany(Favorite::class, ['product_id' => 'id_product']);
    }

    /**
     * Получает активную акцию для товара
     */
    public function getActivePromo()
    {
        if ($this->_activePromo !== null) {
            return $this->_activePromo;
        }
        
        if ($this->isNewRecord) {
            return null;
        }
        
        $this->_activePromo = Promo::find()
            ->innerJoin('promo_product', 'promo.id_promo = promo_product.promo_id')
            ->where(['promo_product.product_id' => $this->id_product])
            ->andWhere(['promo.is_active' => 1])
            ->andWhere(['<=', 'promo.start_date', date('Y-m-d')])
            ->andWhere(['>=', 'promo.end_date', date('Y-m-d')])
            ->one();
        
        return $this->_activePromo;
    }

    /**
     * Проверяет, есть ли любая скидка
     */
    public function hasAnyDiscount()
    {
        return $this->hasRegularDiscount() || $this->hasPromoDiscount();
    }

  


    /**
     * Получает форматированную финальную цену
     */
    public function getFinalPriceFormatted()
    {
        return number_format($this->getFinalPrice(), 0, '', ' ');
    }

    /**
     * Получает форматированную старую цену
     */
    public function getOldPriceFormatted()
    {
        $oldPrice = $this->getDisplayOldPrice();
        return $oldPrice ? number_format($oldPrice, 0, '', ' ') : null;
    }

    /**
     * Получает форматированную обычную цену
     */
    public function getPriceFormatted()
    {
        return number_format($this->price, 0, '', ' ');
    }

    /**
     * Полный URL изображения
     */
    public function getImageUrl()
    {
        if ($this->image_product && file_exists(Yii::getAlias('@webroot') . '/images/' . $this->image_product)) {
            return '/images/' . $this->image_product;
        }
        return '/images/no-image.jpg';
    }

    /**
     * Загрузка изображения
     */
    public function uploadImage()
    {
        if ($this->imageFile instanceof UploadedFile) {
            $path = 'images/';
            $fullPath = Yii::getAlias('@webroot') . '/' . $path;
            
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
            
            $fileName = 'product_' . time() . '_' . uniqid() . '.' . $this->imageFile->extension;
            $filePath = $fullPath . $fileName;
            
            if ($this->imageFile->saveAs($filePath)) {
                if ($this->image_product && file_exists(Yii::getAlias('@webroot') . '/images/' . $this->image_product)) {
                    @unlink(Yii::getAlias('@webroot') . '/images/' . $this->image_product);
                }
                $this->image_product = $fileName;
                return true;
            }
        }
        return false;
    }

    /**
     * Проверяет, есть ли товар в наличии
     */
    public function getInStock()
    {
        return $this->quantity > 0;
    }

    /**
     * Проверяет, активен ли товар
     */
    public function getIsActive()
    {
        return $this->is_active == 1;
    }

    /**
     * Получает количество товара в корзине пользователя
     */
    public function getCartQuantity()
    {
        if (Yii::$app->user->isGuest) {
            return 0;
        }
        
        $cart = Cart::getCurrentCart();
        if (!$cart) {
            return 0;
        }
        
        $cartItem = CartItem::findOne([
            'cart_id' => $cart->id_cart,
            'product_id' => $this->id_product
        ]);
        
        return $cartItem ? $cartItem->quantity : 0;
    }

    /**
     * Получает максимальное доступное количество для заказа
     */
    public function getMaxOrderQuantity()
    {
        return $this->quantity;
    }

    /**
     * Сброс кешированных значений
     */
    public function resetCache()
    {
        $this->_activePromo = null;
        $this->_finalPrice = null;
        $this->_discountPercent = null;
    }

    /**
     * Получить все активные товары
     */
    public static function getActiveProducts()
    {
        return self::find()
            ->where(['is_active' => 1])
            ->andWhere(['>', 'quantity', 0])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Получить товары со скидкой
     */
    public static function getDiscountedProducts()
    {
        $products = self::find()
            ->where(['is_active' => 1])
            ->andWhere(['>', 'quantity', 0])
            ->all();
        
        return array_filter($products, function($product) {
            return $product->hasAnyDiscount();
        });
    }
    
    /**
     * Получить URL для редактирования
     */
    public function getUpdateUrl()
    {
        return ['/product/update', 'id' => $this->id_product];
    }
    
    /**
     * Получить URL для удаления
     */
    public function getDeleteUrl()
    {
        return ['/product/delete', 'id' => $this->id_product];
    }
    
/**
 * Связь с акциями через промежуточную таблицу
 */
public function getPromoProducts()
{
    return $this->hasMany(PromoProduct::class, ['product_id' => 'id_product']);
}

/**
 * Получить все активные акции товара
 */
public function getActivePromos()
{
    return $this->hasMany(Promo::class, ['id_promo' => 'promo_id'])
        ->via('promoProducts')
        ->andWhere(['promo.is_active' => 1])
        ->andWhere(['<=', 'promo.start_date', date('Y-m-d')])
        ->andWhere(['>=', 'promo.end_date', date('Y-m-d')]);
}

/**
 * Получить текущую скидку товара
 */
public function getCurrentPromoProduct()
{
    return $this->getPromoProducts()
        ->joinWith('promo')
        ->andWhere(['promo.is_active' => 1])
        ->orderBy(['price_discount' => SORT_ASC])
        ->one();
}

/**
 * Получить цену с учетом скидки
 */
public function getDiscountedPrice()
{
    $currentPromo = $this->getCurrentPromoProduct();
    
    if ($currentPromo && $currentPromo->price_discount > 0) {
        return $currentPromo->price_discount;
    }
    
    return $this->price;
}

/**
 * Проверить, есть ли активная скидка
 */
public function hasDiscount()
{
    return $this->getCurrentPromoProduct() !== null;
}


    /**
     * Получить информацию о цене с учетом акции
     * @return array|null
     */
    public function getPromoPriceInfo()
    {
        // Получаем активную акцию для товара через promo_product
        $promoProduct = PromoProduct::find()
            ->where(['product_id' => $this->id_product])
            ->joinWith('promo')
            ->andWhere(['promo.is_active' => 1])
            ->andWhere(['<=', 'promo.start_date', date('Y-m-d')])
            ->andWhere(['>=', 'promo.end_date', date('Y-m-d')])
            ->one();
        
        if (!$promoProduct) {
            return null;
        }
        
        // Определяем базовую цену
        if ($this->is_discount && $this->old_price > 0) {
            // Если уже есть обычная скидка, базовая цена - old_price
            $basePrice = $this->old_price;
        } else {
            // Иначе базовая цена - текущая цена
            $basePrice = $this->price;
        }
        
        $discountPercent = $promoProduct->promo->discount_percent;
        
        // Если в promo_product уже сохранены цены, используем их
        if ($promoProduct->old_price > 0 && $promoProduct->price_discount > 0) {
            return [
                'base_price' => $promoProduct->old_price,
                'final_price' => $promoProduct->price_discount,
                'discount_percent' => $discountPercent
            ];
        }
        
        // Рассчитываем финальную цену
        $finalPrice = $basePrice * (100 - $discountPercent) / 100;
        
        return [
            'base_price' => $basePrice,
            'final_price' => round($finalPrice, 2),
            'discount_percent' => $discountPercent
        ];
    }

    /**
     * Получить финальную цену с учетом всех скидок
     * @return float
     */
    public function getFinalPrice()
    {
        $promoInfo = $this->getPromoPriceInfo();
        
        if ($promoInfo) {
            return $promoInfo['final_price'];
        }
        
        return $this->price;
    }

    /**
     * Проверить наличие активной акции
     * @return bool
     */
    public function hasPromoDiscount()
    {
        return $this->getPromoPriceInfo() !== null;
    }

    /**
     * Получить процент скидки по акции
     * @return int
     */
    public function getPromoDiscountPercent()
    {
        $promoInfo = $this->getPromoPriceInfo();
        
        if ($promoInfo) {
            return $promoInfo['discount_percent'];
        }
        
        return 0;
    }


  

    /**
 * Проверить наличие обычной скидки (не акционной)
 * @return bool
 */
public function hasRegularDiscount()
{
    return $this->is_discount == 1 && $this->old_price > 0 && $this->old_price > $this->price;
}

/**
 * Получить процент обычной скидки
 * @return int
 */
public function getDiscountPercent()
{
    if ($this->hasRegularDiscount()) {
        return round((1 - $this->price / $this->old_price) * 100);
    }
    
    return 0;
}

/**
 * Получить отображаемую цену (с учетом всех скидок)
 * @return float
 */
public function getDisplayPrice()
{
    $promoInfo = $this->getPromoPriceInfo();
    
    if ($promoInfo) {
        return $promoInfo['final_price'];
    }
    
    return $this->price;
}

/**
 * Получить старую цену для отображения
 * @return float|null
 */
public function getDisplayOldPrice()
{
    $promoInfo = $this->getPromoPriceInfo();
    
    if ($promoInfo) {
        return $promoInfo['base_price'];
    }
    
    if ($this->hasRegularDiscount()) {
        return $this->old_price;
    }
    
    return null;
}

/**
 * Получить процент скидки для отображения
 * @return int|null
 */
public function getDisplayDiscountPercent()
{
    if ($this->hasPromoDiscount()) {
        return $this->getPromoDiscountPercent();
    }
    
    if ($this->hasRegularDiscount()) {
        return $this->getDiscountPercent();
    }
    
    return null;
}

}