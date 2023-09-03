<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Rejudge';
?>

<div class="contest-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($rejudge, 'problem_id') ?>

    <?= $form->field($rejudge, 'contest_id')->dropDownList($rejudge->getContestIdList()) ?>

    <?= $form->field($rejudge, 'run_id') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
