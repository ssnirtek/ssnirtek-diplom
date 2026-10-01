<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\OrderItem;
/**
 * This is the model class for table "orders".
 *
 * @property int $id_orders
 * @property int $user_id
 * @property float $subtotal_amount
 * @property float $welcome_discount_amount
 * @property float $total_amount
 * @property string|null $status
 * @property string|null $created_at
 *
 * @property User $user
 */
class Orders extends ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * В БД добавлены поля миграцией m260513_120000_orders_add_welcome_discount.
     * Пока миграция не применена, колонок нет — не добавляем их в rules, чтобы не ломать валидацию.
     */
    public static function hasWelcomeDiscountColumns(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $schema = static::getTableSchema();
        $cache = $schema !== null
            && isset($schema->columns['subtotal_amount'], $schema->columns['welcome_discount_amount']);

        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['user_id', 'total_amount'], 'required'],
            [['user_id'], 'integer'],
            [['total_amount'], 'number'],
            [['status'], 'string'],
            [['created_at'], 'safe'],
            [['full_name', 'phone', 'email', 'address', 'comment'], 'safe'],
            [['status'], 'in', 'range' => ['pending', 'processing', 'completed', 'cancelled']],
        ];

        if (static::hasWelcomeDiscountColumns()) {
            $rules[] = [['subtotal_amount', 'welcome_discount_amount'], 'number'];
            $rules[] = [['subtotal_amount', 'welcome_discount_amount'], 'default', 'value' => 0];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
        'id_orders' => '№ заказа',
            'user_id' => 'Пользователь',
            'subtotal_amount' => 'Сумма товаров',
            'welcome_discount_amount' => 'Скидка 15% (первые заказы)',
            'total_amount' => 'К оплате',
            'status' => 'Статус',
            'created_at' => 'Дата',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'user_id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PENDING => 'pending',
            self::STATUS_PROCESSING => 'processing',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_CANCELLED => 'cancelled',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function setStatusToPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function setStatusToProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isStatusCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function setStatusToCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }


    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id_orders']);
    }

    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id_product' => 'product_id'])
            ->via('orderItems');
    }

    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'Ожидает',
            'processing' => 'В обработке',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusClass()
    {
        $classes = [
            'pending' => 'status-pending',
            'processing' => 'status-processing',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
        ];
        return $classes[$this->status] ?? 'status-default';
    }

/**
 * Получить все возможные статусы для select
 */
public static function getStatusOptions()
{
    return [
        'pending' => 'Ожидает обработки',
        'processing' => 'В обработке',
        'completed' => 'Выполнен',
        'cancelled' => 'Отменен',
    ];
}
}
