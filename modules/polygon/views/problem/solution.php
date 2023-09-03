<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Solution;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

$model->setSamples();
?>
<p>
    Please provide the correct code to solve problem on this page. It will be used to generate standard output of test data.
</p>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'solution_lang')->dropDownList(Solution::getLanguageList()) ?>

    <?= $form->field($model, 'solution_source')->widget('app\widgets\codemirror\CodeMirror'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
