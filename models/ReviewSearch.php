<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ReviewSearch extends Reviews
{
    public $product_name;
    public $user_name;
    
    public function rules()
    {
        return [
            [['id_review', 'user_id', 'product_id', 'rating', 'is_approved'], 'integer'],
            [['text', 'created_at', 'product_name', 'user_name'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Reviews::find()
            ->joinWith(['product', 'user']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id_review',
                    'rating',
                    'is_approved',
                    'created_at',
                    'product_name' => [
                        'asc' => ['product.name' => SORT_ASC],
                        'desc' => ['product.name' => SORT_DESC],
                    ],
                    'user_name' => [
                        'asc' => ['user.full_name' => SORT_ASC],
                        'desc' => ['user.full_name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'reviews.id_review' => $this->id_review,
            'reviews.user_id' => $this->user_id,
            'reviews.product_id' => $this->product_id,
            'reviews.rating' => $this->rating,
            'reviews.is_approved' => $this->is_approved,
            'DATE(reviews.created_at)' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'reviews.text', $this->text])
              ->andFilterWhere(['like', 'product.name', $this->product_name])
              ->andFilterWhere(['like', 'user.full_name', $this->user_name]);

        return $dataProvider;
    }
}