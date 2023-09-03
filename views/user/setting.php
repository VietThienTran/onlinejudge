<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;

$this->title = Html::encode($model->username);
$this->params['breadcrumbs'][] = Yii::t('app', 'Setting');
?>
<div class="user-update">
    <div class="contest-view">
        <?php
        $menuItems = [
            [
                'label' => Yii::t('app', 'Profile'),
                'url' => ['user/setting', 'action' => 'profile'],
            ],
            [
                'label' => Yii::t('app', 'Account'),
                'url' => ['user/setting', 'action' => 'account'],
            ],
            [
                'label' => Yii::t('app', 'Security'),
                'url' => ['user/setting', 'action' => 'security'],
            ]
        ];
        echo Nav::widget([
            'items' => $menuItems,
            'options' => ['class' => 'nav nav-tabs']
        ]) ?>
    </div>
    <div class="user-form" style="padding: 10px 0">
        <?= $this->render('_' . $action, [
            'model' => $model,
            'profile' => $profile
        ]) ?>
    </div>
</div>
