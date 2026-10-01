<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProductSearch extends Product
{
    public $category_name;
    
    public function rules()
    {
        return [
            [['id_product', 'category_id', 'is_discount', 'quantity', 'is_active'], 'integer'],
            [['name', 'description', 'category_name'], 'safe'],
            [['price', 'old_price'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Product::find()
            ->joinWith(['category']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id_product' => SORT_DESC],
                'attributes' => [
                    'id_product',
                    'name',
                    'price',
                    'quantity',
                    'is_active',
                    'category_name' => [
                        'asc' => ['category.category_name' => SORT_ASC],
                        'desc' => ['category.category_name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id_product' => $this->id_product,
            'category_id' => $this->category_id,
            'price' => $this->price,
            'is_discount' => $this->is_discount,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['like', 'product.name', $this->name])
              ->andFilterWhere(['like', 'category.category_name', $this->category_name]);

        return $dataProvider;
    }
}