<?php

namespace app\controllers;

use app\components\BaseController;
use app\models\ContestPrint;
use app\models\User;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\models\ContestAnnouncement;
use app\models\ContestUser;
use app\models\Contest;
use app\models\Solution;
use app\models\SolutionSearch;
use app\models\Discuss;

class ContestController extends BaseController
{
    public $layout = 'contest';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'main';
        $dataProvider = new ActiveDataProvider([
            'query' => Contest::find()->where([
                '<>', 'status', Contest::STATUS_HIDDEN
            ])->andWhere([
                'group_id' => 0
            ])->orderBy(['id' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $searchModel = new SolutionSearch();
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('/contest/status', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->search(Yii::$app->request->queryParams, $model)
            ]);
        }
        return $this->render('/contest/status', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams, $model)
        ]);
    }

    public function actionSubmission($pid, $cid, $uid)
    {
        $this->layout = false;
        $model = $this->findModel($cid);
        if (!$model->isContestEnd() && $model->type == Contest::TYPE_OI) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        if ((!$model->isContestEnd() || $model->isScoreboardFrozen()) && (Yii::$app->user->isGuest || Yii::$app->user->id != $model->created_by)) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $submissions = Yii::$app->db->createCommand(
            'SELECT id, result, created_at FROM {{%solution}} WHERE problem_id=:pid AND contest_id=:cid AND created_by=:uid ORDER BY id DESC',
            [':pid' => $pid, ':cid' => $model->id, ':uid' => $uid]
        )->queryAll();
        return $this->render('submission', [
            'submissions' => $submissions
        ]);
    }

    public function actionUser($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);
        $provider = new ActiveDataProvider([
            'query' => ContestUser::find()->where(['contest_id' => $model->id])->with('user')->with('userProfile'),
            'pagination' => [
                'pageSize' => 100
            ]
        ]);

        return $this->render('user', [
            'model' => $model,
            'provider' => $provider
        ]);
    }

    public function actionRegister($id, $register = 0)
    {
        $this->layout = 'main';
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }
        $model = $this->findModel($id);

        if ($model->scenario == Contest::SCENARIO_OFFLINE) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if ($model->status == Contest::STATUS_PRIVATE) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if ($register == 1 && !$model->isUserInContest()) {
            Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                'contest_id' => $model->id,
                'user_id' => Yii::$app->user->id
            ])->execute();
            Yii::$app->session->setFlash('success', 'Successfully registered');
            return $this->redirect(['/contest/view', 'id' => $model->id]);
        }
        return $this->render('register', [
            'model' => $model
        ]);
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $newContestPrint = new ContestPrint();

        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }

        if ($model->scenario != Contest::SCENARIO_OFFLINE || $model->getRunStatus() == Contest::STATUS_ENDED) {
            throw new ForbiddenHttpException('The contest does not currently offer print services.');
        }

        if ($newContestPrint->load(Yii::$app->request->post())) {
            $newContestPrint->contest_id = $model->id;
            $newContestPrint->save();
            return $this->redirect(['print', 'id' => $model->id]);
        }

        $query = ContestPrint::find()->where(['contest_id' => $model->id, 'user_id' => Yii::$app->user->id])->with('user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('print', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'newContestPrint' => $newContestPrint
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id]),
        ]);

        return $this->render('/contest/view', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionEditorial($id)
    {
        $model = $this->findModel($id);

        if ($model->getRunStatus() == Contest::STATUS_ENDED) {
            return $this->render('/contest/editorial', [
                'model' => $model
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    public function actionClarify($id, $cid = -1)
    {
        $model = $this->findModel($id);
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $newClarify = new Discuss();
        $discuss = null;
        $dataProvider = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id]),
        ]);

        if ($cid != -1) {
            if (($discuss = Discuss::findOne(['id' => $cid, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])) === null) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }
        if (!Yii::$app->user->isGuest && $newClarify->load(Yii::$app->request->post())) {
            if (!$model->isUserInContest()) {
                Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                   'contest_id' => $model->id,
                   'user_id' => Yii::$app->user->id
                ])->execute();
            }
            $newClarify->entity = Discuss::ENTITY_CONTEST;
            $newClarify->entity_id = $model->id;
            if ($discuss !== null) {
                if (empty($newClarify->content)) {
                    Yii::$app->session->setFlash('error', 'The content can not be blank');
                    return $this->refresh();
                }
                $newClarify->parent_id = $discuss->id;
                $discuss->updated_at = new Expression('NOW()');
                $discuss->update();
            } else if (empty($newClarify->title)) {
                Yii::$app->session->setFlash('error', 'The title can not be blank');
                return $this->refresh();
            }
            $newClarify->status = Discuss::STATUS_PUBLIC;
            $newClarify->save();

            try {
                $client = stream_socket_client('tcp://0.0.0.0:2121', $errno, $errmsg, 1);
                $uids = Yii::$app->db->createCommand('SELECT id FROM user WHERE role=' . User::ROLE_ADMIN)->queryColumn();
                $content = 'Contest: ' . $model->title .  ' - There are new questions and answers, please go to the homepage to check and reply.';
                foreach ($uids as $uid) {
                    fwrite($client, json_encode([
                        'uid' => $uid,
                        'content' => $content
                    ]) . "\n");
                }
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }
        $query = Discuss::find()
            ->where(['parent_id' => 0, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])
            ->with('user')
            ->orderBy('created_at DESC');
        if (!$model->isContestAdmin()) {
            $query->andWhere('status=1');
        }
        if (!Yii::$app->user->isGuest) {
            $query->orWhere(['parent_id' => 0, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST, 'created_by' => Yii::$app->user->id]);
        }
        $clarifies = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($discuss != null) {
            return $this->render('/contest/clarify_view', [
                'newClarify' => $newClarify,
                'clarify' => $discuss,
                'model' => $model
            ]);
        } else {
            return $this->render('/contest/clarify', [
                'model' => $model,
                'clarifies' => $clarifies,
                'newClarify' => $newClarify,
                'discuss' => $discuss,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    public function actionStanding($id, $showStandingBeforeEnd = 1)
    {
        $model = $this->findModel($id);

        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        if ($showStandingBeforeEnd) {
            $rankResult = $model->getRankData(true);
        } else {
            $rankResult = $model->getRankData(true, time());
        }
        return $this->render('/contest/standing', [
            'model' => $model,
            'rankResult' => $rankResult,
            'showStandingBeforeEnd' => $showStandingBeforeEnd
        ]);
    }

    public function actionStanding2($id, $showStandingBeforeEnd = 1)
    {
        $this->layout = 'basic';
        $model = $this->findModel($id);

        if ($model->status != Contest::STATUS_VISIBLE) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $rankResult = $model->getRankData(true);

        if ($showStandingBeforeEnd) {
            $rankResult = $model->getRankData(true);
        } else {
            $rankResult = $model->getRankData(true, time());
        }
        return $this->render('/contest/standing2', [
            'model' => $model,
            'rankResult' => $rankResult,
            'showStandingBeforeEnd' => $showStandingBeforeEnd
        ]);
    }

    public function actionProblem($id, $pid = 0)
    {
        $model = $this->findModel($id);

        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $solution = new Solution();

        $problem = $model->getProblemById(intval($pid));

        if (!Yii::$app->user->isGuest && $solution->load(Yii::$app->request->post())) {

            if (!$model->isUserInContest()) {
                Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                    'contest_id' => $model->id,
                    'user_id' => Yii::$app->user->id
                ])->execute();
            }
            if ($model->getRunStatus() == Contest::STATUS_NOT_START) {
                Yii::$app->session->setFlash('error', 'The contest has not started.');
                return $this->refresh();
            }
            if ($model->isContestEnd() && time() < strtotime($model->end_time) + 5 * 60) {
                Yii::$app->session->setFlash('error', 'The contest is finished. Submissions open 05 minutes after the contest');
                return $this->refresh();
            }
            $solution->problem_id = $problem['id'];
            $solution->contest_id = $model->id;
            $solution->status = Solution::STATUS_HIDDEN;
            $solution->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }
        $submissions = [];
        if (!Yii::$app->user->isGuest) {
            $submissions = (new Query())->select('created_at, result, id')
                ->from('{{%solution}}')
                ->where([
                    'problem_id' => $problem['id'] ?? null,
                    'contest_id' => $model->id,
                    'created_by' => Yii::$app->user->id
                ])
                ->orderBy('id DESC')
                ->limit(10)
                ->all();
        }
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('/contest/problem', [
                'model' => $model,
                'solution' => $solution,
                'problem' => $problem,
                'submissions' => $submissions
            ]);
        } else {
            return $this->render('/contest/problem', [
                'model' => $model,
                'solution' => $solution,
                'problem' => $problem,
                'submissions' => $submissions
            ]);
        }
    }

    protected function findModel($id)
    {
        if (($model = Contest::findOne($id)) !== null) {
            if ($model->status != Contest::STATUS_HIDDEN || !Yii::$app->user->isGuest && Yii::$app->user->id === $model->created_by) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
