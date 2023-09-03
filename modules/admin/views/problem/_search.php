<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="problem-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'class' => 'form-inline',
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-sunglasses'></span> pid</span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => 'Problem ID'])->label(false) ?>

    <?= $form->field($model, 'title', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">Title</span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => Yii::t('app', 'Title')])->label(false) ?>

    <?= $form->field($model, 'source', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">Source</span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => Yii::t('app', 'Source')])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
