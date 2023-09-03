<?php

namespace app\models;

use Yii;

class Group extends ActiveRecord
{
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;

    const JOIN_POLICY_INVITE = 0;
    const JOIN_POLICY_APPLICATION = 1;
    const JOIN_POLICY_FREE = 2;

    public static function tableName()
    {
        return '{{%group}}';
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
            [['name', 'status', 'join_policy'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Group Name'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'join_policy' => Yii::t('app', 'Join Policy'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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

    public function beforeDelete()
    {
        $contests = Contest::findAll(['group_id' => $this->id]);
        foreach ($contests as $contest) {
            $contest->delete();
        }
        GroupUser::deleteAll(['group_id' => $this->id]);
        return parent::beforeDelete();
    }

    public static function find()
    {
        return new GroupQuery(get_called_class());
    }

    public function getJoinPolicy()
    {
        $policy = [
            Yii::t('app', 'Invite Only'),
            Yii::t('app', 'Application & Approve'),
            Yii::t('app', 'Free')
        ];
        return $policy[$this->join_policy];
    }

    public function getStatus()
    {
        $status = [
            Yii::t('app', 'Hidden'),
            Yii::t('app', 'Visible')
        ];
        return $status[$this->status];
    }

    public function getRole()
    {
        $key = 'role' . $this->id . '_' . Yii::$app->user->id;
        $cache = Yii::$app->cache;
        $data = $cache->get($key);
        if ($data === false) {
            $data = Yii::$app->db->createCommand('SELECT role FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid',[
                ':uid' => Yii::$app->user->id,
                ':gid' => $this->id
            ])->queryScalar();
            $cache->set($key, $data, 60);
        }
        return $data;
    }

    public function getGroupUser()
    {
        return $this->hasMany(GroupUser::className(), ['group_id' => 'id']);
    }

    public function hasPermission()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $role = $this->getRole();
        return $role == GroupUser::ROLE_LEADER || $role == GroupUser::ROLE_MANAGER || Yii::$app->user->identity->isAdmin();
    }

    public function isMember()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $role = $this->getRole();
        return $role == GroupUser::ROLE_LEADER || $role == GroupUser::ROLE_MANAGER ||
            $role == GroupUser::ROLE_MEMBER || Yii::$app->user->identity->isAdmin();
    }

    public function getLeader()
    {
        $id = Yii::$app->db->createCommand('SELECT user_id FROM {{%group_user}} WHERE group_id=:gid AND role=:role', [
            ':gid' => $this->id,
            ':role' => GroupUser::ROLE_LEADER
        ])->queryScalar();
        return User::findOne($id);
    }
}
