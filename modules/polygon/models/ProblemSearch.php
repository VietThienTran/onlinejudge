<?php

namespace app\modules\polygon\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\modules\polygon\models\Problem;
use app\models\User;

class ProblemSearch extends Problem
{
    public $username;

    public function rules()
    {
        return [
            [['id', 'spj', 'spj_lang', 'time_limit', 'memory_limit', 'status', 'accepted', 'submit', 'solved', 'solution_lang'], 'integer'],
            [['title', 'description', 'input', 'output', 'sample_input', 'sample_output', 'spj_source', 'hint',
                'source', 'tags', 'solution_source', 'created_at', 'updated_at', 'created_by', 'username'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Problem::find()->with('user')->orderBy(['id' => SORT_DESC]);

        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role != User::ROLE_ADMIN) {
            $query->andWhere(['created_by' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!empty($this->username)) {
            $this->created_by = (new Query())->select('id')
                ->from('{{%user}}')
                ->andWhere('nickname=:name', [':name' => $this->username])
                ->orWhere('username=:name', [':name' => $this->username])
                ->scalar();
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'spj' => $this->spj,
            'spj_lang' => $this->spj_lang,
            'time_limit' => $this->time_limit,
            'memory_limit' => $this->memory_limit,
            'status' => $this->status,
            'accepted' => $this->accepted,
            'submit' => $this->submit,
            'solved' => $this->solved,
            'solution_lang' => $this->solution_lang,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'input', $this->input])
            ->andFilterWhere(['like', 'output', $this->output])
            ->andFilterWhere(['like', 'sample_input', $this->sample_input])
            ->andFilterWhere(['like', 'sample_output', $this->sample_output])
            ->andFilterWhere(['like', 'spj_source', $this->spj_source])
            ->andFilterWhere(['like', 'hint', $this->hint])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'tags', $this->tags])
            ->andFilterWhere(['like', 'solution_source', $this->solution_source]);

        return $dataProvider;
    }
}
