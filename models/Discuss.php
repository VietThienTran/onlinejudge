<?php

namespace app\models;

use Yii;

class Discuss extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLIC = 1;
    const STATUS_PRIVATE = 2;

    const ENTITY_CONTEST = 'contest';
    const ENTITY_PROBLEM = 'problem';
    const ENTITY_NEWS = 'news';

    public static function tableName()
    {
        return '{{%discuss}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => $this->timeStampBehavior(),
        ];
    }

    public function rules()
    {
        return [
            [['created_by', 'problem_id', 'status', 'contest_id', 'parent_id', 'entity_id'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLIC, self::STATUS_PRIVATE]],
            [['title', 'content', 'entity', 'created_at', 'updated_at'], 'string'],
            [['title'], 'required', 'on' => 'problem']
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['title', 'content', 'status'],
            'problem' => ['title', 'content'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Last reply time'),
            'created_at' => Yii::t('app', 'Created At'),
            'content' => Yii::t('app', 'Content'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getReply()
    {
        return Discuss::findAll(['parent_id' => $this->id]);
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['id' => 'entity_id']);
    }
}
