<?php

namespace app\models;

use Yii;

class ContestPrint extends ActiveRecord
{

    const STATUS_UNREAD = 0;
    const STATUS_HAVE_READ = 1;

    public static function tableName()
    {
        return '{{%contest_print}}';
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
            [['source', 'created_at'], 'string'],
            [['source'], 'required'],
            [['id', 'user_id', 'contest_id', 'status', 'contest_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'source' => 'Source',
            'created_at' => Yii::t('app', 'Created At'),
            'status' => Yii::t('app', 'Status'),
            'contest_id' => 'Contest Id'
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->user_id = Yii::$app->user->id;
                try {
                    $client = stream_socket_client('tcp://0.0.0.0:2121', $errno, $errmsg, 1);
                    $uids = Yii::$app->db->createCommand('SELECT id FROM user WHERE role=' . User::ROLE_ADMIN)->queryColumn();
                    $content = 'Contest: ' . $this->contest->title .  ' - A new print request has been submitted.';
                    foreach ($uids as $uid) {
                        fwrite($client, json_encode([
                            'uid' => $uid,
                            'content' => $content
                        ]) . "\n");
                    }
                } catch (\Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getContest()
    {
        return $this->hasOne(Contest::className(), ['id' => 'contest_id']);
    }
}
