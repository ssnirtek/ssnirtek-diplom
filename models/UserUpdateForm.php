<?php

namespace app\models;

use yii\base\Model;

class UserUpdateForm extends Model
{
    public $email;
    public $phone;

    public function rules()
    {
        return [
            [['email', 'phone'], 'required', 'message' => 'Заполните это поле'],
            ['email', 'trim'],
            ['email', 'email', 'message' => 'Введите корректный email'],
            ['email', 'string', 'max' => 100],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email', 'filter' => function ($query) {
                if (!\Yii::$app->user->isGuest) {
                    $query->andWhere(['!=', 'id_user', \Yii::$app->user->identity->id_user]);
                }
            }, 'message' => 'Этот email уже занят'],
            ['phone', 'trim'],
            ['phone', 'string', 'max' => 20],
            ['phone', 'match', 'pattern' => '/^[\+\-\s\(\)0-9]+$/', 'message' => 'Неверный формат телефона'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'phone' => 'Телефон',
        ];
    }
}
