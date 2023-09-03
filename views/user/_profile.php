<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'nickname')->textInput() ?>

<?= $form->field($profile, 'qq_number')->textInput() ?>

<?= $form->field($profile, 'student_number')->textInput() ?>

<?= $form->field($profile, 'gender')->radioList([Yii::t('app', 'Male'), Yii::t('app', 'Female')]) ?>

<?= $form->field($profile, 'major')->textInput() ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
