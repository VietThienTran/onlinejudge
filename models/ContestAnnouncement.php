<?php

namespace app\models;

use Yii;

class ContestAnnouncement extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%contest_announcement}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => $this->timeStampBehavior(false),
        ];
    }

    public function rules()
    {
        return [
            [['content'], 'required'],
            [['contest_id'], 'integer'],
            [['content', 'created_at'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'contest_id' => Yii::t('app', 'Contest ID'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
