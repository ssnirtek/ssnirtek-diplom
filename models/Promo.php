<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "promo".
 *
 * @property int $id_promo
 * @property string $title
 * @property string $description
 * @property string $image_promo
 * @property int|null $sort_order
 * @property int|null $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Promo extends ActiveRecord
{
    public $imageFile;
   public $product_ids = [];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promo';
    }

    /**
     * {@inheritdoc}
     */
public function rules()
{
    return [
            [['title'], 'required'],
            [['description', 'conditions'], 'string'],
            [['discount_percent', 'sort_order'], 'integer'],
            [['discount_percent'], 'integer', 'min' => 0, 'max' => 100],
            [['start_date', 'end_date'], 'safe'],
            [['is_active'], 'boolean'],
            [['title'], 'string', 'max' => 255],
            [['imageFile'], 'file', 'extensions' => 'png, jpg, jpeg, webp', 'maxSize' => 5 * 1024 * 1024],
            [['product_ids'], 'safe'],
        ];
}

    /**
     * {@inheritdoc}
     */
   public function attributeLabels()
    {
        return [
            'id_promo' => 'ID',
            'title' => 'Название акции',
            'description' => 'Описание',
            'discount_percent' => 'Скидка (%)',
            'conditions' => 'Условия',
            'start_date' => 'Дата начала',
            'end_date' => 'Дата окончания',
            'is_active' => 'Активно',
            'sort_order' => 'Порядок сортировки',
            'image_promo' => 'Изображение',
            'imageFile' => 'Загрузить изображение',
            'product_ids' => 'Товары по акции',
        ];
    }

  /**
 * Загрузка изображения
 * @return bool
 */
public function uploadImage()
{
    if ($this->imageFile instanceof UploadedFile) {
        // Проверка расширения вручную
        $extension = strtolower($this->imageFile->extension);
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];
        
        if (!in_array($extension, $allowed)) {
            $this->addError('imageFile', 'Разрешены только файлы: ' . implode(', ', $allowed));
            return false;
        }
        
        // Проверка размера (2MB)
        if ($this->imageFile->size > 2 * 1024 * 1024) {
            $this->addError('imageFile', 'Максимальный размер файла 2MB');
            return false;
        }
        
        $path = 'images/promo/';
        $fullPath = Yii::getAlias('@webroot') . '/' . $path;
        
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        
        $fileName = 'promo_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $fullPath . $fileName;
        
        if ($this->imageFile->saveAs($filePath)) {
            $this->image_promo = '/' . $path . $fileName;
            return true;
        }
    }
    return false;
}
public function upload()
{
    if ($this->imageFile instanceof UploadedFile) {
        $path = 'images/promo/';
        $fullPath = Yii::getAlias('@webroot') . '/' . $path;
        
        // Создаем директорию если не существует
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        
        $fileName = 'promo_' . time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $this->imageFile->extension;
        $filePath = $fullPath . $fileName;
        
        // Сохраняем файл
        if ($this->imageFile->saveAs($filePath)) {
            // Удаляем старое изображение, если оно есть
            if (!empty($this->image_promo) && file_exists(Yii::getAlias('@webroot') . $this->image_promo)) {
                @unlink(Yii::getAlias('@webroot') . $this->image_promo);
            }
            // Сохраняем путь в базу
            $this->image_promo = '/' . $path . $fileName;
            return true;
        }
    }
    return false;
}


    /**
     * Получить активные акции
     */
    public static function getActivePromos()
    {
        return self::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Акции для публичного блока: флаг активности и окно дат (как {@see isActiveNow()}).
     *
     * @return self[]
     */
    public static function getActivePromosForPublic()
    {
        $today = date('Y-m-d');

        return self::find()
            ->where(['is_active' => 1])
            ->andWhere(['or', ['start_date' => null], ['<=', 'start_date', $today]])
            ->andWhere(['or', ['end_date' => null], ['>=', 'end_date', $today]])
            ->orderBy(['sort_order' => SORT_ASC, 'id_promo' => SORT_ASC])
            ->all();
    }

/**
 * Проверяет, активна ли акция сейчас
 */
public function isActiveNow()
{
    $now = date('Y-m-d');
    
    // Проверка дат
    if ($this->start_date && $this->start_date > $now) {
        return false;
    }
    if ($this->end_date && $this->end_date < $now) {
        return false;
    }
    
    return $this->is_active == 1;
}

/**
 * Получить все активные акции
 */
public static function getActivePromosWithProducts()
{
    $now = date('Y-m-d');
    
    return self::find()
        ->where(['is_active' => 1])
        ->andWhere(['<=', 'start_date', $now])
        ->andWhere(['>=', 'end_date', $now])
        ->orderBy(['sort_order' => SORT_ASC])
        ->with('products')
        ->all();
}
/**
 * Получить скидку для товара
 */
public static function getProductDiscount($productId)
{
    $promos = self::getActivePromosWithProducts();
    
    foreach ($promos as $promo) {
        foreach ($promo->products as $product) {
            if ($product->id_product == $productId) {
                return $promo->discount_percent;
            }
        }
    }
    
    return 0;
}



public function afterFind()
{
    parent::afterFind();
    $this->product_ids = $this->getProducts()
        ->select('id_product')
        ->column();
}
/**
 * Проверить, участвует ли товар в какой-либо акции
 */
public static function isProductInPromo($productId)
{
    return self::getProductDiscount($productId) > 0;
}/**
     * Связь с товарами через промежуточную таблицу
     */
    public function getPromoProducts()
    {
        return $this->hasMany(PromoProduct::class, ['promo_id' => 'id_promo']);
    }

    /**
     * Получить все товары, участвующие в акции
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id_product' => 'product_id'])
            ->via('promoProducts');
    }

    /**
     * Применить скидку ко всем товарам акции
     */
    public function applyDiscount()
    {
        if (!$this->is_active || !$this->discount_percent) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->promoProducts as $promoProduct) {
                $promoProduct->updateProductPrice();
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка применения скидки: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Отменить скидку для всех товаров акции
     */
    public function cancelDiscount()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->promoProducts as $promoProduct) {
                $promoProduct->restoreProductPrice();
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка отмены скидки: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * После сохранения акции
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Обновляем связи с товарами
        $this->updateProductLinks();
        
        // Проверяем, изменился ли статус активности или процент скидки
        $needUpdatePrices = $insert || 
            isset($changedAttributes['is_active']) || 
            isset($changedAttributes['discount_percent']);
        
        if ($needUpdatePrices) {
            if ($this->is_active) {
                $this->applyDiscount();
            } else {
                $this->cancelDiscount();
            }
        }
    }

    /**
     * Обновление связей с товарами
     */
    protected function updateProductLinks()
    {
        if (empty($this->product_ids)) {
            return;
        }

        // Удаляем старые связи (это автоматически восстановит цены через beforeDelete)
        PromoProduct::deleteAll(['promo_id' => $this->id_promo]);
        
        // Добавляем новые связи
        foreach ($this->product_ids as $productId) {
            $link = new PromoProduct();
            $link->promo_id = $this->id_promo;
            $link->product_id = $productId;
            $link->save();
        }
    }

    /**
     * Перед удалением акции
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        
        // Отменяем скидку перед удалением акции
        $this->cancelDiscount();
        
        return true;
    }



}