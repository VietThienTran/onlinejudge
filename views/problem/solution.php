<?php

use yii\helpers\Html;

$this->title = $model->id . ' - ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/problem/view', 'id' => $model->id]];
?>
<div class="news-view">
    <h1 class="news-title">
        <?= Html::a(Html::encode($this->title), ['/problem/view', 'id' => $model->id]) ?>
    </h1>
    <div class="news-content">
        <?= Yii::$app->formatter->asMarkdown($model->solution) ?>
    </div>
</div>
