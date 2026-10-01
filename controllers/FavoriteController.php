<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Favorite;
use app\models\Product;

class FavoriteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['toggle', 'index', 'remove'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['toggle'],
                        'roles' => ['?', '@'],
                        'matchCallback' => function () {
                            if (Yii::$app->user->isGuest) {
                                return true;
                            }
                            $u = Yii::$app->user->identity;

                            return !$u || $u->is_admin !== 'admin';
                        },
                        'denyCallback' => function () {
                            Yii::$app->session->setFlash('info', 'Избранное недоступно для учётной записи администратора.');

                            return Yii::$app->response->redirect(Yii::$app->homeUrl);
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'remove'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            $u = Yii::$app->user->identity;

                            return !$u || $u->is_admin !== 'admin';
                        },
                        'denyCallback' => function () {
                            Yii::$app->session->setFlash('info', 'Избранное недоступно для учётной записи администратора.');

                            return Yii::$app->response->redirect(Yii::$app->homeUrl);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'toggle' => ['POST'],
                    'remove' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Переключение избранного (добавить/удалить)
     */
    public function actionToggle()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $productId = Yii::$app->request->post('product_id');
        
        if (!$productId) {
            return ['success' => false, 'message' => 'ID товара не указан'];
        }
        
        $product = Product::findOne($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден'];
        }
        
        // Используем метод toggle из модели
        $result = Favorite::toggle($productId);
        
        // Добавляем количество избранных товаров
        if ($result['success']) {
            $result['favoritesCount'] = Favorite::getFavoritesCount();
        }
        
        return $result;
    }
    
    /**
     * Удаление из избранного
     */
    public function actionRemove()
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
        
        if ($favorite && $favorite->delete()) {
            return [
                'success' => true,
                'message' => 'Товар удален из избранного',
                'action' => 'removed',
                'favoritesCount' => Favorite::getFavoritesCount()
            ];
        }
        
        return ['success' => false, 'message' => 'Ошибка при удалении'];
    }
    
    /**
     * Страница избранного
     */
    public function actionIndex()
    {
        $this->view->title = 'Избранное';

        $favoritesQuery = Favorite::getUserFavorites();
        $favorites = $favoritesQuery->all();

        return $this->render('index', [
            'favorites' => $favorites,
        ]);
    }
}