<?php

namespace app\models;

use Yii;

class SolutionInfo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%solution_info}}';
    }

    public function rules()
    {
        return [
            [['solution_id'], 'required'],
            [['solution_id'], 'integer'],
            [['error'], 'string'],
            [['solution_id'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'solution_id' => Yii::t('app', 'Solution ID'),
            'error' => Yii::t('app', 'Error'),
        ];
    }
}
