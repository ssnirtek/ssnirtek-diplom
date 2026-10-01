<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "promo_product" (связь акций с товарами)
 *
 * @property int $id_promo_product
 * @property int $promo_id
 * @property int $product_id
 *
 * @property Promo $promo
 * @property Product $product
 */
class PromoProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promo_product';
    }

    /**
     * {@inheritdoc}
     */
 public function rules()
    {
        return [
            [['promo_id', 'product_id'], 'required'],
            [['promo_id', 'product_id'], 'integer'],
            [['old_price', 'price_discount'], 'number', 'min' => 0],
            [['promo_id', 'product_id'], 'unique', 'targetAttribute' => ['promo_id', 'product_id']],
            [['promo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promo::class, 'targetAttribute' => ['promo_id' => 'id_promo']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id_product']],
        ];
    }
    /**
     * {@inheritdoc}
     */
  public function attributeLabels()
    {
        return [
            'id_promo_product' => 'ID',
            'promo_id' => 'Акция',
            'product_id' => 'Товар',
            'old_price' => 'Старая цена',
            'price_discount' => 'Цена со скидкой',
        ];
    }

 /**
     * Связь с акцией
     */
    public function getPromo()
    {
        return $this->hasOne(Promo::class, ['id_promo' => 'promo_id']);
    }

    /**
     * Связь с товаром
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id_product' => 'product_id']);
    }

    /**
     * Рассчитать цену со скидкой
     */
    public function calculateDiscountPrice($originalPrice, $discountPercent)
    {
        if ($discountPercent <= 0 || $discountPercent > 100) {
            return $originalPrice;
        }
        
        $discount = ($originalPrice * $discountPercent) / 100;
        return round($originalPrice - $discount, 2);
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $product = Product::findOne($this->product_id);
            $promo = Promo::findOne($this->promo_id);
            
            if ($product && $promo) {
                // Сохраняем оригинальную цену товара
                $this->old_price = $product->price;
                
                // Рассчитываем цену со скидкой
                $this->price_discount = $this->calculateDiscountPrice(
                    $product->price, 
                    $promo->discount_percent
                );
            }
        }

        return true;
    }

    /**
     * После сохранения - обновляем цену товара
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Обновляем цену товара
        $this->updateProductPrice();
    }

    /**
     * Перед удалением - восстанавливаем цену товара
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        
        // Восстанавливаем оригинальную цену товара
        $this->restoreProductPrice();
        
        return true;
    }

    /**
     * Обновить цену товара (применить скидку)
     */
    public function updateProductPrice()
    {
        $product = Product::findOne($this->product_id);
        $promo = $this->promo;
        
        if ($product && $promo && $promo->is_active) {
            // Устанавливаем флаг скидки
            $product->is_discount = 1;
            
            // Сохраняем старую цену в товаре
            $product->old_price = $this->old_price;
            
            // Устанавливаем новую цену
            $product->price = $this->price_discount;
            
            if (!$product->save()) {
                Yii::error('Ошибка обновления цены товара: ' . implode(', ', $product->getErrorSummary(true)));
                return false;
            }
        }
        
        return true;
    }

    /**
     * Восстановить оригинальную цену товара
     */
    public function restoreProductPrice()
    {
        $product = Product::findOne($this->product_id);
        
        if ($product && $this->old_price > 0) {
            // Проверяем, есть ли другие активные акции для этого товара
            $otherActivePromos = self::find()
                ->where(['product_id' => $this->product_id])
                ->andWhere(['!=', 'promo_id', $this->promo_id])
                ->joinWith('promo')
                ->andWhere(['promo.is_active' => 1])
                ->exists();
            
            if (!$otherActivePromos) {
                // Если других активных акций нет, восстанавливаем цену
                $product->price = $this->old_price;
                $product->old_price = null;
                $product->is_discount = 0;
            } else {
                // Если есть другие акции, берем первую активную
                $nextPromoProduct = self::find()
                    ->where(['product_id' => $this->product_id])
                    ->andWhere(['!=', 'promo_id', $this->promo_id])
                    ->joinWith('promo')
                    ->andWhere(['promo.is_active' => 1])
                    ->orderBy(['price_discount' => SORT_ASC])
                    ->one();
                
                if ($nextPromoProduct) {
                    $product->price = $nextPromoProduct->price_discount;
                    $product->old_price = $nextPromoProduct->old_price;
                    $product->is_discount = 1;
                }
            }
            
            if (!$product->save()) {
                Yii::error('Ошибка восстановления цены товара: ' . implode(', ', $product->getErrorSummary(true)));
                return false;
            }
        }
        
        return true;
    }
}