<?php

use yii\helpers\Html;

$this->title = $model->name;
?>
<div class="group-update">

    <h1><?= Html::a(Html::encode($this->title), ['/group/view', 'id' => $model->id]) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <hr>

    <?= Html::a('Delete this group', ['/group/delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => 'The system will delete all data related to the group. Are you sure you want to delete?',
        'data-method' => 'post',
    ]) ?>

</div>
