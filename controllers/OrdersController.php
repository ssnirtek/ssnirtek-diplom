<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Orders;
use app\models\OrderItem;
use app\models\Cart;
use app\models\CartItem;
use yii\web\NotFoundHttpException;

class OrdersController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    /** Редirect на просмотр в личном кабинете */
    public function actionView($id)
    {
        $order = Orders::findOne($id);
        if (!$order || $order->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('Заказ не найден');
        }

        return $this->redirect(['/user/order-view', 'id' => $id]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->is_admin === 'admin') {
            Yii::$app->session->setFlash('info', 'Оформление заказов недоступно для администратора');
            return $this->redirect(Yii::$app->homeUrl);
        }

        $cart = Cart::getCurrentCart();
        if (!$cart) {
            return $this->redirect(['/cart']);
        }

        $cartItems = CartItem::find()
            ->where(['cart_id' => $cart->id_cart])
            ->with('product')
            ->all();

        if ($cartItems === []) {
            return $this->redirect(['/cart']);
        }

        foreach ($cartItems as $cartItem) {
            if ($cartItem->product) {
                $cartItem->product->getActivePromo();
            }
        }
        $cart->populateRelation('cartItems', $cartItems);

        $order = new Orders([
            'user_id' => Yii::$app->user->id,
            'status' => 'pending',
        ]);
        $this->applyOrderAmountsFromCart($order, $cart);

        if ($order->load(Yii::$app->request->post()) && $order->validate()) {
            $this->applyOrderAmountsFromCart($order, $cart);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($order->save()) {
                    foreach ($cart->cartItems as $cartItem) {
                        $orderItem = new OrderItem([
                            'order_id' => $order->id_orders,
                            'product_id' => $cartItem->product_id,
                            'quantity' => $cartItem->quantity,
                            'price' => $cartItem->product->getFinalPrice(),
                        ]);
                        $orderItem->save();

                        $product = $cartItem->product;
                        $product->quantity -= $cartItem->quantity;
                        $product->save();
                    }

                    $cart->clear();
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Заказ успешно оформлен');
                    return $this->redirect(['/user/orders']);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Ошибка при оформлении: ' . $e->getMessage());
            }
        } elseif ($order->hasErrors()) {
            Yii::$app->session->setFlash('error', 'Ошибка: ' . implode('; ', $order->getFirstErrors()));
        }

        return $this->render('create', compact('order', 'cart', 'cartItems'));
    }

    private function applyOrderAmountsFromCart(Orders $order, Cart $cart): void
    {
        if ($order->hasAttribute('subtotal_amount')) {
            $order->subtotal_amount = $cart->getSubtotalAfterPromos();
            $order->welcome_discount_amount = $cart->getWelcomeDiscountAmount();
        }
        $order->total_amount = $cart->getAmountToPay();
    }
}
