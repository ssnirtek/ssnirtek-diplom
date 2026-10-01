<?php

namespace app\controllers;

use Yii;
use app\models\Promo;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class AdminPromoController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->is_admin === 'admin';
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-active' => ['POST'],
                ],
            ],
        ];
    }


    public function beforeAction($action)
    {
        // Устанавливаем layout для всех actions
        $this->layout = 'admin';
        return parent::beforeAction($action);
    }

    /**
     * Список всех акций
     */
   public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Promo::find(),
            'sort' => [
                'defaultOrder' => ['sort_order' => SORT_ASC]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Создание акции
     */

    
public function actionCreate()
{
    $model = new Promo();

    if ($model->load(Yii::$app->request->post())) {
        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

        // Сначала validate — FileValidator читает временный файл PHP.
        // upload() переносит файл и удаляет tmp, поэтому save() без false снова упадёт на finfo_file().
        if ($model->validate()) {
            if ($model->imageFile && !$model->upload()) {
                Yii::$app->session->setFlash('error', 'Не удалось загрузить изображение');
            } elseif ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Акция успешно создана');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при создании акции');
            }
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}

    public function actionUpdate($id_promo)
    {
        $model = $this->findModel($id_promo);
        $oldImage = $model->image_promo;

        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate()) {
                if ($model->imageFile) {
                    $model->upload();
                } else {
                    $model->image_promo = $oldImage;
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Акция обновлена');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }



    /**
     * Удаление акции
     */
    public function actionDelete($id_promo) // ИСПРАВЛЕНО: $id_promo вместо $id
    {
        $model = $this->findModel($id_promo);
        
        // Удаляем изображение
        if ($model->image_promo && file_exists(Yii::getAlias('@webroot') . $model->image_promo)) {
            unlink(Yii::getAlias('@webroot') . $model->image_promo);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Акция удалена');

        return $this->redirect(['index']);
    }

    /**
     * Переключение статуса активности
     */
    public function actionToggleActive($id_promo) // ИСПРАВЛЕНО: $id_promo вместо $id
    {
        $model = $this->findModel($id_promo);
        $model->is_active = $model->is_active ? 0 : 1;
        $model->save(false);
        
        return $this->redirect(['index']);
    }

    /**
     * Поиск модели
     */
    protected function findModel($id_promo) // ИСПРАВЛЕНО: $id_promo вместо $id
    {
        if (($model = Promo::findOne(['id_promo' => $id_promo])) !== null) { // ИСПРАВЛЕНО: ищем по id_promo
            return $model;
        }

        throw new NotFoundHttpException('Акция не найдена');
    }
}