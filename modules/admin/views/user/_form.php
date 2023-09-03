<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'newPassword')->textInput() ?>

    <?= $form->field($model, 'role')->radioList([
        $model::ROLE_PLAYER => 'Contestant',
        $model::ROLE_USER => 'Normal User',
        $model::ROLE_VIP => 'VIP',
        $model::ROLE_ADMIN => 'Administrator'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
