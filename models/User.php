<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id_user
 * @property string $email
 * @property string $password_hash
 * @property string $full_name
 * @property string|null $phone
 * @property string|null $is_admin
 * @property string|null $created_at
 *
 * @property Cart[] $carts
 * @property Orders[] $orders
 */
class User extends ActiveRecord implements IdentityInterface
{
 public $agree; 

public $password_repetition;
    /**
     * ENUM field values
     */

      const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const IS_ADMIN_USER = 'user';
    const IS_ADMIN_ADMIN = 'admin';

    /** Процент скидки на первые заказы после регистрации */
    public const WELCOME_ORDER_DISCOUNT_PERCENT = 15;

    /** Скидка действует на столько первых заказов (включая отменённые) */
    public const WELCOME_ORDER_DISCOUNT_MAX_ORDERS = 2;

    public const SCENARIO_PROFILE = 'profile';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
public function rules()
{
    return [
        [['email', 'password_hash', 'full_name'], 'required', 'message' => 'Это поле обязательно для заполнения', 'except' => [self::SCENARIO_PROFILE]],

        ['email', 'email', 'message' => 'Введите корректный email'],
        ['email', 'string', 'max' => 100],
        ['email', 'unique', 'message' => 'Пользователь с таким email уже существует'],

        [['email', 'phone'], 'required', 'on' => [self::SCENARIO_PROFILE], 'message' => 'Это поле обязательно для заполнения'],

        ['password_hash', 'string', 'min' => 8, 'tooShort' => 'Пароль должен быть не менее 8 символов', 'except' => [self::SCENARIO_PROFILE]],
        ['password_hash', 'string', 'max' => 255, 'except' => [self::SCENARIO_PROFILE]],

        ['password_repetition', 'required', 'message' => 'Повторите пароль', 'except' => [self::SCENARIO_PROFILE]],
        ['password_repetition', 'compare', 'compareAttribute' => 'password_hash', 'message' => 'Пароли не совпадают', 'except' => [self::SCENARIO_PROFILE]],

        ['full_name', 'match', 'pattern' => '/^[А-Яа-яЁё\s-]+$/u', 'message' => 'ФИО должно содержать только буквы кириллицы и пробелы', 'except' => [self::SCENARIO_PROFILE]],
        ['full_name', 'string', 'max' => 150, 'except' => [self::SCENARIO_PROFILE]],

        ['phone', 'default', 'value' => null],
        ['phone', 'string', 'max' => 20],
        ['phone', 'match', 'pattern' => '/^[\+\-\s\(\)0-9]+$/', 'message' => 'Неверный формат телефона', 'on' => [self::SCENARIO_PROFILE]],

        ['date_born', 'date', 'format' => 'php:Y-m-d', 'message' => 'Неверный формат даты', 'except' => [self::SCENARIO_PROFILE]],
        ['date_born', 'default', 'value' => null, 'except' => [self::SCENARIO_PROFILE]],

        ['is_admin', 'default', 'value' => 'user', 'except' => [self::SCENARIO_PROFILE]],
        ['is_admin', 'in', 'range' => ['user', 'admin'], 'message' => 'Недопустимая роль пользователя', 'except' => [self::SCENARIO_PROFILE]],

        ['status', 'default', 'value' => self::STATUS_ACTIVE, 'except' => [self::SCENARIO_PROFILE]],

        ['agree', 'required', 'requiredValue' => 1, 'message' => 'Необходимо согласиться на обработку персональных данных', 'except' => [self::SCENARIO_PROFILE]],

        [['created_at', 'password_reset_token', 'email_confirm_token'], 'safe'],
    ];
}

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_PROFILE] = ['email', 'phone'];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_user' => 'Id User',
            'email' => 'Почта',
            'password_hash' => 'Пароль',
             'password_repetition' => 'Повтор пароля',
            'full_name' => 'Фамилия Имя Отчество',
            'phone' => 'Номер телефона',
                'date_born' => 'Дата рождения',
         'is_admin' => 'Роль',
            'created_at' => 'Дата регистрации',
        ];
    }

    /**
     * Gets query for [[Carts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarts()
    {
        return $this->hasMany(Cart::class, ['user_id' => 'id_user']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::class, ['user_id' => 'id_user']);
    }

    /**
     * Число заказов пользователя (любой статус, в т.ч. отменённые).
     */
    public function getOrdersCountAll(): int
    {
        return (int) Orders::find()->where(['user_id' => $this->id_user])->count();
    }

    /**
     * Доступна ли приветственная скидка на следующий заказ (первые N заказов, не для админа).
     */
    public function isEligibleForWelcomeOrderDiscount(): bool
    {
        if ($this->is_admin === self::IS_ADMIN_ADMIN) {
            return false;
        }

        return $this->getOrdersCountAll() < self::WELCOME_ORDER_DISCOUNT_MAX_ORDERS;
    }

    /**
     * column is_admin ENUM value labels
     * @return string[]
     */
    public static function optsIsAdmin()
    {
        return [
            self::IS_ADMIN_USER => 'user',
            self::IS_ADMIN_ADMIN => 'admin',
        ];
    }

    /**
     * @return string
     */
    public function displayIsAdmin()
    {
        return self::optsIsAdmin()[$this->is_admin];
    }

    /**
     * @return bool
     */
    public function isIsAdminUser()
    {
        return $this->is_admin === self::IS_ADMIN_USER;
    }

    public function setIsAdminToUser()
    {
        $this->is_admin = self::IS_ADMIN_USER;
    }

    /**
     * @return bool
     */
    public function isIsAdminAdmin()
    {
        return $this->is_admin === self::IS_ADMIN_ADMIN;
    }

    public function setIsAdminToAdmin()
    {
        $this->is_admin = self::IS_ADMIN_ADMIN;
    }




    public static function findIdentity($id)
    {
 return static::findOne($id);
     }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
 

        return null;
    }

  
   public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id_user;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
          return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
           return null;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
         return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

     public static function generateRandomPassword($length = 8)
    {
        return Yii::$app->security->generateRandomString($length);
    }

            public function isAdmin()
    {
        return $this->role == self::IS_ADMIN_ADMIN;
    } 
public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

     public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Если это новый пользователь и пароль не хеширован
            if ($insert && !empty($this->password_hash) && strlen($this->password_hash) < 60) {
                $this->setPassword($this->password_hash);
            }
            return true;
        }
        return false;
    }



    
/**
 * Связь с избранными товарами
 */
public function getFavorites()
{
    return $this->hasMany(Favorite::class, ['user_id' => 'id_user']);
}

/**
 * Связь с товарами в избранном (через промежуточную таблицу)
 */
public function getFavoriteProducts()
{
    return $this->hasMany(Product::class, ['id_product' => 'product_id'])
        ->via('favorites');
}

/**
 * Проверить, есть ли товар в избранном у текущего пользователя
 */
public function hasInFavorites($productId)
{
    return Favorite::find()
        ->where(['user_id' => $this->id_user, 'product_id' => $productId])
        ->exists();
}




public function sendPasswordResetToken()
{
    // 1. Генерируем уникальный токен (32 случайных символа)
    $token = Yii::$app->security->generateRandomString(32);
    
    // 2. Сохраняем токен в БД в поле password_reset_token
    $this->password_reset_token = $token;
    
    // Сохраняем ТОЛЬКО поле токена, не трогая остальные
    if (!$this->save(false, ['password_reset_token'])) {
        Yii::error('Ошибка сохранения токена для пользователя #' . $this->id_user);
        return false;
    }
    
    // 3. Формируем абсолютную ссылку для сброса
    $resetLink = Yii::$app->urlManager->createAbsoluteUrl([
        'user/reset-password', 
        'token' => $token
    ]);
    
    // 4. Отправляем письмо
    try {
        $message = Yii::$app->mailer->compose()
            ->setFrom(['noreply@businka.ru' => 'Businka'])
            ->setTo($this->email)
            ->setSubject('Сброс пароля — Businka')
            ->setHtmlBody("
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
                    <h3 style='color: #282826;'>Здравствуйте, {$this->full_name}!</h3>
                    <p>Вы запросили сброс пароля на сайте <strong>Businka</strong>.</p>
                    <p>Для установки нового пароля нажмите на кнопку:</p>
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' 
                           style='display: inline-block; padding: 12px 30px; 
                                  background: #ff4081; color: #fff; 
                                  text-decoration: none; border-radius: 25px;'>
                            Сбросить пароль
                        </a>
                    </p>
                    <p style='font-size: 12px; color: #8b8c7b;'>
                        Или скопируйте ссылку:<br> {$resetLink}
                    </p>
                    <p style='color: #c62828; font-size: 13px;'>
                        ⚠ Если вы не запрашивали сброс пароля — проигнорируйте это письмо.
                    </p>
                </div>
            ");
        
        return $message->send();
        
    } catch (\Exception $e) {
        Yii::error('Ошибка отправки письма: ' . $e->getMessage());
        return false;
    }
}
/**
 * Генерация токена подтверждения email
 */
public function generateEmailConfirmToken()
{
    $this->email_confirm_token = Yii::$app->security->generateRandomString() . '_' . time();
}

/**
 * Удаление токена после подтверждения
 */
public function removeEmailConfirmToken()
{
    $this->email_confirm_token = null;
}

/**
 * Отправка письма с подтверждением email
 */
public function sendConfirmEmail()
{
    $confirmLink = Yii::$app->urlManager->createAbsoluteUrl([
        'user/confirm-email', 
        'token' => $this->email_confirm_token
    ]);
    
    // ✅ Исправляем имя отправителя на Businka
    return Yii::$app->mailer->compose()
        ->setTo($this->email)
        ->setFrom(['noreply@businka.ru' => 'Businka'])
        ->setSubject('Подтверждение email — Businka')
        ->setHtmlBody("
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <span style='font-size: 24px; font-weight: bold; color: #ff4081;'>✦ Businka</span>
                </div>
                <div style='background: #fdfcf8; border: 1px solid #e5bccb; border-radius: 12px; padding: 24px;'>
                    <h3 style='color: #282826;'>Здравствуйте, {$this->full_name}!</h3>
                    <p>Спасибо за регистрацию в интернет-магазине <strong>Businka</strong>!</p>
                    <p>Для подтверждения вашего email нажмите на кнопку:</p>
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='{$confirmLink}' 
                           style='display: inline-block; padding: 12px 30px; 
                                  background: #ff4081; color: #fff; 
                                  text-decoration: none; border-radius: 25px; 
                                  font-weight: bold;'>
                            Подтвердить email
                        </a>
                    </p>
                    <p style='font-size: 12px; color: #8b8c7b;'>
                        Или скопируйте ссылку в браузер:<br>
                        <small>{$confirmLink}</small>
                    </p>
                    <p style='color: #c62828; font-size: 13px; margin-top: 16px;'>
                        ⚠ Если вы не регистрировались на нашем сайте — просто проигнорируйте это письмо.
                    </p>
                </div>
                <div style='margin-top: 20px; font-size: 12px; color: #8b8c7b; text-align: center;'>
                    <p>С уважением, команда Businka</p>
                    <p>© " . date('Y') . " Businka. Все права защищены.</p>
                </div>
            </div>
        ")
        ->send();
}





}