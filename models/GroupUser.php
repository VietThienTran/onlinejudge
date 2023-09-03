<?php

namespace app\models;

use Yii;

class GroupUser extends \yii\db\ActiveRecord
{
    const ROLE_REUSE_INVITATION = 0;
    const ROLE_REUSE_APPLICATION = 1;
    const ROLE_INVITING = 2;
    const ROLE_APPLICATION = 3;
    const ROLE_MEMBER = 4;
    const ROLE_MANAGER = 5;
    const ROLE_LEADER = 6;

    public $username;

    public static function tableName()
    {
        return '{{%group_user}}';
    }

    public function rules()
    {
        return [
            [['group_id', 'user_id', 'created_at'], 'required'],
            [['group_id', 'user_id', 'role'], 'integer'],
            ['username', 'string'],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group_id' => Yii::t('app', 'Group ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'role' => Yii::t('app', 'Role'),
            'created_at' => Yii::t('app', 'Created At'),
            'username' => Yii::t('app', 'Username')
        ];
    }

    public static function find()
    {
        return new GroupUserQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getRole($color = false)
    {
        $roles = [
            Yii::t('app', 'Refuse to join'),
            Yii::t('app', 'Refuse to apply'),
            Yii::t('app', 'Inviting'),
            Yii::t('app', 'Apply to join'),
            Yii::t('app', 'Member'),
            Yii::t('app', 'Manager'),
            Yii::t('app', 'Leader')
        ];
        if (!$color) {
            return $roles[$this->role];
        }
        $rolesColor = [
            'text-danger',
            'text-warning',
            'text-info',
            'text-info',
            'text-info',
            'text-primary',
            'text-success'
        ];
        return '<span class="' . $rolesColor[$this->role] . '">' . $roles[$this->role] . '</span>' ;
    }
}
