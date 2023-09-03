<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="print-source-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'source')->textarea(['rows' => 20]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
