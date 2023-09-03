<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->user->nickname . ' [' . $model->user->username . ']';
$this->params['breadcrumbs'][] = ['label' => $model->contest->title, 'url' => ['/contest/view', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = ['label' => 'Print Sources', 'url' => ['index', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
@media print {
.breadcrumb {
    display: none;
}
}
');
?>
<div class="print-source-view">
    <h1>
        <span class="glyphicon glyphicon-user"></span> <?= Html::encode($this->title) ?>
    </h1>
    <p class="hidden-print">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php if (Yii::$app->user->identity->role === \app\models\User::ROLE_ADMIN): ?>
        <div class="alert alert-warning alert-dismissible fade in hidden-print" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <p>Tip: You can use your browser's print function to print quickly.</p>
        </div>
    <?php endif; ?>
    <hr>
    <p><span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></p>
    <pre><p><?= Html::encode($model->source) ?></p></pre>

</div>
