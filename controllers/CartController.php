<?php

namespace app\controllers;

use Yii;
use app\models\Cart;
use app\models\CartItem;
use app\models\Product;
use app\models\Favorite;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class CartController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'add', 'update', 'remove', 'clear'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['add'],
                        'roles' => ['?', '@'],
                        'matchCallback' => function () {
                            if (Yii::$app->user->isGuest) {
                                return true;
                            }
                            $u = Yii::$app->user->identity;

                            return !$u || $u->is_admin !== 'admin';
                        },
                        'denyCallback' => function () {
                            Yii::$app->session->setFlash('info', 'Корзина недоступна для учётной записи администратора.');

                            return Yii::$app->response->redirect(Yii::$app->homeUrl);
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'update', 'remove', 'clear'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            $u = Yii::$app->user->identity;

                            return !$u || $u->is_admin !== 'admin';
                        },
                        'denyCallback' => function () {
                            Yii::$app->session->setFlash('info', 'Корзина недоступна для учётной записи администратора.');

                            return Yii::$app->response->redirect(Yii::$app->homeUrl);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'add' => ['POST'],
                    'update' => ['POST'],
                    'remove' => ['POST'],
                    'clear' => ['POST'],
                    'toggle-favorite' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Просмотр корзины
     */
    public function actionIndex()
    {
        $cart = Cart::findOne(['user_id' => Yii::$app->user->id]);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->user_id = Yii::$app->user->id;
            $cart->save();
        }
        
        $items = CartItem::find()
            ->where(['cart_id' => $cart->id_cart])
            ->with('product')
            ->all();
        
        // Подгружаем акции для товаров
        foreach ($items as $item) {
            if ($item->product) {
                $item->product->getActivePromo();
            }
        }
        
        return $this->render('index', [
            'items' => $items,
            'cart' => $cart
        ]);
    }


    

public function beforeAction($action)
{
    if (!Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;
        if ($user->is_admin === 'admin') {
            Yii::$app->session->setFlash('error', 'Администратор не может работать с корзиной');
            return $this->redirect(['/admin/index'])->send();
        }
    }
    return parent::beforeAction($action);
}

    /**
     * Добавление товара в корзину
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return [
                'success' => false,
                'require_auth' => true,
                'message' => 'Чтобы добавлять товары в корзину, войдите в аккаунт или зарегистрируйтесь.',
            ];
        }
        
        $productId = Yii::$app->request->post('product_id');
        
        if (!$productId) {
            return ['success' => false, 'message' => 'ID товара не указан'];
        }
        
        $product = Product::findOne($productId);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден'];
        }
        
        if ($product->quantity <= 0) {
            return ['success' => false, 'message' => 'Товара нет в наличии'];
        }
        
        // Находим или создаем корзину пользователя
        $cart = Cart::findOne(['user_id' => Yii::$app->user->id]);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->user_id = Yii::$app->user->id;
            $cart->save();
        }
        
        // Ищем товар в корзине
        $cartItem = CartItem::findOne([
            'cart_id' => $cart->id_cart,
            'product_id' => $productId
        ]);
        
        if ($cartItem) {
            // Проверяем доступное количество
            if ($cartItem->quantity + 1 > $product->quantity) {
                return ['success' => false, 'message' => 'Недостаточно товара на складе'];
            }
            $cartItem->quantity += 1;
        } else {
            $cartItem = new CartItem();
            $cartItem->cart_id = $cart->id_cart;
            $cartItem->product_id = $productId;
            $cartItem->quantity = 1;
        }
        
        if ($cartItem->save()) {
            // Получаем общее количество товаров в корзине
            $cartCount = CartItem::find()
                ->where(['cart_id' => $cart->id_cart])
                ->sum('quantity');
            
            return [
                'success' => true,
                'message' => 'Товар добавлен в корзину',
                'cartCount' => $cartCount
            ];
        }
        
        return ['success' => false, 'message' => 'Ошибка при добавлении товара'];
    }

    /**
     * Обновление количества товара в корзине
     */
    public function actionUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Требуется авторизация'];
        }
        
        $cartItemId = Yii::$app->request->post('id');
        $quantity = Yii::$app->request->post('quantity');
        
        if (!$cartItemId) {
            return ['success' => false, 'message' => 'ID товара в корзине не указан'];
        }
        
        $cartItem = CartItem::findOne($cartItemId);
        
        if (!$cartItem) {
            return ['success' => false, 'message' => 'Товар не найден в корзине'];
        }
        
        // Проверяем принадлежность корзины пользователю
        $cart = Cart::findOne($cartItem->cart_id);
        if (!$cart || $cart->user_id != Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Нет доступа к этому товару'];
        }
        
        $product = Product::findOne($cartItem->product_id);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден'];
        }
        
        $quantity = max(1, intval($quantity));
        
        if ($quantity > $product->quantity) {
            return ['success' => false, 'message' => 'Превышено доступное количество. Доступно: ' . $product->quantity];
        }
        
        $cartItem->quantity = $quantity;
        
        if ($cartItem->save()) {
            // Получаем общую сумму корзины через представление
            $totalSum = $this->getCartTotal($cart->id_cart);
            $totalCount = CartItem::find()
                ->where(['cart_id' => $cart->id_cart])
                ->sum('quantity');
            
            return [
                'success' => true,
                'message' => 'Количество обновлено',
                'total_sum' => $totalSum,
                'total_count' => $totalCount
            ];
        }
        
        return ['success' => false, 'message' => 'Ошибка при обновлении количества'];
    }

    /**
     * Удаление товара из корзины
     */
    public function actionRemove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Требуется авторизация'];
        }
        
        $cartItemId = Yii::$app->request->post('id');
        
        if (!$cartItemId) {
            return ['success' => false, 'message' => 'ID товара в корзине не указан'];
        }
        
        $cartItem = CartItem::findOne($cartItemId);
        
        if (!$cartItem) {
            return ['success' => false, 'message' => 'Товар не найден в корзине'];
        }
        
        // Проверяем принадлежность корзины пользователю
        $cart = Cart::findOne($cartItem->cart_id);
        if (!$cart || $cart->user_id != Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Нет доступа к этому товару'];
        }
        
        if ($cartItem->delete()) {
            // Получаем общую сумму корзины через представление
            $totalSum = $this->getCartTotal($cart->id_cart);
            $totalCount = CartItem::find()
                ->where(['cart_id' => $cart->id_cart])
                ->sum('quantity');
            
            return [
                'success' => true,
                'message' => 'Товар удален из корзины',
                'total_sum' => $totalSum,
                'total_count' => $totalCount
            ];
        }
        
        return ['success' => false, 'message' => 'Ошибка при удалении товара'];
    }

    /**
     * Очистка всей корзины
     */
    public function actionClear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Требуется авторизация'];
        }
        
        $cart = Cart::findOne(['user_id' => Yii::$app->user->id]);
        
        if (!$cart) {
            return ['success' => false, 'message' => 'Корзина не найдена'];
        }
        
        $deleted = CartItem::deleteAll(['cart_id' => $cart->id_cart]);
        
        if ($deleted) {
            return [
                'success' => true,
                'message' => 'Корзина очищена',
                'total_sum' => 0,
                'total_count' => 0
            ];
        }
        
        return ['success' => false, 'message' => 'Ошибка при очистке корзины'];
    }

    /**
     * Переключение избранного
     */
    public function actionToggleFavorite()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Требуется авторизация'];
        }
        
        $productId = Yii::$app->request->post('product_id');
        
        if (!$productId) {
            return ['success' => false, 'message' => 'ID товара не указан'];
        }
        
        $favorite = Favorite::findOne([
            'user_id' => Yii::$app->user->id,
            'product_id' => $productId
        ]);
        
        if ($favorite) {
            $favorite->delete();
            $favoritesCount = Favorite::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->count();
            
            return [
                'success' => true,
                'message' => 'Удалено из избранного',
                'isFavorite' => false,
                'favoritesCount' => $favoritesCount
            ];
        } else {
            $favorite = new Favorite();
            $favorite->user_id = Yii::$app->user->id;
            $favorite->product_id = $productId;
            $favorite->save();
            
            $favoritesCount = Favorite::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->count();
            
            return [
                'success' => true,
                'message' => 'Добавлено в избранное',
                'isFavorite' => true,
                'favoritesCount' => $favoritesCount
            ];
        }
    }

    /**
     * Получение общей суммы корзины через представление v_cart_details
     */
    private function getCartTotal($cartId)
    {
        $sql = "SELECT SUM(total_price) as total FROM v_cart_details WHERE id_cart = :cart_id";
        $result = Yii::$app->db->createCommand($sql, [':cart_id' => $cartId])->queryOne();
        
        return $result['total'] ?? 0;
    }
}