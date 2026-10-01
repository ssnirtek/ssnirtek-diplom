<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reviews".
 *
 * @property int $id_review
 * @property int $user_id
 * @property int|null $product_id
 * @property int $rating
 * @property string $text
 * @property int $is_approved
 * @property string $created_at
 *
 * @property User $user
 * @property Product $product
 */
class Reviews extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reviews';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'text', 'rating'], 'required'],
            [['user_id', 'product_id', 'rating', 'is_approved'], 'integer'],
            [['text'], 'string'],
            [['created_at'], 'safe'],
            [['rating'], 'default', 'value' => 5],
            [['is_approved'], 'default', 'value' => 1],
            [['rating'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id_user']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id_product']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_review' => 'ID отзыва',
            'user_id' => 'Пользователь',
            'product_id' => 'Товар',
            'rating' => 'Оценка',
            'text' => 'Текст отзыва',
            'is_approved' => 'Одобрен',
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
        return $this->hasOne(User::class, ['id_user' => 'user_id']);
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
     * Получить имя автора из связанного пользователя
     */
    public function getAuthorName()
    {
        return $this->user ? $this->user->full_name : 'Пользователь';
    }

    /**
     * Получить последние одобренные отзывы
     */
    public static function getLatestReviews($limit = 3)
    {
        return self::find()
            ->where(['is_approved' => 1])
            ->with(['user', 'product'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Получить звезды в HTML
     */
    public function getStarsHtml()
    {
        $html = '';
        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                $html .= '<i class="fas fa-star"></i>';
            } elseif ($i == $fullStars + 1 && $halfStar) {
                $html .= '<i class="fas fa-star-half-alt"></i>';
            } else {
                $html .= '<i class="far fa-star"></i>';
            }
        }
        
        return $html;
    }

    /**
     * Получить средний рейтинг
     */
    public static function getAverageRating()
    {
        return self::find()
            ->where(['is_approved' => 1])
            ->average('rating');
    }

    /**
     * Получить количество отзывов
     */
    public static function getTotalCount()
    {
        return self::find()
            ->where(['is_approved' => 1])
            ->count();
    }

    /**
     * У пользователя уже есть отзыв на этот товар (любой статус модерации).
     */
    public static function userHasReviewForProduct(int $userId, int $productId): bool
    {
        if ($userId <= 0 || $productId <= 0) {
            return false;
        }

        return self::find()
            ->where(['user_id' => $userId, 'product_id' => $productId])
            ->exists();
    }
}