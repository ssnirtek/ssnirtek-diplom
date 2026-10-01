<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Reviews; 
use app\models\Promo;
use yii\db\Query;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    
public function actionIndex()
{
    // Получаем активные акции
    $promos = Promo::getActivePromosForPublic();
    
    // Получаем последние 3 товара
    $products = (new Query())
        ->select([
            'p.id_product', 
            'p.name', 
            'p.description', 
            'p.price', 
            'p.image_product', 
            'p.old_price', 
            'p.is_discount',
            'p.quantity',
            'c.category_name'
        ])
        ->from('product p')
        ->leftJoin('category c', 'p.category_id = c.id_category')
        ->where(['p.is_active' => 1])
        ->orderBy(['p.created_at' => SORT_DESC])
        ->limit(3)
        ->all();

    // Получаем акции для товаров через отдельный запрос
    $productIds = array_column($products, 'id_product');
    $promoByProduct = [];

    if (!empty($productIds)) {
        // Формируем строку с ID для запроса
        $idsString = implode(',', $productIds);
        
        // Выполняем прямой SQL запрос
        $connection = Yii::$app->getDb();
        $sql = "SELECT pp.product_id, p.discount_percent 
                FROM promo_product pp
                LEFT JOIN promo p ON pp.promo_id = p.id_promo
                WHERE pp.product_id IN ({$idsString})
                AND p.is_active = 1
                AND p.start_date <= CURDATE()
                AND p.end_date >= CURDATE()";
        
        $promoProducts = $connection->createCommand($sql)->queryAll();
        
        foreach ($promoProducts as $pp) {
            $promoByProduct[$pp['product_id']] = $pp['discount_percent'];
        }
    }
    
    // Получаем последние отзывы
    $latestReviews = Reviews::find()
        ->where(['is_approved' => 1])
        ->andWhere(['not', ['product_id' => null]])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();

    return $this->render('index', [
        'promos' => $promos,
        'products' => $products,
        'promoByProduct' => $promoByProduct,
        'latestReviews' => $latestReviews,
    ]);
}
    /**
     * Login action.
     *
     * @return Response|string
     */
 // controllers/SiteController.php

public function actionLogin()
{
    // Если пользователь уже авторизован
    if (!Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;
        
        // Проверяем роль и перенаправляем админа на админ-панель
        if ($user->is_admin === 'admin') {
            return $this->redirect(['/admin/index']);
        }
        
        // Обычного пользователя перенаправляем на главную
        return $this->goHome();
    }

    $model = new LoginForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        $user = Yii::$app->user->identity;
        
        // ✅ ПРОВЕРКА: ПОДТВЕРЖДЁН ЛИ EMAIL
        if ($user->email_confirm_token !== null) {
            // Если токен ещё не удалён — email не подтверждён
            Yii::$app->session->setFlash('warning', 
                '⚠ Ваш email ещё не подтверждён. Проверьте почту или ' . 
                \yii\helpers\Html::a('запросите повторную отправку', ['user/resend-confirmation'], [
                    'style' => 'color: #ff4081; text-decoration: underline;'
                ])
            );
            
            // Всё равно пускаем в профиль (или можно разлогинить — решать вам)
            // Вариант 1: Пускать, но с предупреждением
            if ($user->is_admin === 'admin') {
                return $this->redirect(['/admin/index']);
            }
            return $this->redirect(['user/profile']);
            
            // Вариант 2: Не пускать, пока не подтвердит (раскомментируйте вместо верхнего)
            // Yii::$app->user->logout();
            // Yii::$app->session->setFlash('error', 
            //     ' Ваш email не подтверждён. Проверьте почту и перейдите по ссылке в письме.');
            // return $this->refresh();
        }
        
        // Email подтверждён — всё ок
        if ($user->is_admin === 'admin') {
            return $this->redirect(['/admin/index']);
        }
        return $this->redirect(['user/profile']);
    }

    $model->password = '';
    return $this->render('login', [
        'model' => $model,
    ]);
}

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionRequestPasswordReset()
    {
        $model = new \app\models\PasswordResetRequestForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Проверьте вашу почту для дальнейших инструкций.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Извините, не удалось отправить письмо. Проверьте правильность email.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }
    
    public function actionResetPassword($token)
    {
        try {
            $model = new \app\models\ResetPasswordForm($token);
        } catch (\yii\base\InvalidArgumentException $e) {
            throw new \yii\web\BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Новый пароль сохранен.');
            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        $latestReviews = Reviews::getLatestReviews(5);
        $promos = Promo::getActivePromosForPublic();
        return $this->render('about', [
            'latestReviews' => $latestReviews,
            'promos' => $promos,
        ]);
    }

    /**
     * Политика конфиденциальности.
     *
     * @return string
     */
    public function actionPolicy()
    {
        return $this->render('policy');
    }
}