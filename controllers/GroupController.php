<?php

namespace app\controllers;

use app\components\BaseController;
use Yii;
use app\models\Group;
use app\models\GroupUser;
use app\models\GroupSearch;
use app\models\Contest;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;
use yii\data\ActiveDataProvider;

class GroupController extends BaseController
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
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'actions' => ['create', 'accept', 'my-group', 'update', 'user-delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionMyGroup()
    {
        $count = Yii::$app->db->createCommand('
            SELECT COUNT(*) FROM {{%group}} AS g LEFT JOIN {{%group_user}} AS u ON u.group_id=g.id WHERE u.user_id=:id',
            [':id' => Yii::$app->user->id]
        )->queryScalar();
        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT g.id,g.name,g.description FROM {{%group}} AS g LEFT JOIN {{%group_user}} AS u ON u.group_id=g.id WHERE u.user_id=:id AND u.role <> 0',
            'params' => [':id' => Yii::$app->user->id],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAccept($id, $accept = -1)
    {
        $model = $this->findModel($id);
        if ($model->isMember()) {
            return $this->redirect(['/group/view', 'id' => $model->id]);
        }
        if ($model->join_policy == Group::JOIN_POLICY_INVITE && $model->getRole() != GroupUser::ROLE_INVITING) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $userDataProvider = new ActiveDataProvider([
            'query' => GroupUser::find()->where([
                'group_id' => $model->id
            ])->with('user')->orderBy(['role' => SORT_DESC])
        ]);
        if (!Yii::$app->user->isGuest) {
            $role = $model->getRole();
            if ($accept == 0 && $role == GroupUser::ROLE_INVITING) { 
                Yii::$app->db->createCommand()->update('{{%group_user}}', [
                    'role' => GroupUser::ROLE_REUSE_INVITATION
                ], ['user_id' => Yii::$app->user->id, 'group_id' => $model->id])->execute();
                Yii::$app->session->setFlash('info', 'Rejected');
                return $this->redirect(['/group/index']);
            } else if ($accept == 1 && $role == GroupUser::ROLE_INVITING) { 
                Yii::$app->db->createCommand()->update('{{%group_user}}', [
                    'role' => GroupUser::ROLE_MEMBER
                ], ['user_id' => Yii::$app->user->id, 'group_id' => $model->id])->execute();
                Yii::$app->session->setFlash('success', 'Joined');
                return $this->redirect(['/group/view', 'id' => $model->id]);
            } else if ($model->join_policy == Group::JOIN_POLICY_FREE && $accept == 2 && !$role) { 
                Yii::$app->db->createCommand()->insert('{{%group_user}}', [
                    'user_id' => Yii::$app->user->id,
                    'group_id' => $model->id,
                    'created_at' => new Expression('NOW()'),
                    'role' => GroupUser::ROLE_MEMBER
                ])->execute();
                Yii::$app->session->setFlash('info', 'Joined');
            } else if ($model->join_policy == Group::JOIN_POLICY_APPLICATION && $accept == 3 && !$role) { 
                Yii::$app->db->createCommand()->insert('{{%group_user}}', [
                    'user_id' => Yii::$app->user->id,
                    'group_id' => $model->id,
                    'created_at' => new Expression('NOW()'),
                    'role' => GroupUser::ROLE_APPLICATION
                ])->execute();
                Yii::$app->session->setFlash('info', 'Already applied');
            }
        }
        Yii::$app->cache->delete('role' . $model->id . '_' . Yii::$app->user->id);
        return $this->render('accept', [
            'model' => $model,
            'userDataProvider' => $userDataProvider
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $role = $model->getRole();
        if (!$model->isMember() && ($role == GroupUser::ROLE_INVITING ||
                                    $role == GroupUser::ROLE_APPLICATION ||
                                    $model->join_policy == Group::JOIN_POLICY_FREE ||
                                    $model->join_policy == Group::JOIN_POLICY_APPLICATION)) {
            return $this->redirect(['/group/accept', 'id' => $model->id]);
        } else if (!$model->isMember() && $model->join_policy == Group::JOIN_POLICY_INVITE) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $newGroupUser = new GroupUser();
        $newContest = new Contest();
        $newContest->type = Contest::TYPE_RANK_GROUP;
        $contestDataProvider = new ActiveDataProvider([
            'query' => Contest::find()->where([
                'group_id' => $model->id
            ])->orderBy(['id' => SORT_DESC]),
        ]);

        $userDataProvider = new ActiveDataProvider([
            'query' => GroupUser::find()->where([
                'group_id' => $model->id
            ])->with('user')->orderBy(['role' => SORT_DESC])
        ]);

        if ($newContest->load(Yii::$app->request->post())) {
            if (!$model->hasPermission()) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
            $newContest->group_id = $model->id;
            $newContest->scenario = Contest::SCENARIO_ONLINE;
            $newContest->status = Contest::STATUS_PRIVATE;
            $newContest->save();
            return $this->refresh();
        }

        if ($newGroupUser->load(Yii::$app->request->post())) {
            if (!$model->hasPermission()) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
            $query = (new Query())->select('u.id as user_id, count(g.user_id) as exist')
                ->from('{{%user}} as u')
                ->leftJoin('{{%group_user}} as g', 'g.user_id=u.id')
                ->where('u.username=:name and g.group_id=:gid', [':name' => $newGroupUser->username, ':gid' => $model->id])
                ->one();
            if (!isset($query['user_id'])) {
                Yii::$app->session->setFlash('error', 'User does not exist');
            } else if (!$query['exist']) {
                $newGroupUser->role = GroupUser::ROLE_INVITING;
                $newGroupUser->created_at = new Expression('NOW()');
                $newGroupUser->user_id = $query['user_id'];
                $newGroupUser->group_id = $model->id;
                $newGroupUser->save();
                Yii::$app->session->setFlash('success', 'Invited');
            } else {
                Yii::$app->db->createCommand()->update('{{%group_user}}', [
                    'role' => GroupUser::ROLE_INVITING
                ], ['user_id' => $query['user_id'], 'group_id' => $model->id])->execute();
                Yii::$app->session->setFlash('error', 'Invited');
            }
            return $this->refresh();
        }

        return $this->render('view', [
            'model' => $model,
            'contestDataProvider' => $contestDataProvider,
            'userDataProvider' => $userDataProvider,
            'newGroupUser' => $newGroupUser,
            'newContest' => $newContest
        ]);
    }

    public function actionCreate()
    {
        $model = new Group();
        $model->status = Group::STATUS_VISIBLE;
        $model->join_policy = Group::JOIN_POLICY_INVITE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $groupUser = new GroupUser();
            $groupUser->role = GroupUser::ROLE_LEADER;
            $groupUser->created_at = new Expression('NOW()');
            $groupUser->user_id = Yii::$app->user->id;
            $groupUser->group_id = $model->id;
            $groupUser->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest && ($model->getRole() == GroupUser::ROLE_LEADER || Yii::$app->user->identity->isAdmin())) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }

            return $this->render('update', [
                'model' => $model,
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    public function actionUserDelete($id)
    {
        $groupUser = GroupUser::findOne($id);
        $group = $this->findModel($groupUser->group_id);
        if ($group->hasPermission() && $groupUser->role != GroupUser::ROLE_LEADER) {
            Yii::$app->cache->delete('role' . $group->id . '_' . $groupUser->user_id);
            $groupUser->delete();
            return $this->redirect(['/group/view', 'id' => $group->id]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    public function actionUserUpdate($id, $role = 0)
    {
        $groupUser = GroupUser::findOne($id);
        $group = $this->findModel($groupUser->group_id);
        if (!$group->hasPermission()) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        if ($role == 1) { 
            $groupUser->role = GroupUser::ROLE_MEMBER;
        } else if ($role == 2) { 
            $groupUser->role = GroupUser::ROLE_REUSE_APPLICATION;
        } else if ($role == 3) { 
            $groupUser->role = GroupUser::ROLE_INVITING;
        } else if ($role == 4 && $group->getRole() == GroupUser::ROLE_LEADER) { 
            $groupUser->role = GroupUser::ROLE_MANAGER;
        } else if ($role == 5 && $group->getRole() == GroupUser::ROLE_LEADER) { 
            $groupUser->role = GroupUser::ROLE_MEMBER;
        }
        if ($role != 0) {
            $groupUser->update();
            Yii::$app->cache->delete('role' . $group->id . '_' . $groupUser->user_id);
            return $this->redirect(['/group/view', 'id' => $group->id]);
        }

        return $this->renderAjax('user_manager', [
            'model' => $group,
            'groupUser' => $groupUser
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest && ($model->created_by === Yii::$app->user->id || Yii::$app->user->identity->isAdmin())) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Deleted');
            return $this->redirect(['index']);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    protected function findModel($id)
    {
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
