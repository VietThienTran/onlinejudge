<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Release news');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'News'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$model->status = $model::STATUS_PUBLIC;
?>
<div class="discuss-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
