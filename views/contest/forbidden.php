<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Contest;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contest'), 'url' => ['/contest/index']];
$this->params['model'] = $model;
?>
<?php if ($model->status == Contest::STATUS_PRIVATE): ?>
    <h2 class="text-center"><?= Yii::t('app','This contest can only be viewed by contestants.')?></h2>
    <?php
        $this->title = Yii::$app->setting->get('ojName');
        $this->params['model']->title = '';
        $this->params['model']->start_time = '';
        $this->params['model']->end_time = '';
    ?>
<?php else: ?>
    <h2 class="text-center"><?= Yii::t('app','You have not registered for this contest, please register, or visit after the contest is finish')?></h2>
    <hr>
    <?php if ($model->getRunStatus() == Contest::STATUS_RUNNING): ?>
        <a href="<?= Url::toRoute(['/contest/standing2', 'id' => $model->id]) ?>">
            <h3 class="text-center"><?= Yii::t('app','View standing')?></h3>
        </a>
    <?php endif; ?>
    <?php if ($model->scenario == Contest::SCENARIO_OFFLINE): ?>
        <p><?= Yii::t('app','This contest is offline, if you want to participate, please contact the administrator'?></p>
    <?php else: ?>
        <h4><?= Yii::t('app','Entry Agreement')?></h4>
        <p><?= Yii::t('app','1. Not sharing solutions with others')?></p>
        <p><?= Yii::t('app','2. Do not destroy or attack the judge system')?></p>

        <?= Html::a(Yii::t('app', 'Agree above and register'), ['/contest/register', 'id' => $model->id, 'register' => 1]) ?>
    <?php endif; ?>
<?php endif; ?>