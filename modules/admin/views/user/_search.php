<?php

use app\models\User;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="solution-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'class' => 'form-inline',
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-sunglasses'></span> User ID</span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => 'User ID'])->label(false) ?>

    <?= $form->field($model, 'username', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-user'></span></span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => 'Username'])->label(false) ?>

    <?= $form->field($model, 'email', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-envelope'></span></span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => 'Email'])->label(false) ?>

    <?= $form->field($model, 'role', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">Role</span>{input}</div>",
    ])->dropDownList([
        '' => 'All users',
        User::ROLE_PLAYER => 'Contestant',
        User::ROLE_USER => 'Normal User',
        User::ROLE_VIP => 'VIP',
        User::ROLE_ADMIN => 'Administrator',

    ])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
