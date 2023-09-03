<?php

namespace app\controllers;

use app\components\BaseController;
use Yii;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use app\models\Problem;
use app\models\Solution;
use app\models\User;
use app\widgets\tagging\TaggingQuery;
use app\models\Discuss;

class ProblemController extends BaseController
{
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
        $query = Problem::find();

        if (Yii::$app->request->get('tag') != '') {
            $query->andWhere('tags LIKE :tag', [':tag' => '%' . Yii::$app->request->get('tag') . '%']);
        }
        if (($post = Yii::$app->request->post())) {
            $query->orWhere(['like', 'title', $post['q']])
                ->orWhere(['like', 'id', $post['q']])
                ->orWhere(['like', 'source', $post['q']]);
        }
        $query->andWhere('status<>' . Problem::STATUS_HIDDEN);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        $cache = Yii::$app->cache;
        $tags = $cache->get('problem-tags');
        if ($tags === false) {
            $tags = (new TaggingQuery())->select('tags')
                ->from('{{%problem}}')
                ->where('status<>' . Problem::STATUS_HIDDEN)
                ->limit(30)
                ->displaySort(['freq' => SORT_DESC])
                ->getTags();
            $cache->set('problem-tags', $tags, 3600);
        }

        $solvedProblem = [];
        if (!Yii::$app->user->isGuest) {
            $solved = (new Query())->select('problem_id')
                ->from('{{%solution}}')
                ->where(['created_by' => Yii::$app->user->id, 'result' => Solution::OJ_AC])
                ->all();
            foreach ($solved as $k) {
                $solvedProblem[$k['problem_id']] = true;
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tags' => $tags,
            'solvedProblem' => $solvedProblem
        ]);
    }

    public function actionStatistics($id)
    {
        $model = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => Solution::find()->with('user')
                ->where(['problem_id' => $model->id, 'result' => Solution::OJ_AC])
                ->orderBy(['time' => SORT_ASC, 'memory' => SORT_ASC, 'code_length' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('stat', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDiscuss($id)
    {
        $model = $this->findModel($id);
        $newDiscuss = new Discuss();
        $newDiscuss->setScenario('problem');

        $query = Discuss::find()->where([
            'entity' => Discuss::ENTITY_PROBLEM,
            'entity_id' => $model->id,
            'parent_id' => 0
        ])->orderBy('updated_at DESC');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $discusses = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        if ($newDiscuss->load(Yii::$app->request->post())) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Please login.');
                return $this->redirect(['/site/login']);
            }
            $newDiscuss->entity = Discuss::ENTITY_PROBLEM;
            $newDiscuss->entity_id = $id;
            $newDiscuss->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }

        return $this->render('discuss', [
            'model' => $model,
            'discusses' => $discusses,
            'pages' => $pages,
            'newDiscuss' => $newDiscuss
        ]);
    }

    public function actionView($id, $view = 'view')
    {
        $model = $this->findModel($id);
        $solution = new Solution();
        $submissions = NULL;
        if (!Yii::$app->user->isGuest) {
            $submissions = (new Query())->select('created_at, result, id')
                ->from('{{%solution}}')
                ->where([
                    'problem_id' => $id,
                    'created_by' => Yii::$app->user->id
                ])
                ->orderBy('id DESC')
                ->limit(10)
                ->all();
        }
        if ($solution->load(Yii::$app->request->post())) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Please login.');
                return $this->redirect(['/site/login']);
            }
            $solution->problem_id = $model->id;
            $solution->status = Solution::STATUS_VISIBLE;
            $solution->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }
        $view = ($view == 'view' ? 'view' : 'classic');

        return $this->render($view, [
            'solution' => $solution,
            'model' => $model,
            'submissions' => $submissions
        ]);
    }

    public function actionSolution($id)
    {
        $model = $this->findModel($id);

        return $this->render('solution', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Problem::findOne($id)) !== null) {
            $isVisible = ($model->status == Problem::STATUS_VISIBLE);
            $isPrivate = ($model->status == Problem::STATUS_PRIVATE);
            if ($isVisible || ($isPrivate && !Yii::$app->user->isGuest &&
                               (Yii::$app->user->identity->role === User::ROLE_VIP || Yii::$app->user->identity->role === User::ROLE_ADMIN))) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
