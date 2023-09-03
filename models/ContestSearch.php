<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Contest;

class ContestSearch extends Contest
{
    public function rules()
    {
        return [
            [['cid', 'hide_others', 'isvirtual', 'type', 'has_cha', 'owner_viewable'], 'integer'],
            [['title', 'description', 'start_time', 'end_time', 'lock_board_time', 'board_make', 'owner', 'report', 'mboard_make', 'allp', 'challenge_end_time', 'challenge_start_time', 'password'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Contest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'cid' => $this->cid,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'lock_board_time' => $this->lock_board_time,
            'hide_others' => $this->hide_others,
            'board_make' => $this->board_make,
            'isvirtual' => $this->isvirtual,
            'mboard_make' => $this->mboard_make,
            'type' => $this->type,
            'has_cha' => $this->has_cha,
            'challenge_end_time' => $this->challenge_end_time,
            'challenge_start_time' => $this->challenge_start_time,
            'owner_viewable' => $this->owner_viewable,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'owner', $this->owner])
            ->andFilterWhere(['like', 'report', $this->report])
            ->andFilterWhere(['like', 'allp', $this->allp])
            ->andFilterWhere(['like', 'password', $this->password]);

        return $dataProvider;
    }
}
