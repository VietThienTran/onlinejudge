<?php

namespace app\models;

use Yii;

class ContestProblem extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%contest_problem}}';
    }

    public function rules()
    {
        return [
            [['problem_id', 'contest_id', 'num'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'problem_id' => Yii::t('app', 'Problem ID'),
            'contest_id' => Yii::t('app', 'Contest ID'),
            'num' => Yii::t('app', 'Num'),
        ];
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['problem_id' => 'problem_id']);
    }
}
