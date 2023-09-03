<?php

namespace app\modules\admin\models;

use app\models\ContestUser;
use Yii;
use yii\base\Model;
use app\models\User;

class GenerateUserForm extends Model
{
    public $prefix;
    public $team_number;
    public $contest_id;
    public $names;

    public function rules()
    {
        return [
            [['prefix', 'names'], 'string'],
            [['team_number', 'contest_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'prefix' => Yii::t('app', 'Prefix'),
            'team_number' => Yii::t('app', 'Number'),
            'names' => Yii::t('app', '')
        ];
    }

    public function generatePassword($length = 8)
    {
        $chars = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'J', 'K', 'L','M', 'N',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
            '2', '3', '4', '5', '6', '7', '8', '9'];
        $keys = array_rand($chars, $length);
        $password = '';
        for($i = 0; $i < $length; $i++) {
            $password .= $chars[$keys[$i]];
        }
        return $password;
    }

    public function save()
    {
        $pieces = explode("\n", trim($this->names));

        User::deleteAll("username LIKE '" . $this->prefix . "%'");
        ContestUser::deleteAll(['contest_id' => $this->contest_id]);

        set_time_limit(0);
        ob_end_clean();
        echo "It will take some time to generate an account, please do not refresh or close this page during this time<br>";
        for ($i = 1; $i <= $this->team_number; ++$i) {

            if(isset($pieces[$i - 1]) && !empty($pieces[$i - 1]))
                $nick = $pieces[$i - 1];
            else
                $nick = $this->prefix . $i;

            $password = $this->generatePassword();
            $user = new User();
            $user->username = $this->prefix . $i;
            $user->nickname = $nick;
            $user->email = $this->prefix . $i . '@chuyenlethanhtong.edu.vn';
            $user->role = User::ROLE_PLAYER;
            $user->is_verify_email = User::VERIFY_EMAIL_YES;
            $user->status = User::STATUS_ACTIVE;
            $user->setPassword($password);
            $user->generateAuthKey();
            $user->save(false);

            Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                'user_id' => $user->id,
                'contest_id' => $this->contest_id,
                'user_password' => $password
            ])->execute();
            echo "Account {$nick} was created successfully - the number of accounts {$i}/{$this->team_number}<br>";
            flush();
        }
        echo "Account generated";
        exit('<script>location.replace(location.href);</script>');
    }

    public function generateUsers()
    {
        $pieces = explode("\n", trim($this->names));
        $count = count($pieces);

        set_time_limit(0);
        ob_end_clean();
        echo "It will take some time to generate an account, please do not refresh or close this page during this time<br>";
        for ($i = 1; $i <= count($pieces); ++$i) {
            if (empty($pieces[$i - 1]))
                continue;
            $u = explode(' ', trim($pieces[$i - 1]));
            $username = $u[0];
            $password = $u[1];
            $user = new User();
            $user->username = $username;
            $user->nickname = $username;
            $user->email = $username . '@chuyenlethanhtong.edu.vn';
            $user->role = User::ROLE_USER;
            $user->setPassword($password);
            $user->generateAuthKey();
            if ($user->save()) {
                Yii::$app->db->createCommand()->insert('{{%user_profile}}', [
                    'user_id' => $user->id
                ])->execute();
                echo "Account {$i}/{$count}: Account {$username} was created successfully";
            } else { 
                $err = $user->getErrors();
                echo "Account {$i}/{$count}: Account {$username} was created failed! Reason for failure: ";
                print_r($err);
            }
            echo '<br>';
            flush();
        }
        echo "Account generated";
        die;
    }
}
