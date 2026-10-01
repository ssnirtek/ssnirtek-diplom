<?php

namespace app\models;

use Yii;
use yii\base\Model;

class PasswordResetRequestForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'Пользователь с таким email не найден'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

    public function sendEmail()
    {
        $user = User::findOne([
            'email' => $this->email,
            'status' => User::STATUS_ACTIVE,
        ]);

        if (!$user) {
            return false;
        }

        $user->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
        
        if (!$user->save()) {
            return false;
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);

        return Yii::$app->mailer->compose()
            ->setTo($this->email)
            ->setFrom([Yii::$app->params['adminEmail'] => 'Bijouterie'])
            ->setSubject('Восстановление пароля на Bijouterie')
            ->setHtmlBody("
                <h2>Здравствуйте, {$user->full_name}!</h2>
                <p>Для восстановления пароля перейдите по ссылке:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.</p>
            ")
            ->send();
    }
}