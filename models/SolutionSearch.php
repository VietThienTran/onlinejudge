<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Solution;
use yii\db\Query;

class SolutionSearch extends Solution
{
    public $username;

    public function rules()
    {
        return [
            [['id', 'problem_id', 'time', 'memory', 'result', 'language', 'contest_id', 'status', 'code_length'], 'integer'],
            [['created_by', 'created_at', 'ip', 'judgetime', 'judge'], 'safe'],
            [['username', 'pass_info'], 'string'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $contest = NULL)
    {
        $query = Solution::find()->with('user')->with('problem');

        if ($contest != NULL) {
            $contestId = $contest->id;
            $query = $query->where(['contest_id' => $contestId])->with([
                'contestProblem' => function (\yii\db\ActiveQuery $query) use ($contestId) {
                    $query->andWhere(['contest_id' => $contestId]);
                }
            ]);
            if (Yii::$app->user->isGuest || Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                $lockTime = strtotime($contest->lock_board_time);
                $endTime = strtotime($contest->end_time);
                $currentTime = time();
                $type = $contest->type;

                if ($type == Contest::TYPE_OI && $currentTime <= $endTime) {
                    $query->andWhere('created_by=:uid', [
                        ':uid' => Yii::$app->user->id
                    ]);
                } else if (!empty($lockTime) && $lockTime <= $currentTime && $currentTime <= $endTime + Yii::$app->setting->get('scoreboardFrozenTime')) {
                    $query->andWhere('created_by=:uid OR created_at < :lock_board_time', [
                        ':uid' => Yii::$app->user->id,
                        ':lock_board_time' => $contest->lock_board_time
                    ]);
                }
            }
        } else {
            $query = $query->where(['status' => Solution::STATUS_VISIBLE]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $created_by = '';
        if (!empty($this->username)) {
            $created_by = (new Query())->select('id')
                ->from('{{%user}}')
                ->andWhere('nickname=:name', [':name' => $this->username])
                ->orWhere('username=:name', [':name' => $this->username])
                ->scalar();
        }
        
        $query->andFilterWhere([
            'id' => $this->id,
            'problem_id' => $this->problem_id,
            'time' => $this->time,
            'memory' => $this->memory,
            'created_at' => $this->created_at,
            'result' => $this->result,
            'language' => $this->language,
            'created_by' => $created_by,
            'code_length' => $this->code_length,
            'judgetime' => $this->judgetime,
            'pass_info' => $this->pass_info,
        ]);

        $query->andFilterWhere(['like', 'created_by', $this->created_by])
            ->andFilterWhere(['like', 'judge', $this->judge]);

        return $dataProvider;
    }
}
