<?php

namespace app\models;

use Yii;

class UserProfile extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'gender', 'qq_number', 'student_number'], 'integer'],
            [['birthdate'], 'safe'],
            [['address', 'description', 'major'], 'string'],
            [['signature', 'school'], 'string', 'max' => 128],
            [['major'], 'string', 'max' => 64],
            [['user_id'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'gender' => Yii::t('app', 'Gender'),
            'qq_number' => Yii::t('app', 'Zalo account'),
            'birthdate' => Yii::t('app', 'Birthdate'),
            'signature' => Yii::t('app', 'Signature'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
            'school' => Yii::t('app', 'School'),
            'student_number' => Yii::t('app', 'Student Number'),
            'major' => Yii::t('app', 'Major')
        ];
    }
}
