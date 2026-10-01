<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use app\models\Product;
use app\models\Category;
use app\models\Promo;
use app\models\Cart;
use app\models\Favorite;
use app\models\CartItem;
use app\components\CatalogPageSize;

class CatalogController extends Controller
{
    public function actionIndex()
    {
        $query = Product::find()->where(['is_active' => 1]);

        $categoryId = Yii::$app->request->get('category_id');
        if ($categoryId !== null && $categoryId !== '' && (int) $categoryId > 0) {
            $query->andWhere(['category_id' => (int) $categoryId]);
        }

        $discountOnly = Yii::$app->request->get('discount_only');
        if ($discountOnly) {
            $query->andWhere('is_discount = 1 OR id_product IN (SELECT DISTINCT product_id FROM promo_product)');
        }

        $sort = Yii::$app->request->get('sort', '');
        switch ($sort) {
            case 'created_at_asc':
                $query->orderBy(['created_at' => SORT_ASC]);
                break;
            case 'created_at_desc':
                $query->orderBy(['created_at' => SORT_DESC]);
                break;
            case 'price_asc':
                $query->orderBy(['price' => SORT_ASC]);
                break;
            case 'price_desc':
                $query->orderBy(['price' => SORT_DESC]);
                break;
            case 'name_asc':
                $query->orderBy(['name' => SORT_ASC]);
                break;
            case 'name_desc':
                $query->orderBy(['name' => SORT_DESC]);
                break;
            default:
                $query->orderBy(['created_at' => SORT_DESC]);
                break;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => CatalogPageSize::resolveFromRequest(),
                'pageSizeParam' => false,
                'validatePage' => true,
            ],
        ]);

        $categories = Category::find()
            ->select(['id_category', 'category_name'])
            ->orderBy(['category_name' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'success' => true,
                'html' => $this->renderPartial('_products_grid', [
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }

        $promos = Promo::getActivePromosForPublic();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'promos' => $promos,
            'categories' => $categories,
        ]);
    }
    
    /**
     * Добавление в корзину
     */
    public function actionAddToCart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'Требуется авторизация'];
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
        
        $cart = \app\models\Cart::getCurrentCart();
        
        if (!$cart) {
            $cart = new \app\models\Cart();
            $cart->user_id = Yii::$app->user->id;
            $cart->save();
        }
        
        $cartItem = \app\models\CartItem::findOne([
            'cart_id' => $cart->id_cart,
            'product_id' => $productId
        ]);
        
        if ($cartItem) {
            if ($cartItem->quantity + 1 > $product->quantity) {
                return ['success' => false, 'message' => 'Недостаточно товара на складе'];
            }
            $cartItem->quantity += 1;
        } else {
            $cartItem = new \app\models\CartItem();
            $cartItem->cart_id = $cart->id_cart;
            $cartItem->product_id = $productId;
            $cartItem->quantity = 1;
        }
        
        if ($cartItem->save()) {
            $cartCount = \app\models\CartItem::find()
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


public function actionToggleFavorite()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return [
            'success' => false, 
            'message' => 'Для добавления в избранное необходимо авторизоваться'
        ];
    }

    $productId = Yii::$app->request->post('product_id');
    return Favorite::toggle($productId);
}

    public function actionCheckFavorite()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $productId = Yii::$app->request->post('product_id');
        return ['isFavorite' => Favorite::isFavorite($productId)];
    }


}