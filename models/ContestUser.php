<?php

namespace app\models;

use Yii;

class ContestUser extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%contest_user}}';
    }
    
    public function rules()
    {
        return [
            [['user_id', 'contest_id'], 'required'],
            [['user_id', 'contest_id', 'rating_change'], 'integer'],
            [['user_password'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'contest_id' => 'Contest ID',
            'user_password' => Yii::t('app', 'User Password')
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'user_id']);
    }

    public function getContest()
    {
        return $this->hasOne(Contest::className(), ['id' => 'contest_id']);
    }
}
