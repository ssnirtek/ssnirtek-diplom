<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

class OrderItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_item';
    }

    public function rules()
    {
        return [
            [['order_id', 'product_id', 'quantity', 'price'], 'required'],
            [['order_id', 'product_id', 'quantity'], 'integer'],
            [['price'], 'number'],
        ];
    }
     public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Заказ',
            'product_id' => 'Товар',
            'quantity' => 'Количество',
            'price' => 'Цена',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Orders::class, ['id_orders' => 'order_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id_product' => 'product_id']);
    }

    public function getSum()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Товары из заказов пользователя (заказ не отменён) — уникальные id товара.
     *
     * @return int[]
     */
    public static function getOrderedProductIdsByUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return (new Query())
            ->from(['oi' => self::tableName()])
            ->innerJoin(['o' => Orders::tableName()], '[[oi.order_id]] = [[o.id_orders]]')
            ->where(['o.user_id' => $userId])
            ->andWhere(['!=', 'o.status', Orders::STATUS_CANCELLED])
            ->select('oi.product_id')
            ->distinct()
            ->column();
    }

    /**
     * Пользователь заказывал этот товар (есть позиция в неотменённом заказе).
     */
    public static function userHasOrderedProduct(int $userId, int $productId): bool
    {
        if ($userId <= 0 || $productId <= 0) {
            return false;
        }

        return (new Query())
            ->from(['oi' => self::tableName()])
            ->innerJoin(['o' => Orders::tableName()], '[[oi.order_id]] = [[o.id_orders]]')
            ->where([
                'o.user_id' => $userId,
                'oi.product_id' => $productId,
            ])
            ->andWhere(['!=', 'o.status', Orders::STATUS_CANCELLED])
            ->exists();
    }
}