<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\Favorite;
use app\models\Category;
use app\models\Reviews;
use app\models\OrderItem;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class ProductController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['add-review' => ['POST']],
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => ['add-review'],
                'rules' => [
                    ['actions' => ['add-review'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    /** Каталог — единая точка списка товаров */
    public function actionIndex()
    {
        return $this->redirect(array_merge(['/catalog/index'], Yii::$app->request->queryParams));
    }

    public function actionView($id)
    {
        $product = Product::findOne($id);
        if (!$product || !$product->is_active) {
            throw new NotFoundHttpException('Товар не найден');
        }

        $canReview = false;
        $userHasOrderedProduct = false;
        $userAlreadyReviewed = false;
        if (!Yii::$app->user->isGuest) {
            $uid = (int) Yii::$app->user->id;
            $pid = (int) $product->id_product;
            $userHasOrderedProduct = OrderItem::userHasOrderedProduct($uid, $pid);
            $userAlreadyReviewed = Reviews::userHasReviewForProduct($uid, $pid);
            $canReview = $userHasOrderedProduct && !$userAlreadyReviewed;
        }

        return $this->render('view', [
            'product' => $product,
            'reviews' => Reviews::find()
                ->where(['product_id' => $id, 'is_approved' => 1])
                ->orderBy(['created_at' => SORT_DESC])
                ->with('user')
                ->all(),
            'relatedProducts' => Product::find()
                ->where(['category_id' => $product->category_id, 'is_active' => 1])
                ->andWhere(['!=', 'id_product', $id])
                ->limit(4)
                ->all(),
            'canReview' => $canReview,
            'userHasOrderedProduct' => $userHasOrderedProduct,
            'userAlreadyReviewed' => $userAlreadyReviewed,
        ]);
    }

    public function actionCreate()
    {
        if (Yii::$app->session->get('product_creating')) {
            Yii::$app->session->setFlash('warning', 'Подождите, предыдущий товар ещё создаётся…');
            return $this->redirect(['/admin/products']);
        }

        $model = new Product();
        $categories = Category::find()
            ->select(['id_category', 'category_name'])
            ->orderBy(['category_name' => SORT_ASC])
            ->all();

        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->session->set('product_creating', true);
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if (!$model->validate()) {
                Yii::$app->session->remove('product_creating');
                Yii::$app->session->setFlash('error', 'Ошибка валидации: ' . implode('; ', $model->getFirstErrors()));
                return $this->render('create', compact('model', 'categories'));
            }

            if ($model->save()) {
                if ($model->imageFile && $model->uploadImage()) {
                    $model->save(false);
                }
                Yii::$app->session->remove('product_creating');
                Yii::$app->session->setFlash('success', 'Товар успешно создан');
                return $this->redirect(['view', 'id' => $model->id_product]);
            }

            Yii::$app->session->remove('product_creating');
            Yii::$app->session->setFlash('error', 'Ошибка при создании товара');
        }

        return $this->render('create', compact('model', 'categories'));
    }

    public function actionAddReview()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Необходимо авторизоваться');
            return $this->redirect(['/site/login']);
        }

        $request = Yii::$app->request;
        $productId = (int) $request->post('product_id');
        if ($productId <= 0) {
            Yii::$app->session->setFlash('error', 'Не указан товар');
            return $this->redirect($request->referrer ?: ['/catalog/index']);
        }

        $product = Product::findOne(['id_product' => $productId, 'is_active' => 1]);
        if ($product === null) {
            Yii::$app->session->setFlash('error', 'Товар не найден');
            return $this->redirect($request->referrer ?: ['/catalog/index']);
        }

        $userId = (int) Yii::$app->user->id;
        if (!OrderItem::userHasOrderedProduct($userId, $productId)) {
            Yii::$app->session->setFlash('error', 'Отзыв можно оставить только на заказанный товар');
            return $this->redirect(['view', 'id' => $productId, '#' => 'product-reviews']);
        }
        if (Reviews::userHasReviewForProduct($userId, $productId)) {
            Yii::$app->session->setFlash('error', 'Вы уже оставляли отзыв на этот товар');
            return $this->redirect(['view', 'id' => $productId, '#' => 'product-reviews']);
        }

        $rating = max(1, min(5, (int) $request->post('rating', 5)));
        $review = new Reviews([
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'text' => (string) $request->post('text', ''),
            'is_approved' => 0,
        ]);

        if ($review->save()) {
            Yii::$app->session->setFlash('success', 'Спасибо за отзыв! Он будет опубликован после проверки.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка: ' . implode('; ', $review->getFirstErrors()));
        }

        return $this->redirect(['view', 'id' => $productId, '#' => 'product-reviews']);
    }
}
