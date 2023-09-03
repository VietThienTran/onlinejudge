<?php

namespace app\models;

use Yii;

class Solution extends ActiveRecord
{
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;
    const STATUS_TEST = 2;

    const OJ_WAITING_STATUS = 3;

    const OJ_WT0 = 0;
    const OJ_WT1 = 1;
    const OJ_CI  = 2;
    const OJ_RI  = 3;
    const OJ_AC  = 4;
    const OJ_PE  = 5;
    const OJ_WA  = 6;
    const OJ_TL  = 7;
    const OJ_ML  = 8;
    const OJ_OL  = 9;
    const OJ_RE  = 10;
    const OJ_CE  = 11;
    const OJ_SE  = 12;
    const OJ_NT  = 13;

    const CLANG = 0;
    const CPPLANG = 1;
    const JAVALANG = 2;
    const PYLANG = 3;
    const PASCALLANG = 4;

    public static function tableName()
    {
        return '{{%solution}}';
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
            [['problem_id', 'created_by', 'time', 'memory', 'result', 'language', 'contest_id', 'status',
              'code_length', 'score'], 'integer'],
            [['created_at', 'judgetime'], 'safe'],
            [['language', 'source'], 'required'],
            [['language'], 'in', 'range' => [
                Solution::CLANG,
                Solution::CPPLANG,
                Solution::JAVALANG,
                Solution::PYLANG,
                Solution::PASCALLANG,
            ], 'message' => 'Please select a language'],
            [['source', 'pass_info'], 'string'],
            [['judge'], 'string', 'max' => 16],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Run ID'),
            'problem_id' => Yii::t('app', 'Problem ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'time' => Yii::t('app', 'Time'),
            'memory' => Yii::t('app', 'Memory'),
            'created_at' => Yii::t('app', 'Submit Time'),
            'source' => Yii::t('app', 'Code'),
            'result' => Yii::t('app', 'Result'),
            'language' => Yii::t('app', 'Language'),
            'contest_id' => Yii::t('app', 'Contest ID'),
            'status' => Yii::t('app', 'Status'),
            'code_length' => Yii::t('app', 'Code Length'),
            'judgetime' => Yii::t('app', 'Judgetime'),
            'pass_info' => Yii::t('app', 'Pass Info'),
            'judge' => Yii::t('app', 'Judge'),
            'score' => Yii::t('app', 'Score'),
            'user' => Yii::t('app', 'User')
        ];
    }

    public static function find()
    {
        return new SolutionQuery(get_called_class());
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
                $this->code_length = strlen($this->source);
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->language != Yii::$app->user->identity->language) {
            User::setLanguage($this->language);
        }
    }

    public function getTestCount()
    {
        return intval(substr(strstr($this->pass_info,'/'), 1));
    }

    public function getPassedTestCount()
    {
        return intval(strstr($this->pass_info,'/', true));
    }

    public function getLang()
    {
        switch ($this->language) {
            case Solution::CLANG:
                $res = 'C';
                break;
            case Solution::CPPLANG:
                $res = 'C++';
                break;
            case Solution::JAVALANG:
                $res = 'Java';
                break;
            case Solution::PYLANG:
                $res = 'Python3';
                break;
            case Solution::PASCALLANG:
                $res = 'Pascal';
                break;
            default:
                $res = 'not set';
                break;
        }
        return $res;
    }

    public static function getLangFileExtension($lang)
    {
        switch ($lang) {
            case Solution::CLANG:
                $res = 'c';
                break;
            case Solution::CPPLANG:
                $res = 'cpp';
                break;
            case Solution::JAVALANG:
                $res = 'java';
                break;
            case Solution::PYLANG:
                $res = 'py';
                break;
            case Solution::PYLANG:
                $res = 'pas';
                break;
            default:
                $res = 'txt';
                break;
        }
        return $res;
    }

    public function getResult()
    {
        $res = self::getResultList($this->result);
        $loadingImgUrl = Yii::getAlias('@web/images/loading.gif');

        if ($this->result <= Solution::OJ_WAITING_STATUS) {
            $waitingHtmlDom = 'waiting="true"';
            $loadingImg = "<img src=\"{$loadingImgUrl}\">";
        } else {
            $waitingHtmlDom = 'waiting="false"';
            $loadingImg = "";
        }
        $innerHtml =  'data-verdict="' . $this->result . '" data-submissionid="' . $this->id . '" ' . $waitingHtmlDom;

        $cssClass = [
            "text-muted", // Pending
            "text-muted",
            "text-muted",
            "text-muted",
            "text-success", // AC
            "text-warning", // PE
            "text-danger",  // WA
            "text-warning", // TLE
            "text-warning", // MLE
            "text-warning", // OLE
            "text-warning", // RE
            "text-warning", // CE
            "text-danger",  // SE
            "text-danger", // No Test Data
        ];
        return "<strong class=" . $cssClass[$this->result] . " $innerHtml>{$res}{$loadingImg}</strong>";
    }

    public static function getResultList($res = '')
    {
        $results = [
            '' => 'All',
            Solution::OJ_WT0 => Yii::t('app', 'Pending'),
            Solution::OJ_WT1 => Yii::t('app', 'Pending Rejudge'),
            Solution::OJ_CI => Yii::t('app', 'Compiling'),
            Solution::OJ_RI => Yii::t('app', 'Running & Judging'),
            Solution::OJ_AC => Yii::t('app', 'Accepted'),
            Solution::OJ_PE => Yii::t('app', 'Presentation Error'),
            Solution::OJ_WA => Yii::t('app', 'Wrong Answer'),
            Solution::OJ_TL => Yii::t('app', 'Time Limit Exceeded'),
            Solution::OJ_ML => Yii::t('app', 'Memory Limit Exceeded'),
            Solution::OJ_OL => Yii::t('app', 'Output Limit Exceeded'),
            Solution::OJ_RE => Yii::t('app', 'Runtime Error'),
            Solution::OJ_CE => Yii::t('app', 'Compile Error'),
            Solution::OJ_SE => Yii::t('app', 'System Error'),
            Solution::OJ_NT => Yii::t('app', 'No Test Data')
        ];
        return $res === '' ? $results : $results[$res];
    }

    public static function getLanguageList($status = '')
    {
        $arr = [
            '' => 'All',
            '0' => 'C',
            '1' => 'C++',
            '2' => 'Java',
            '3' => 'Python3',
            '4' => 'Pascal'
        ];
        return $status === '' ? $arr : $arr[$status];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['id' => 'problem_id']);
    }

    public function getUsername()
    {
        return $this->user->username;
    }

    public function getSolutionInfo()
    {
        return $this->hasOne(SolutionInfo::className(), ['solution_id' => 'id']);
    }

    public function getContestProblem()
    {
        return $this->hasOne(ContestProblem::className(), ['problem_id' => 'problem_id']);
    }

    public function getProblemInContest()
    {
        return $this->contestProblem;
    }

    public function canViewResult()
    {
        if ($this->status == Solution::STATUS_VISIBLE && Yii::$app->setting->get('isShareCode')) {
            return true;
        }
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            return true;
        }
        if (empty($this->contest_id)) {
            return true;
        }

        $contest = self::getContestInfo($this->contest_id);

        if ($contest['type'] == Contest::TYPE_HOMEWORK) {
            return true;
        }

        if ($contest['group_id'] && !Yii::$app->user->isGuest) {
            $role = Yii::$app->db->createCommand('SELECT role FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid', [
                ':uid' => Yii::$app->user->id,
                ':gid' => $contest['group_id']
            ])->queryScalar();

            if ($role == GroupUser::ROLE_LEADER || $role == GroupUser::ROLE_MANAGER) {
                return true;
            }
            if ($role != GroupUser::ROLE_MEMBER) {
                return false;
            }
        }

        if ($contest['type'] != Contest::TYPE_OI || time() >= strtotime($contest['end_time'])) {
            return true;
        }
        return false;
    }

    public function canViewSource()
    {
        if ($this->created_by == Yii::$app->user->id) {
            return true;
        }
        if ($this->status == Solution::STATUS_VISIBLE && Yii::$app->setting->get('isShareCode')) {
            return true;
        }
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            return true;
        }
        if (!empty($this->contest_id)) {
            $contest = self::getContestInfo($this->contest_id);
            if (time() >= strtotime($contest['end_time'])) {
                return true;
            }
            if ($contest['group_id'] && !Yii::$app->user->isGuest) {
                $role = Yii::$app->db->createCommand('SELECT role FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid', [
                    ':uid' => Yii::$app->user->id,
                    ':gid' => $contest['group_id']
                ])->queryScalar();
                if ($role == GroupUser::ROLE_LEADER || $role == GroupUser::ROLE_MANAGER) {
                    return true;
                }
                if ($role != GroupUser::ROLE_MEMBER) {
                    return false;
                }
            }
        }
        return false;
    }

    public static function getContestInfo($contestID)
    {
        $key = 'status_' . $contestID;
        $cache = Yii::$app->cache;
        $contest = $cache->get($key);
        if ($contest === false) {
            $contest = Yii::$app->db->createCommand('SELECT `id`, `start_time`, `end_time`, `type`, `group_id` FROM  {{%contest}} WHERE id = :id', [
                ':id' => $contestID
            ])->queryOne();
            $cache->set($key, $contest, 60);
        }
        return $contest;
    }

    public function canViewErrorInfo()
    {
        if ($this->status == Solution::STATUS_VISIBLE && Yii::$app->setting->get('isShareCode')) {
            return true;
        }

        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            return true;
        }
        if (!empty($this->contest_id)) {
            $contest = self::getContestInfo($this->contest_id);
           
            if (time() >= strtotime($contest['end_time'])) {
                return true;
            }

            if ($contest['type'] == Contest::TYPE_HOMEWORK) {
                return true;
            }

            if ($this->created_by == Yii::$app->user->id && $this->result == self::OJ_CE) {
                return true;
            }
        }

        if ($this->status == Solution::STATUS_VISIBLE && $this->created_by == Yii::$app->user->id) {
            return true;
        }
        return false;
    }
}
