<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Html::encode($model->title);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-view">
    <h1 class="news-title">
        <?= $this->title ?>
    </h1>
    <div class="news-meta">
        <span class="glyphicon glyphicon-time icon-muted"></span> <?= Yii::$app->formatter->asDate($model->created_at) ?>
    </div>
    <div class="news-content">
        <?= Yii::$app->formatter->asMarkdown($model->content) ?>
    </div>
</div>
