<?php

use yii\helpers\Html;
use app\models\GroupUser;

?>

<h3><?= Yii::t('app','Manage users') . ': ' . Html::a(Html::encode($groupUser->user->nickname), ['/group/view', 'id' => $groupUser->user->id]) ?></h3>
<hr>
<h4><?= Yii::t('app','Current role') . ': ' ?><?= $groupUser->getRole() ?></h4>
<?php if ($groupUser->role == GroupUser::ROLE_APPLICATION): ?>
    <?= Html::a(Yii::t('app','Agree to join'), ['/group/user-update', 'id' => $groupUser->id, 'role' => 1], ['class' => 'btn btn-success']); ?>
    <?= Html::a(Yii::t('app','Refuse to join'), ['/group/user-update', 'id' => $groupUser->id, 'role' => 2], ['class' => 'btn btn-danger']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_REUSE_INVITATION): ?>
    <?= Html::a(Yii::t('app','Re-invite'), ['/group/user-update', 'id' => $groupUser->id, 'role' => 3], ['class' => 'btn btn-default']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_MEMBER && $model->getRole() == GroupUser::ROLE_LEADER): ?>
    <?= Html::a(Yii::t('app','Set as admin'), ['/group/user-update', 'id' => $groupUser->id, 'role' => 4], ['class' => 'btn btn-default']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_MANAGER && $model->getRole() == GroupUser::ROLE_LEADER): ?>
    <?= Html::a(Yii::t('app','Make regular member'), ['/group/user-update', 'id' => $groupUser->id, 'role' => 5], ['class' => 'btn btn-default']); ?>
<?php endif; ?>
