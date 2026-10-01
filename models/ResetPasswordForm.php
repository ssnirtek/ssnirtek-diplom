<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

class ResetPasswordForm extends Model
{
    public $password;
    private $_user;

    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Токен не может быть пустым.');
        }
        
        $this->_user = User::findOne(['password_reset_token' => $token]);
        
        if (!$this->_user) {
            throw new InvalidArgumentException('Неверный токен.');
        }
        
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Новый пароль',
        ];
    }

    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->password_reset_token = null;
        
        return $user->save();
    }
}