<?php

namespace app\modules\polygon\models;

use app\models\Solution;
use Yii;
use yii\db\Expression;

class PolygonStatus extends Solution
{
    public static function tableName()
    {
        return '{{%polygon_status}}';
    }

    public function rules()
    {
        return [
            [['problem_id', 'result', 'time', 'memory', 'language', 'created_by'], 'integer'],
            [['info', 'source'], 'string'],
            [['created_at'], 'required'],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'problem_id' => Yii::t('app', 'Problem ID'),
            'result' => Yii::t('app', 'Result'),
            'time' => Yii::t('app', 'Time'),
            'memory' => Yii::t('app', 'Memory'),
            'info' => Yii::t('app', 'Info'),
            'created_at' => Yii::t('app', 'Created At'),
            'source' => Yii::t('app', 'Code'),
            'language' => Yii::t('app', 'Language')
        ];
    }

    public function beforeSave($insert)
    {
        return true;
    }
}
