<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cart".
 *
 * @property int $id_cart
 * @property int $user_id
 * @property string $created_at
 *
 * @property User $user
 * @property CartItem[] $cartItems
 */
class Cart extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id_user']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_cart' => 'ID корзины',
            'user_id' => 'ID пользователя',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id_user' => 'user_id']);
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItem::className(), ['cart_id' => 'id_cart']);
    }
    
    /**
     * Получает текущую корзину пользователя
     */
    public static function getCurrentCart()
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }
        
        $cart = self::findOne(['user_id' => Yii::$app->user->id]);
        
        if (!$cart) {
            $cart = new self();
            $cart->user_id = Yii::$app->user->id;
            $cart->save();
        }
        
        return $cart;
    }
    
    /**
     * Получает общее количество товаров в корзине
     */
    public function getTotalCount()
    {
        return CartItem::find()
            ->where(['cart_id' => $this->id_cart])
            ->sum('quantity') ?: 0;
    }
    
    /**
     * Получает общую сумму корзины
     */
    public function getTotalSum()
    {
        $sql = "SELECT SUM(total_price) as total FROM v_cart_details WHERE id_cart = :cart_id";
        $result = Yii::$app->db->createCommand($sql, [':cart_id' => $this->id_cart])->queryOne();
        
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Сумма корзины по позициям с учётом промо и товарных скидок (до приветственной скидки на заказ).
     */
    public function getSubtotalAfterPromos(): float
    {
        return $this->getTotalSum();
    }

    public function isWelcomeDiscountApplicable(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $user = Yii::$app->user->identity;
        if (!$user instanceof User) {
            return false;
        }

        return $user->isEligibleForWelcomeOrderDiscount();
    }

    /**
     * Сумма приветственной скидки (15% от суммы после товарных акций), целые рубли.
     */
    public function getWelcomeDiscountAmount(): float
    {
        if (!$this->isWelcomeDiscountApplicable()) {
            return 0.0;
        }
        $base = $this->getSubtotalAfterPromos();
        $amount = round($base * User::WELCOME_ORDER_DISCOUNT_PERCENT / 100);

        return (float) min($amount, $base);
    }

    /**
     * К оплате после приветственной скидки.
     */
    public function getAmountToPay(): float
    {
        return (float) max(0, round($this->getSubtotalAfterPromos() - $this->getWelcomeDiscountAmount()));
    }

    /**
     * Очистить корзину (удалить все товары)
     */
    public function clear()
    {
        return CartItem::deleteAll(['cart_id' => $this->id_cart]);
    }

    /**
     * Очистить корзину и вернуть количество удаленных товаров
     */
    public function clearAndGetCount()
    {
        $count = CartItem::find()->where(['cart_id' => $this->id_cart])->count();
        CartItem::deleteAll(['cart_id' => $this->id_cart]);
        return $count;
    }
}