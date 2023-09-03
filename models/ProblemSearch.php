<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProblemSearch extends Problem
{
    public function rules()
    {
        return [
            [['id', 'time_limit', 'memory_limit', 'accepted', 'submit', 'solved'], 'integer'],
            [['title', 'description', 'input', 'output', 'sample_input', 'sample_output', 'spj', 'hint', 'source', 'created_at', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Problem::find()->orderBy(['id' => SORT_DESC])->with('user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'time_limit' => $this->time_limit,
            'memory_limit' => $this->memory_limit,
            'accepted' => $this->accepted,
            'submit' => $this->submit,
            'solved' => $this->solved,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'input', $this->input])
            ->andFilterWhere(['like', 'output', $this->output])
            ->andFilterWhere(['like', 'sample_input', $this->sample_input])
            ->andFilterWhere(['like', 'sample_output', $this->sample_output])
            ->andFilterWhere(['like', 'spj', $this->spj])
            ->andFilterWhere(['like', 'hint', $this->hint])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
