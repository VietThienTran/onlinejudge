<?php

use yii\helpers\Html;
use app\models\Solution;
use yii\widgets\ActiveForm;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
?>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <div class="table-responsive">
        <table class="table table-bordered table-rank">
            <thead>
            <tr>
                <th width="60px">Run ID</th>
                <th width="60px">Submited Time</th>
                <th width="100px">Result</th>
                <th width="60px">Language</th>
                <th width="70px">Time</th>
                <th width="80px">Memory</th>
                <th width="80px">Code Length</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($solutions as $solution): ?>
                <tr>
                    <th><?= $solution['id'] ?></th>
                    <th>
                        <?= $solution['created_at'] ?>
                    </th>
                    <th>
                        <?= Html::a(Solution::getResultList($solution['result']), ['/solution/detail', 'id' => $solution['id']], ['target' => '_blank']); ?>
                    </th>
                    <th>
                        <?= Html::a(Solution::getLanguageList($solution['language']), ['/solution/detail', 'id' => $solution['id']], ['target' => '_blank']) ?>
                    </th>
                    <th>
                        <?= $solution['time'] ?>
                    </th>
                    <th>
                        <?= $solution['memory'] ?>
                    </th>
                    <th>
                        <?= $solution['code_length'] ?>
                    </th>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($newSolution, 'language')->dropDownList($newSolution::getLanguageList()) ?>

    <?= $form->field($newSolution, 'source')->widget('app\widgets\codemirror\CodeMirror'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
