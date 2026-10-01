<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cart_item".
 *
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 *
 * @property Cart $cart
 * @property Product $product
 */
class CartItem extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity'], 'default', 'value' => 1],
            [['cart_id', 'product_id'], 'required'],
            [['cart_id', 'product_id', 'quantity'], 'integer'],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::class, 'targetAttribute' => ['cart_id' => 'id_cart']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id_product']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cart_id' => 'Cart ID',
            'product_id' => 'Product ID',
            'quantity' => 'Quantity',
        ];
    }

    /**
     * Gets query for [[Cart]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['id_cart' => 'cart_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id_product' => 'product_id']);
    }



/**
 * Проверить, действует ли акция на этот товар
 */
   public function hasPromo()
{
    return $this->product && $this->product->hasPromoDiscount();
}



/**
 * Проверить, есть ли скидка (обычная или акция)
 */
public function hasAnyDiscount()
{
    return $this->product && $this->product->hasAnyDiscount();
}

/**
 * Получить процент скидки
 */
public function getDiscountPercent()
{
    if (!$this->product) return 0;
    
    if ($this->hasPromo()) {
        return $this->product->getPromoDiscountPercent();
    }
    
    if ($this->product->hasRegularDiscount()) {
        return $this->product->getDiscountPercent();
    }
    
    return 0;
}

/**
 * Получить финальную цену товара в корзине
 */
public function getFinalPrice()
{
    return $this->product ? $this->product->getFinalPrice() : 0;
}

/**
 * Получить общую стоимость позиции в корзине
 */
public function getTotalPrice()
{
    return $this->getFinalPrice() * $this->quantity;
}

     /**
     * Получает процент скидки по акции
     */
    public function getPromoDiscountPercent()
    {
        if (!$this->hasPromo()) {
            return 0;
        }
        return $this->product->getPromoDiscountPercent();
    }

    /**
     * Получает оригинальную цену (без учета акции)
     */
    public function getOriginalPrice()
    {
        return $this->product ? $this->product->price : 0;
    }

    /**
     * Получает цену с учетом акции
     */
    public function getPriceWithPromo()
    {
        return $this->product ? $this->product->getFinalPrice() : 0;
    }

    /**
     * Получает сумму для этого товара
     */
    public function getSum()
    {
        return $this->getPriceWithPromo() * $this->quantity;
    }

    /**
     * Добавить товар в корзину
     */
    public static function addToCart($productId, $quantity = 1)
    {
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Войдите, чтобы добавить товар'];
        }

        $product = Product::findOne($productId);
        if (!$product || $product->quantity <= 0) {
            return ['success' => false, 'message' => 'Товар недоступен'];
        }

        $cart = Cart::getCurrentCart();
        
        $item = self::find()
            ->where(['cart_id' => $cart->id_cart, 'product_id' => $productId])
            ->one();

        if ($item) {
            $item->quantity += $quantity;
        } else {
            $item = new CartItem();
            $item->cart_id = $cart->id_cart;
            $item->product_id = $productId;
            $item->quantity = $quantity;
        }

        if ($item->save()) {
            return ['success' => true, 'message' => 'Товар добавлен в корзину'];
        }

        return ['success' => false, 'message' => 'Ошибка добавления'];
    }

}
