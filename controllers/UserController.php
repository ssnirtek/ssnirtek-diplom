<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Orders;
use app\models\UserUpdateForm;
use app\models\OrderItem;
use app\models\Product;
use app\models\Reviews;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['profile', 'update-profile', 'orders', 'order-view', 'resend-confirmation'],
                'rules' => [
                    [
                        'actions' => ['profile', 'update-profile', 'orders', 'order-view', 'resend-confirmation'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => fn () => !Yii::$app->user->identity || Yii::$app->user->identity->is_admin !== 'admin',
                        'denyCallback' => function () {
                            Yii::$app->session->setFlash('info', 'Личный кабинет недоступен для администратора');
                            return Yii::$app->response->redirect(Yii::$app->homeUrl);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }

        $model = new User();
        if ($model->load(Yii::$app->request->post())) {
            $model->is_admin = 'user';
            $model->status = User::STATUS_ACTIVE;
            $model->generateEmailConfirmToken();

            if (!empty($model->date_born)) {
                $date = \DateTime::createFromFormat('d-m-Y', $model->date_born);
                if ($date) {
                    $model->date_born = $date->format('Y-m-d');
                }
            }

            if ($model->save()) {
                $model->sendConfirmEmail();
                Yii::$app->session->setFlash('success', 'Регистрация успешна! Проверьте почту для подтверждения email.');
                return $this->redirect(['site/login']);
            }

            $errors = [];
            foreach ($model->errors as $field => $fieldErrors) {
                $errors[] = $model->getAttributeLabel($field) . ': ' . implode(', ', $fieldErrors);
            }
            Yii::$app->session->setFlash('error', 'Ошибка регистрации:<br>' . implode('<br>', $errors));
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionConfirmEmail($token)
    {
        if (empty($token)) {
            Yii::$app->session->setFlash('error', 'Токен не указан');
            return $this->redirect(['site/index']);
        }

        $user = User::findOne(['email_confirm_token' => $token]);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Неверный или устаревший токен');
            return $this->redirect(['site/index']);
        }

        $user->removeEmailConfirmToken();
        $user->status = User::STATUS_ACTIVE;
        if ($user->save(false, ['email_confirm_token', 'status'])) {
            Yii::$app->session->setFlash('success', 'Email подтверждён. Теперь вы можете войти.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка подтверждения email');
        }

        return $this->redirect(['site/login']);
    }

    public function actionProfile()
    {
        $userId = (int) Yii::$app->user->identity->id_user;
        $updateForm = new UserUpdateForm([
            'email' => Yii::$app->user->identity->email,
            'phone' => Yii::$app->user->identity->phone,
        ]);

        return $this->render('profile', $this->profileViewData($userId, $updateForm));
    }

    public function actionUpdateProfile()
    {
        $userId = (int) Yii::$app->user->identity->id_user;
        $model = $this->findModel($userId);
        $updateForm = new UserUpdateForm();

        if (!Yii::$app->request->isPost || !$updateForm->load(Yii::$app->request->post())) {
            return $this->redirect(['profile']);
        }

        if (!$updateForm->validate()) {
            Yii::$app->session->setFlash('error', 'Проверьте правильность заполнения полей');
            return $this->render('profile', $this->profileViewData($userId, $updateForm));
        }

        $oldEmail = $model->email;
        $model->setScenario(User::SCENARIO_PROFILE);
        $model->email = trim($updateForm->email);
        $model->phone = trim($updateForm->phone);

        if (!$model->save()) {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
            return $this->render('profile', $this->profileViewData($userId, $updateForm));
        }

        if (strcasecmp($oldEmail, $model->email) !== 0) {
            $model->generateEmailConfirmToken();
            $model->save(false, ['email_confirm_token']);
            $model->sendConfirmEmail();
            Yii::$app->session->setFlash(
                'success',
                'Контакты обновлены. На новый email отправлено письмо для подтверждения.'
            );
        } else {
            Yii::$app->session->setFlash('success', 'Email и телефон успешно сохранены');
        }

        return $this->redirect(['profile']);
    }

    private function profileViewData(int $userId, UserUpdateForm $updateForm): array
    {
        $model = $this->findModel($userId);

        $reviewableIds = [];
        foreach (OrderItem::getOrderedProductIdsByUser($userId) as $pid) {
            $pid = (int) $pid;
            if ($pid > 0 && !Reviews::userHasReviewForProduct($userId, $pid)) {
                $reviewableIds[] = $pid;
            }
        }

        return [
            'model' => $model,
            'lastOrders' => Orders::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC])->limit(3)->all(),
            'updateForm' => $updateForm,
            'reviewableProducts' => $reviewableIds === []
                ? []
                : Product::find()->where(['id_product' => $reviewableIds, 'is_active' => 1])->orderBy(['name' => SORT_ASC])->all(),
        ];
    }

    public function actionOrders()
    {
        $userId = Yii::$app->user->identity->id_user;
        return $this->render('orders', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Orders::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC]),
                'pagination' => ['pageSize' => 10],
            ]),
        ]);
    }

    public function actionOrderView($id)
    {
        $order = Orders::findOne(['id_orders' => $id, 'user_id' => Yii::$app->user->identity->id_user]);
        if ($order === null) {
            throw new NotFoundHttpException('Заказ не найден');
        }

        return $this->render('order-view', ['order' => $order]);
    }

    public function actionRequestPasswordReset()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if (Yii::$app->request->isPost) {
            $email = trim(Yii::$app->request->post('email'));
            if ($email === '') {
                Yii::$app->session->setFlash('error', 'Введите email');
            } else {
                $user = User::findOne(['email' => $email]);
                if ($user && !$user->sendPasswordResetToken()) {
                    Yii::$app->session->setFlash('error', 'Ошибка отправки. Попробуйте позже.');
                } else {
                    Yii::$app->session->setFlash('success', 'Если email зарегистрирован, на него отправлена ссылка.');
                }
            }
            return $this->refresh();
        }

        return $this->render('request-password-reset');
    }

    public function actionResetPassword($token)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if (empty($token)) {
            Yii::$app->session->setFlash('error', 'Токен не указан');
            return $this->redirect(['request-password-reset']);
        }

        $user = User::findOne(['password_reset_token' => $token]);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Неверный или устаревший токен');
            return $this->redirect(['request-password-reset']);
        }

        if (Yii::$app->request->isPost) {
            $password = Yii::$app->request->post('password');
            $passwordConfirm = Yii::$app->request->post('password_confirm');

            if ($password === '') {
                Yii::$app->session->setFlash('error', 'Введите новый пароль');
            } elseif (strlen($password) < 8) {
                Yii::$app->session->setFlash('error', 'Пароль — не менее 8 символов');
            } elseif ($password !== $passwordConfirm) {
                Yii::$app->session->setFlash('error', 'Пароли не совпадают');
            } else {
                $user->password_hash = Yii::$app->security->generatePasswordHash($password);
                $user->password_reset_token = null;
                if ($user->save(false, ['password_hash', 'password_reset_token'])) {
                    Yii::$app->session->setFlash('success', 'Пароль изменён. Войдите с новым паролем.');
                    return $this->redirect(['site/login']);
                }
                Yii::$app->session->setFlash('error', 'Ошибка сохранения пароля');
            }
        }

        return $this->render('reset-password', ['token' => $token]);
    }

    public function actionResendConfirmation()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $user = Yii::$app->user->identity;
        if ($user->email_confirm_token === null) {
            Yii::$app->session->setFlash('info', 'Email уже подтверждён');
            return $this->redirect(['profile']);
        }

        $user->generateEmailConfirmToken();
        if ($user->save(false, ['email_confirm_token']) && $user->sendConfirmEmail()) {
            Yii::$app->session->setFlash('success', 'Письмо отправлено повторно');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось отправить письмо');
        }

        return $this->redirect(['profile']);
    }

    protected function findModel($id): User
    {
        $model = User::findOne(['id_user' => $id]);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Пользователь не найден');
    }
}
