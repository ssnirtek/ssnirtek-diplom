<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Favorite extends ActiveRecord
{
    public static function tableName()
    {
        return 'favorites';
    }

    public function rules()
    {
        return [
            [['user_id', 'product_id'], 'required'],
            [['user_id', 'product_id'], 'integer'],
            [['user_id', 'product_id'], 'unique', 'targetAttribute' => ['user_id', 'product_id'], 'message' => 'Этот товар уже в избранном'],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_favorite' => 'ID',
            'user_id' => 'Пользователь',
            'product_id' => 'Товар',
            'created_at' => 'Дата добавления',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'user_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id_product' => 'product_id']);
    }

    /**
     * Переключение статуса избранного (добавить/удалить)
     * @param int $productId ID товара
     * @return array результат операции
     */
    public static function toggle($productId)
    {
        if (Yii::$app->user->isGuest) {
            return [
                'success' => false,
                'require_auth' => true,
                'message' => 'Чтобы добавлять товары в избранное, войдите в аккаунт или зарегистрируйтесь.',
            ];
        }

        $userId = Yii::$app->user->id;
        $favorite = self::find()
            ->where(['user_id' => $userId, 'product_id' => $productId])
            ->one();

        if ($favorite) {
            // Удаляем из избранного
            if ($favorite->delete()) {
                return [
                    'success' => true, 
                    'action' => 'removed', 
                    'message' => 'Удалено из избранного',
                    'isFavorite' => false
                ];
            }
        } else {
            // Добавляем в избранное
            $favorite = new Favorite();
            $favorite->user_id = $userId;
            $favorite->product_id = $productId;
            $favorite->created_at = date('Y-m-d H:i:s');
            
            if ($favorite->save()) {
                return [
                    'success' => true, 
                    'action' => 'added', 
                    'message' => 'Добавлено в избранное',
                    'isFavorite' => true
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Ошибка при выполнении операции'];
    }

    /**
     * Проверить, находится ли товар в избранном у текущего пользователя
     * @param int $productId ID товара
     * @return bool
     */
    public static function isFavorite($productId)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        return self::find()
            ->where([
                'user_id' => Yii::$app->user->id, 
                'product_id' => $productId
            ])
            ->exists();
    }
    
    /**
     * Получить количество избранных товаров пользователя
     * @param int|null $userId ID пользователя (если null - текущий)
     * @return int
     */
    public static function getFavoritesCount($userId = null)
    {
        if ($userId === null) {
            if (Yii::$app->user->isGuest) {
                return 0;
            }
            $userId = Yii::$app->user->id;
        }
        
        return self::find()
            ->where(['user_id' => $userId])
            ->count();
    }
    
    /**
     * Получить все избранные товары пользователя
     * @param int|null $userId ID пользователя (если null - текущий)
     * @return array|\yii\db\ActiveQuery
     */
    public static function getUserFavorites($userId = null)
    {
        if ($userId === null) {
            if (Yii::$app->user->isGuest) {
                return [];
            }
            $userId = Yii::$app->user->id;
        }
        
        return self::find()
            ->where(['user_id' => $userId])
            ->with('product')
            ->orderBy(['created_at' => SORT_DESC]);
    }
}