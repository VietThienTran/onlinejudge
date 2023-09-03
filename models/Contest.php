<?php

namespace app\models;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\caching\TagDependency;

class Contest extends \yii\db\ActiveRecord
{
    const BASIC_SCORE = 500;
    const RATING_INIT_SCORE = 1149;
    const STATUS_NOT_START = 0;
    const STATUS_RUNNING = 1;
    const STATUS_ENDED = 2;
    const TYPE_EDUCATIONAL = 0;
    const TYPE_RANK_SINGLE = 1;
    const TYPE_RANK_GROUP  = 2;
    const TYPE_HOMEWORK    = 3;
    const TYPE_OI          = 4;
    const TYPE_IOI         = 5;
    const STATUS_HIDDEN = 0; 
    const STATUS_VISIBLE = 1; 
    const STATUS_PRIVATE = 2; 
    const SCENARIO_ONLINE = 0;
    const SCENARIO_OFFLINE = 1;

    public static function tableName()
    {
        return '{{%contest}}';
    }

    public function rules()
    {
        return [
            [['title', 'start_time', 'end_time'], 'required'],
            [['start_time', 'end_time', 'lock_board_time'], 'safe'],
            [['description', 'editorial'], 'string'],
            [['id', 'status', 'type', 'scenario', 'created_by', 'group_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Contest ID'),
            'title' => Yii::t('app', 'Title'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'lock_board_time' => Yii::t('app', 'Lock Board Time'),
            'editorial' => Yii::t('app', 'Editorial'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
            'scenario' => Yii::t('app', 'Scenario')
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
        ContestUser::deleteAll(['contest_id' => $this->id]);
        ContestProblem::deleteAll(['contest_id' => $this->id]);
        Solution::deleteAll(['contest_id' => $this->id]);
        Discuss::deleteAll(['entity' => Discuss::ENTITY_CONTEST, 'entity_id' => $this->id]);
        ContestPrint::deleteAll(['contest_id' => $this->id]);
        ContestAnnouncement::deleteAll(['contest_id' => $this->id]);
        return parent::beforeDelete();
    }

    public function getSolutions()
    {
        return $this->hasMany(Solution::className(), ['problem_id' => 'problem_id'])
            ->viaTable(ContestProblem::tableName(), ['contest_id' => 'id']);
    }

    public function getType()
    {
        switch ($this->type) {
            case Contest::TYPE_EDUCATIONAL:
                $res = Yii::t('app', 'Educational');
                break;
            case Contest::TYPE_RANK_SINGLE:
                $res = Yii::t('app', 'Single Ranked');
                break;
            case Contest::TYPE_RANK_GROUP:
                $res = Yii::t('app', 'ICPC');
                break;
            case Contest::TYPE_HOMEWORK:
                $res = Yii::t('app', 'Homework');
                break;
            case Contest::TYPE_OI:
                $res = Yii::t('app', 'OI');
                break;
            case Contest::TYPE_IOI:
                $res = Yii::t('app', 'IOI');
                break;
            default:
                $res = "null";
        }
        return $res;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    public function getRunStatus($description = false)
    {
        $start_time = strtotime($this->start_time);
        $end_time = strtotime($this->end_time);
        $current_time = time();
        if ($description) {
            if ($start_time > $current_time) {
                return Yii::t('app', 'Not started yet');
            } else if ($start_time <= $current_time && $current_time <= $end_time) {
                return Yii::t('app', 'Running');
            } else {
                return Yii::t('app', 'Ended');
            }
        } else {
            if ($start_time > $current_time) {
                return Contest::STATUS_NOT_START;
            } else if ($start_time <= $current_time && $current_time <= $end_time) {
                return Contest::STATUS_RUNNING;
            } else {
                return Contest::STATUS_ENDED;
            }
        }
    }

    public function isContestEnd()
    {
        return time() > strtotime($this->end_time);
    }

    public static function getContestList()
    {
        $res = (new Query())->select('id, title')
            ->from('{{%contest}}')
            ->orderBy('id DESC')
            ->all();
        $list = ['' => 'None'];
        foreach ($res as $key => $value) {
            $list[$value['id']] = $value['id'] . ' [' . $value['title'] . ']';
        }
        return $list;
    }

    public function getAnnouncements()
    {
        return $this->hasMany(ContestAnnouncement::className(), ['contest_id' => 'id']);
    }

    public function getProblems()
    {
        $dependency = new \yii\caching\DbDependency([
            'sql'=>'SELECT COUNT(*) FROM {{%contest_problem}} WHERE contest_id=:cid',
            'params' => [':cid' => $this->id]
        ]);
        return Yii::$app->db->cache(function ($db) {
            return $db->createCommand('
                SELECT `p`.`title`, `p`.`id` AS `problem_id`, `c`.`num`
                FROM `problem` `p`
                LEFT JOIN `contest_problem` `c` ON `c`.`contest_id`=:cid
                WHERE p.id=c.problem_id
                ORDER BY `c`.`num`
            ', [':cid' => $this->id])->queryAll();
        }, 60, $dependency);
    }

    public function getUsersSolution()
    {
        return Yii::$app->db->createCommand('
            SELECT u.id as user_id, username, nickname, result, s.problem_id, s.created_at, s.id, s.score
            FROM `solution` `s`
            LEFT JOIN `user` `u` ON u.id=s.created_by
            WHERE `contest_id`=:id ORDER BY `s`.`id`
        ', [':id' => $this->id])->queryAll();
    }

    public function saveContestSolutionToFile()
    {
        $solutions = Yii::$app->db->createCommand('
            SELECT u.id as user_id, username, result, s.problem_id, s.created_at, s.id, s.score, s.language, s.source
            FROM `solution` `s`
            LEFT JOIN `user` `u` ON u.id=s.created_by
            WHERE `contest_id`=:id AND `result`=:result AND s.created_at <= :end
        ', [':id' => $this->id, ':result' => Solution::OJ_AC, ':end' => $this->end_time])->queryAll();

        $problems = $this->getProblems();
        foreach ($problems as $p) {
            $problems[$p['problem_id']] = $p;
        }
        $workDir = Yii::$app->getRuntimePath() . '/contest/' . $this->id . '/';
        foreach ($solutions as $solution) {
            $problemIndex = chr(65 + $problems[$solution['problem_id']]['num']);
            $path = $workDir . $problemIndex . '/';
            if (!is_dir($path)) {
                FileHelper::createDirectory($path);
            }
            $fileName = $problemIndex
                . '_RunID[' . $solution['id'] . ']'
                . '_Username[' . $solution['username'] . ']'
                . '.' . Solution::getLangFileExtension($solution['language']);
            
            $fp = fopen($path . $fileName, 'w');
            fputs($fp, $solution['source']);
            fclose($fp);
        }

        $zipName = Yii::$app->getRuntimePath() . '/oj_contest_' . $this->id . '.zip';
        exec("zip -jqr $zipName $workDir");
        FileHelper::removeDirectory($workDir);
        if (!file_exists($zipName)) {
            return false;
        }
        return $zipName;
    }

    public function getProblemCount()
    {
        return Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%contest_problem}} WHERE contest_id=' . $this->id)->queryScalar();
    }

    public function getContestUser()
    {
        $dependency = new \yii\caching\DbDependency([
            'sql'=>'SELECT COUNT(*) FROM {{%contest_user}} WHERE contest_id=:cid',
            'params' => [':cid' => $this->id]
        ]);
        return Yii::$app->db->cache(function ($db) {
            return $db->createCommand('
                SELECT `u`.`username`, `u`.`nickname`, `p`.`student_number`, `u`.`id` as `user_id`, `u`.`role`, `u`.`rating`
                FROM `user` `u`
                LEFT JOIN `contest_user` `c` ON `c`.`contest_id`=:cid
                LEFT JOIN `user_profile` `p` ON `p`.`user_id`=`c`.`user_id`
                WHERE u.id=c.user_id ORDER BY `c`.`id`
            ', [':cid' => $this->id])->queryAll();
        }, 3600, $dependency);
    }

    public function getContestUserCount()
    {
        return Yii::$app->db->createCommand('
            SELECT COUNT(*) FROM {{%contest_user}} WHERE contest_id=:cid
        ', [':cid' => $this->id])->queryScalar();
    }

    public function getSubmissionStatistics()
    {
        $userSolutions = $this->getUsersSolution();
        $problems = $this->getProblems();
        $isScoreboardFrozen = $this->isScoreboardFrozen();
        $contestEndTime = strtotime($this->end_time);
        if ($isScoreboardFrozen) {
            $lockBoardTime = strtotime($this->lock_board_time);
        }
        $res = [];
        foreach ($problems as $problem) {
            $res[$problem['problem_id']]['solved'] = 0;
            $res[$problem['problem_id']]['submit'] = 0;
        }
        foreach ($userSolutions as $solution) {
            $createdAt = strtotime($solution['created_at']);
            $pid = $solution['problem_id'];
            if (!isset($res[$pid]['solved'])) {
                $res[$pid]['solved'] = 0;
            }
            if (!isset($res[$pid]['submit'])) {
                $res[$pid]['submit'] = 0;
            }
            $res[$pid]['submit']++;
            if ($isScoreboardFrozen && $createdAt > $lockBoardTime &&
                $createdAt < $contestEndTime) {
                continue;
            }
            if ($solution['result'] == Solution::OJ_AC) {
                $res[$pid]['solved']++;
            }
        }
        return $res;
    }

    public function getRankData($lock = true, $endtime = null)
    {
        if ($this->type == Contest::TYPE_OI || $this->type == Contest::TYPE_IOI) {
            return $this->getOIRankData($lock, $endtime);
        }
        return $this->getICPCRankData($lock, $endtime);
    }

    public function getICPCRankData($lock = true, $endtime = null)
    {
        $users_solution_data = $this->getUsersSolution();
        $users = $this->getContestUser();
        $problems = $this->getProblems();
        $result = [];
        $first_blood = [];
        $submit_count = [];
        $problem_ids = [];
        $count = count($users_solution_data);
        $start_time = strtotime($this->start_time);
        $lock_time = 0x7fffffff;
        $contest_end_time = strtotime($this->end_time);
        if ($endtime == null) {
            $endtime = $contest_end_time;
        }

        foreach ($problems as $problem) {
            $problem_ids[$problem['problem_id']] = 1;
        }
        foreach ($users as $user) {
            $result[$user['user_id']]['username'] = $user['username'];
            $result[$user['user_id']]['role'] = $user['role'];
            $result[$user['user_id']]['rating'] = $user['rating'];
            $result[$user['user_id']]['time'] = 0;
            $result[$user['user_id']]['solved'] = 0;
            $result[$user['user_id']]['submit'] = 0;
            $result[$user['user_id']]['nickname'] = $user['nickname'];
            $result[$user['user_id']]['student_number'] = $user['student_number'];
            $result[$user['user_id']]['user_id'] = $user['user_id'];
        }

        if (!empty($this->lock_board_time)) {
            $lock_time = strtotime($this->lock_board_time);
        }

        for ($i = 0; $i < $count; $i++) {
            $row = $users_solution_data[$i];
            $user = $row['user_id'];
            $pid = $row['problem_id'];
            $created_at = strtotime($row['created_at']);
            if ($created_at > $endtime) {
                break;
            }

            if (!isset($problem_ids[$pid])) {
                continue;
            }

            if (!isset($submit_count[$pid]['solved']))
                $submit_count[$pid]['solved'] = 0;
            if (!isset($submit_count[$pid]['submit']))
                $submit_count[$pid]['submit'] = 0;

            if (!isset($result[$user]['ac_time'][$pid]))
                $result[$user]['ac_time'][$pid] = -1;

            if (!isset($result[$user]['wa_count'][$pid]))
                $result[$user]['wa_count'][$pid] = 0;

            if (!isset($result[$user]['ce_count'][$pid]))
                $result[$user]['ce_count'][$pid] = 0;

            if (!isset($result[$user]['pending'][$pid]))
                $result[$user]['pending'][$pid] = 0;

            if (!isset($first_blood[$pid]))
                $first_blood[$pid] = '';

            if ($result[$user]['ac_time'][$pid] >= 0) {
                continue;
            }

            $submit_count[$pid]['submit']++;

            if ($lock && $lock_time <= $created_at &&
                time() <= $contest_end_time + Yii::$app->setting->get('scoreboardFrozenTime')) {
                ++$result[$user]['pending'][$pid];
                continue;
            }

            if ($row['result'] == Solution::OJ_AC) {

                $submit_count[$pid]['solved']++;
                $result[$user]['pending'][$pid] = 0;

                if (empty($first_blood[$pid])) {
                    if ($this->type == self::TYPE_RANK_SINGLE) {
                        $result[$user]['time'] += 0.1 * self::BASIC_SCORE;
                    }
                    $first_blood[$pid] = $user;
                }
                $sec = $created_at - $start_time;
                ++$result[$user]['solved'];
                if ($this->type == self::TYPE_RANK_SINGLE) {
                    $score = 0.5 * self::BASIC_SCORE + max(0, self::BASIC_SCORE - 2 * $sec / 60 - $result[$user]['wa_count'][$pid] * 50);
                    $result[$user]['ac_time'][$pid] = $score;
                    $result[$user]['time'] += $score;
                } else {
                    if ($created_at < $contest_end_time) {
                        $result[$user]['ac_time'][$pid] = $sec / 60;
                    } else {
                        $result[$user]['ac_time'][$pid] = 0;
                    }
                    $result[$user]['time'] += $sec + $result[$user]['wa_count'][$pid] * 60 * 20;
                }
            } else if ($row['result'] <= 3) {
                ++$result[$user]['pending'][$pid];
            } else if ($row['result'] == Solution::OJ_CE) {
                ++$result[$user]['ce_count'][$pid];
            } else {
                ++$result[$user]['wa_count'][$pid];
            }
        }

        usort($result, function($a, $b) {
            if ($a['solved'] != $b['solved']) { 
                return $a['solved'] < $b['solved'];
            } else if ($a['time'] != $b['time']) { 
                if ($this->type == self::TYPE_RANK_SINGLE) {
                    return $a['time'] < $b['time'];
                } else {
                    return $a['time'] > $b['time'];
                }
            } else {
                return $a['submit'] < $b['submit'];
            }
        });

        return [
            'rank_result' => $result,
            'submit_count' => $submit_count,
            'first_blood' => $first_blood
        ];
    }

    public function getOIRankData($lock = true, $endtime = null)
    {
        $users_solution_data = $this->getUsersSolution();
        $users = $this->getContestUser();
        $problems = $this->getProblems();
        $result = [];
        $first_blood = [];
        $submit_count = [];
        $count = count($users_solution_data);
        $start_time = strtotime($this->start_time);
        $lock_time = 0x7fffffff;
        $contest_end_time = strtotime($this->end_time);
        if ($endtime == null) {
            $endtime = $contest_end_time;
        }

        foreach ($users as $user) {
            $result[$user['user_id']]['username'] = $user['username'];
            $result[$user['user_id']]['user_id'] = $user['user_id'];
            $result[$user['user_id']]['nickname'] = $user['nickname'];
            $result[$user['user_id']]['role'] = $user['role'];
            $result[$user['user_id']]['rating'] = $user['rating'];
            $result[$user['user_id']]['solved'] = 0;
            $result[$user['user_id']]['total_score'] = 0; 
            $result[$user['user_id']]['score'] = []; 
            $result[$user['user_id']]['max_score'] = []; 
            $result[$user['user_id']]['correction_score'] = 0; 
            $result[$user['user_id']]['student_number'] = $user['student_number'];
            $result[$user['user_id']]['total_time'] = 0; 
        }

        foreach ($problems as $problem) {
            $problem_ids[$problem['problem_id']] = 1;
        }

        if (!empty($this->lock_board_time)) {
            $lock_time = strtotime($this->lock_board_time);
        }

        for ($i = 0; $i < $count; $i++) {
            $row = $users_solution_data[$i];
            $user = $row['user_id'];
            $pid = $row['problem_id'];
            $created_at = strtotime($row['created_at']);
            $score = $row['score'];
            if ($created_at > $endtime) {
                break;
            }
            if (!isset($problem_ids[$pid])) {
                continue;
            }

            if (!isset($submit_count[$pid]['solved']))
                $submit_count[$pid]['solved'] = 0;
            if (!isset($submit_count[$pid]['submit']))
                $submit_count[$pid]['submit'] = 0;
            if (!isset($result[$user]['score'][$pid]))
                $result[$user]['score'][$pid] = 0;
            if (!isset($result[$user]['max_score'][$pid]))
                $result[$user]['max_score'][$pid] = 0;

            if ($created_at <= $contest_end_time) {
                $result[$user]['score'][$pid] = $score;
            }
            if (isset($result[$user]['solved_flag'][$pid])) {
                continue;
            }
            if (!isset($result[$user]['submit_time'][$pid]) && $created_at < $contest_end_time) {
                $result[$user]['submit_time'][$pid] = ($created_at - $start_time) / 60;
            }
            if ($result[$user]['max_score'][$pid] < $score) {
                $result[$user]['max_score'][$pid] = $score;
                if ($created_at < $contest_end_time) {
                    $result[$user]['submit_time'][$pid] = ($created_at - $start_time) / 60;
                }
            }

            if (!isset($result[$user]['pending'][$pid]))
                $result[$user]['pending'][$pid] = 0;
          
            if (!isset($first_blood[$pid]))
                $first_blood[$pid] = '';

            if ($lock && $lock_time <= $created_at &&
                time() <= $contest_end_time + Yii::$app->setting->get('scoreboardFrozenTime')) {
                ++$result[$user]['pending'][$pid];
                continue;
            }
            $submit_count[$pid]['submit']++;
            if ($row['result'] == Solution::OJ_AC) {
            
                $submit_count[$pid]['solved']++;
                $result[$user]['pending'][$pid] = 0;
                $result[$user]['solved_flag'][$pid] = 1; 
                $result[$user]['solved']++; 
                if ($created_at < $contest_end_time) {
                    $result[$user]['total_time'] += ($created_at - $start_time) / 60;
                }
                if (empty($first_blood[$pid])) {
                    $first_blood[$pid] = $user;
                }
            } else if ($row['result'] <= 3) {
                ++$result[$user]['pending'][$pid];
            }
        }

        foreach ($result as &$v) {
            foreach ($v['score'] as $s) {
                $v['total_score'] += $s;
            }
            foreach ($v['max_score'] as $s) {
                $v['correction_score'] += $s;
            }
        }

        $type = $this->type;
        usort($result, function($a, $b) use ($type) {
            if ($type == self::TYPE_OI) {
                if ($a['total_score'] != $b['total_score']) { 
                    return $a['total_score'] < $b['total_score'];
                } else if ($a['correction_score'] != $b['correction_score']) { 
                    return $a['correction_score'] < $b['correction_score'];
                } else {
                    return $a['total_time'] > $b['total_time'];
                }
            } else { 
                if ($a['solved'] != $b['solved']) { 
                    return $a['solved'] < $b['solved'];
                } else if ($a['correction_score'] != $b['correction_score']) {
                    return $a['correction_score'] < $b['correction_score'];
                } else {
                    return $a['total_time'] > $b['total_time'];
                }
            }
        });

        return [
            'rank_result' => $result,
            'submit_count' => $submit_count,
            'first_blood' => $first_blood
        ];
    }

    public function isUserInContest()
    {
        return Yii::$app->db->createCommand('SELECT count(*) FROM {{%contest_user}} WHERE user_id=:uid AND contest_id=:cid', [
            ':uid' => Yii::$app->user->id,
            ':cid' => $this->id
        ])->queryScalar();
    }

    public function getProblemById($id)
    {
        $contestID = $this->id;
        $dependency = new TagDependency(['tags' => ['id' => $id, 'contestID' => $contestID]]);
        return Yii::$app->db->cache(function ($db) use ($id, $contestID) {
            return $db->createCommand(
                "SELECT `cp`.`num`, `p`.`title`, `p`.`id`, `p`.`description`, 
                `p`.`input`, `p`.`output`, `p`.`sample_input`, `p`.`sample_output`, `p`.`hint`, `p`.`time_limit`, 
                `p`.`memory_limit` 
                FROM `problem` `p` 
                LEFT JOIN `contest_problem` `cp` ON cp.problem_id=p.id 
                WHERE (`cp`.`num`={$id}) AND (`cp`.`contest_id`={$contestID})"
            )->queryOne();
        }, 60, $dependency);
    }

    public function getClarifies()
    {
        return $this->hasMany(Discuss::className(), ['contest_id' => 'id']);
    }

    public function calRating()
    {
        $users = Yii::$app->db->createCommand('
            SELECT `u`.`id` as `user_id`, `rating`, `rating_change`
            FROM `user` `u`
            LEFT JOIN `contest_user` `c` ON `c`.`contest_id`=:cid
            WHERE u.id=c.user_id ORDER BY `c`.`id`
        ', [':cid' => $this->id])->queryAll();

        if ($this->type == self::TYPE_OI) {
            $rankResult = $this->getOIRankData(false)['rank_result'];
        } else {
            $rankResult = $this->getRankData(false)['rank_result'];
        }
        $tmp = [];
        foreach ($rankResult as $k => $user) {
            $tmp[$user['user_id']] = ['solved' => $user['solved'], 'rank' => $k];
        }
        $rankResult = $tmp;

        $userCount = 0;
        foreach ($users as $user) {
            if ($rankResult[$user['user_id']]['solved'] != 0) {
                if ($user['rating_change'] != NULL) {
                    return;
                }
                $userCount++;
            }
        }
        foreach ($users as $user) {
            $old = $user['rating'] == NULL ? self::RATING_INIT_SCORE : $user['rating'];
            $exp = 0;

            if ($rankResult[$user['user_id']]['solved'] == 0) {
                continue;
            }
            if ($user['rating']) {
                foreach ($users as $u) {
                    if ($user['user_id'] != $u['user_id'] && $rankResult[$u['user_id']]['solved'] > 0) {
                        $exp += 1.0 / (1.0 + pow(10, ($u['rating'] ? $u['rating'] : self::RATING_INIT_SCORE) - $old) / 400.0);
                    }
                }
            } else {
                $exp = intval($userCount / 2);
            }

            if ($old < 1150) {
                $eloK = 5;
            } else if ($old < 1400) {
                $eloK = 6;
            } else if ($old < 1650) {
                $eloK = 7;
            } else if ($old < 1900) {
                $eloK = 8;
            } else if ($old < 2150) {
                $eloK = 9;
            } else {
                $eloK = 10;
            }
            $newRating = intval($old + $eloK * (($userCount - $rankResult[$user['user_id']]['rank']) - $exp));

            Yii::$app->db->createCommand()->update('{{%user}}', [
                'rating' => $newRating
            ], ['id' => $user['user_id']])->execute();
            Yii::$app->db->createCommand()->update('{{contest_user}}', [
                'rating_change' => $newRating - $old,
                'rank' => $rankResult[$user['user_id']]['rank'] + 1
            ], ['user_id' => $user['user_id'], 'contest_id' => $this->id])->execute();
        }
    }

    public function canView()
    {
        if ($this->status == Contest::STATUS_VISIBLE && $this->getRunStatus() == Contest::STATUS_ENDED) {
            return true;
        }
        $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_ADMIN;
        $isAuthor = !Yii::$app->user->isGuest && $this->created_by == Yii::$app->user->id;
        if ($isAdmin || $isAuthor) {
            return true;
        }
        if ($this->status == Contest::STATUS_HIDDEN) {
            return false;
        }
        if ($this->isUserInContest()) {
            return true;
        }
        if ($this->group_id != 0) {
            $role = Yii::$app->db->createCommand('SELECT role FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid', [
                ':uid' => Yii::$app->user->id,
                ':gid' => $this->group_id
            ])->queryScalar();
            if ($role == GroupUser::ROLE_MEMBER || $role == GroupUser::ROLE_MANAGER || $role == GroupUser::ROLE_LEADER) {
                return true;
            }
        }
        return false;
    }

    public function getLoginUserProblemSolvingStatus()
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }
        $statuses = Yii::$app->db->createCommand('
            SELECT `s`.`result`, `s`.`problem_id`
            FROM `solution` `s` LEFT JOIN `user` `u` ON u.id=s.created_by
            WHERE `contest_id`=:id AND `s`.`created_at`<=:endtime AND `s`.`created_by`=:uid
        ', [':id' => $this->id, ':endtime' => $this->end_time, ':uid' => Yii::$app->user->id])->queryAll();
        $res = [];
        foreach ($statuses as $status) {
            if (isset($res[$status['problem_id']]) && $res[$status['problem_id']] == Solution::OJ_AC) {
                continue;
            }
            $res[$status['problem_id']] = $status['result'];
        }
        return $res;
    }

    public function isScoreboardFrozen()
    {
       return !empty($this->lock_board_time) && strtotime($this->lock_board_time) <= time() &&
           time() <= strtotime($this->end_time) + Yii::$app->setting->get('scoreboardFrozenTime');
    }

    public function isContestAdmin() {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        if (Yii::$app->user->identity->isAdmin()) {
            return true;
        }
        if ($this->id == Yii::$app->user->id) {
            return true;
        }
        if (!empty($this->group_id)) {
            if ($this->group->hasPermission()) {
                return true;
            }
        }
        return false;
    }
    public function deleteProblem($pid) {
        $db = Yii::$app->db;
        $cid = $this->id;
        $db->transaction(function () use ($pid, $cid) {
            Yii::$app->db->createCommand()
                ->delete('{{%contest_problem}}', ['contest_id' => $cid, 'problem_id' => $pid])
                ->execute();
            Solution::deleteAll(['contest_id' => $cid, 'problem_id' => $pid]);

            $problems = Yii::$app->db->createCommand('
                SELECT `p`.`id` AS `problem_id`
                FROM `problem` `p`
                LEFT JOIN `contest_problem` `c` ON `c`.`contest_id`=:cid
                WHERE p.id=c.problem_id
                ORDER BY `c`.`num`
            ', [':cid' => $cid])->queryAll();

            $i = 0;
            foreach ($problems as $problem) {
                Yii::$app->db->createCommand()->update('{{%contest_problem}}', [
                    'num' => $i
                ], ['contest_id' => $cid, 'problem_id' => $problem['problem_id']])->execute();
                TagDependency::invalidate(Yii::$app->cache, ['id' => $i, 'contestID' => $cid]);
                $i++;
            }
        });
    }
}
