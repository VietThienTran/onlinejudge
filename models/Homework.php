<?php

namespace app\models;

use Yii;

class Homework extends Contest
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_PRIVATE = 2;

    public function rules()
    {
        return [
            [['title', 'start_time', 'end_time'], 'required'],
            [['description', 'editorial'], 'string'],
            [['created_by'], 'integer'],
            [['start_time', 'end_time', 'lock_board_time'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['id', 'status', 'type', 'scenario', 'created_by', 'group_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
            'created_by' => Yii::t('app', 'Created By'),
            'description' => Yii::t('app', 'Description'),
            'editorial' => Yii::t('app', 'Editorial'),
            'lock_board_time' => Yii::t('app', 'Lock Board Time'),
        ];
    }

    public function hasPermission()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($this->group_id == 0) {
            return false;
        }

        if ($this->created_by == Yii::$app->user->id) {
            return true;
        }
        if ($this->group->hasPermission()) {
            return true;
        }
        return false;
    }
}
