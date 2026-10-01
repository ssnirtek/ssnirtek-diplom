<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Orders;
use app\models\Product;
use app\models\User;
use app\models\Promo;
use app\models\ProductSearch;
use app\models\Category;
use app\models\ReviewSearch;
use app\models\Reviews;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\db\Expression;
use app\models\LoginForm;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['login'],
                            'allow' => true,
                            'roles' => ['?'],
                        ],
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function () {
                                $u = Yii::$app->user->identity;

                                return $u && $u->is_admin === 'admin';
                            },
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($action->id === 'login') {
            $this->layout = 'admin-login';
        } else {
            $this->layout = 'admin';
        }

        return parent::beforeAction($action);
    }

    /**
     * Вход в админ-панель (только для гостей; после входа — только роль admin).
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->is_admin === 'admin') {
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('error', 'Вы уже вошли как обычный пользователь. Используйте выход.');

            return $this->redirect(['/site/index']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            if ($user->is_admin !== 'admin') {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Доступ к админ-панели только для администратора.');

                return $this->refresh();
            }

            return $this->redirect(['index']);
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Главная страница админ-панели
     */
    public function actionIndex()
    {
        $today = date('Y-m-d');
        $stats = [
            'orders_today' => Orders::find()
                ->where(new Expression('DATE(created_at) = :d', [':d' => $today]))
                ->count(),
            'orders_total' => Orders::find()->count(),
            'products_total' => Product::find()->count(),
            'products_low_stock' => Product::find()->where(['<', 'quantity', 5])->count(),
            'users_total' => User::find()->count(),
            'promo_active' => Promo::find()->where(['is_active' => 1])->count(),
        ];

        // Последние заказы
        $recentOrders = new ActiveDataProvider([
            'query' => Orders::find()->orderBy(['created_at' => SORT_DESC])->limit(10),
            'pagination' => false,
        ]);

        // Товары с малым остатком
        $lowStockProducts = new ActiveDataProvider([
            'query' => Product::find()->where(['<', 'quantity', 5])->orderBy(['quantity' => SORT_ASC]),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    /**
     * Управление заказами
     */
    public function actionOrders()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Orders::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('orders', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Управление товарами
     */
    public function actionProducts()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;

        $categories = Category::find()->all();

        return $this->render('products', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
        ]);
    }

    /**
     * Редактирование товара
     */
    public function actionUpdateProduct($id)
    {
        $model = Product::findOne($id);
        
        if (!$model) {
            throw new NotFoundHttpException('Товар не найден');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Товар успешно обновлен');
            return $this->redirect(['products']);
        }

        return $this->render('update-product', [
            'model' => $model,
        ]);
    }

    /**
     * Просмотр деталей заказа
     */
    public function actionOrderView($id)
    {
        $order = Orders::findOne($id);
        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден');
        }

        // Получаем товары в заказе
        $items = $order->orderItems;

        return $this->render('order-view', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    /**
     * Управление отзывами
     */
    public function actionReviews()
    {
        $searchModel = new ReviewSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;

        return $this->render('reviews', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ============================================================
    // AJAX METHODS
    // ============================================================

    /**
     * Обновление количества товара
     */
    public function actionUpdateProductQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $productId = Yii::$app->request->post('product_id');
        $quantity = Yii::$app->request->post('quantity');

        $product = Product::findOne($productId);
        if ($product) {
            $product->quantity = $quantity;
            if ($product->save()) {
                return ['success' => true, 'message' => 'Количество обновлено'];
            }
        }
        return ['success' => false, 'message' => 'Ошибка обновления'];
    }

    /**
     * Быстрое обновление количества товара (AJAX)
     */
  // controllers/AdminController.php

public function actionQuickUpdateQuantity()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    // Проверка на AJAX запрос
    if (!Yii::$app->request->isAjax) {
        return ['success' => false, 'message' => 'Только AJAX запросы разрешены'];
    }
    
    $id = Yii::$app->request->post('id');
    $quantity = Yii::$app->request->post('quantity');
    
    // Логирование для отладки
    Yii::info("Updating product ID: $id, Quantity: $quantity", 'admin');
    
    if (!$id || $quantity === null) {
        return ['success' => false, 'message' => 'Не указаны обязательные параметры'];
    }
    
    // Найдите вашу модель продукта (имя модели может отличаться)
    $product = \app\models\Product::findOne($id);
    
    if (!$product) {
        return ['success' => false, 'message' => 'Товар не найден'];
    }
    
    $product->quantity = (int)$quantity;
    
    if ($product->save()) {
        return [
            'success' => true, 
            'message' => 'Количество обновлено',
            'quantity' => $product->quantity
        ];
    } else {
        return [
            'success' => false, 
            'message' => 'Ошибка при сохранении: ' . implode(', ', $product->getErrorSummary(true))
        ];
    }
}

    /**
     * Быстрое обновление статуса товара (AJAX)
     */
    public function actionToggleProductStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $productId = Yii::$app->request->post('product_id');
        $product = Product::findOne($productId);
        
        if ($product) {
            $product->is_active = $product->is_active ? 0 : 1;
            if ($product->save()) {
                return [
                    'success' => true, 
                    'message' => 'Статус обновлен',
                    'status' => $product->is_active
                ];
            }
        }
        return ['success' => false, 'message' => 'Ошибка обновления'];
    }

    /**
     * Обновление статуса заказа (AJAX)
     */
    public function actionUpdateOrderStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Проверяем CSRF токен
        if (!Yii::$app->request->validateCsrfToken()) {
            return ['success' => false, 'message' => 'Ошибка CSRF-валидации'];
        }
        
        $orderId = Yii::$app->request->post('order_id');
        $status = Yii::$app->request->post('status');
        
        if (!$orderId || !$status) {
            return ['success' => false, 'message' => 'Не переданы все необходимые данные'];
        }
        
        $order = Orders::findOne($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Заказ не найден'];
        }
        
        $order->status = $status;
        if ($order->save()) {
            return [
                'success' => true,
                'message' => 'Статус заказа обновлен',
                'status' => $status,
                'status_text' => $order->getStatusLabel(),
                'status_class' => $order->getStatusClass()
            ];
        } else {
            return ['success' => false, 'message' => 'Ошибка сохранения: ' . json_encode($order->errors)];
        }
    }



    public function actionApproveReview()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    try {
        $reviewId = Yii::$app->request->post('review_id');
        
        if (!$reviewId) {
            return ['success' => false, 'message' => 'ID отзыва не указан'];
        }
        
        $review = Reviews::findOne($reviewId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Отзыв не найден'];
        }
        
        $review->is_approved = 1;
        
        if ($review->save()) {
            // Логируем действие
            Yii::info("Отзыв ID {$reviewId} одобрен администратором", 'review');
            return ['success' => true, 'message' => 'Отзыв одобрен'];
        } else {
            return ['success' => false, 'message' => 'Ошибка при сохранении: ' . json_encode($review->errors)];
        }
    } catch (\Exception $e) {
        Yii::error("Ошибка при одобрении отзыва: " . $e->getMessage(), 'review');
        return ['success' => false, 'message' => 'Произошла ошибка на сервере'];
    }
}

public function actionRejectReview()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    try {
        $reviewId = Yii::$app->request->post('review_id');
        
        if (!$reviewId) {
            return ['success' => false, 'message' => 'ID отзыва не указан'];
        }
        
        $review = Reviews::findOne($reviewId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Отзыв не найден'];
        }
        
        $review->is_approved = 0;
        
        if ($review->save()) {
            Yii::info("Отзыв ID {$reviewId} отклонен администратором", 'review');
            return ['success' => true, 'message' => 'Отзыв отклонен'];
        } else {
            return ['success' => false, 'message' => 'Ошибка при сохранении'];
        }
    } catch (\Exception $e) {
        Yii::error("Ошибка при отклонении отзыва: " . $e->getMessage(), 'review');
        return ['success' => false, 'message' => 'Произошла ошибка на сервере'];
    }
}

public function actionDeleteReview()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    try {
        $reviewId = Yii::$app->request->post('review_id');
        
        if (!$reviewId) {
            return ['success' => false, 'message' => 'ID отзыва не указан'];
        }
        
        $review = Reviews::findOne($reviewId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Отзыв не найден'];
        }
        
        if ($review->delete()) {
            Yii::info("Отзыв ID {$reviewId} удален администратором", 'review');
            return ['success' => true, 'message' => 'Отзыв удален'];
        } else {
            return ['success' => false, 'message' => 'Ошибка при удалении'];
        }
    } catch (\Exception $e) {
        Yii::error("Ошибка при удалении отзыва: " . $e->getMessage(), 'review');
        return ['success' => false, 'message' => 'Произошла ошибка на сервере'];
    }
}
}