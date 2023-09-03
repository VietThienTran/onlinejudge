<?php

use yii\helpers\Html;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
?>
<div class="row">
    <div class="col-md-9 problem-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <div class="content-wrapper">
            <?= Yii::$app->formatter->asMarkdown($model->description) ?>
        </div>

        <h3><?= Yii::t('app', 'Input') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asMarkdown($model->input) ?>
        </div>

        <h3><?= Yii::t('app', 'Output') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asMarkdown($model->output) ?>
        </div>

        <h3><?= Yii::t('app', 'Examples') ?></h3>
        <div class="content-wrapper">
            <div class="sample-test">
                <div class="input">
                    <h4><?= Yii::t('app', 'Input') ?></h4>
                    <pre><?= Html::encode($model->sample_input) ?></pre>
                </div>
                <div class="output">
                    <h4><?= Yii::t('app', 'Output') ?></h4>
                    <pre><?= Html::encode($model->sample_output) ?></pre>
                </div>

                <?php if ($model->sample_input_2 != '' || $model->sample_output_2 != ''):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= Html::encode($model->sample_input_2) ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= Html::encode($model->sample_output_2) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if ($model->sample_input_3 != '' || $model->sample_output_3 != ''):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= Html::encode($model->sample_input_3) ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= Html::encode($model->sample_output_3) ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($model->hint)): ?>
            <h3><?= Yii::t('app', 'Hint') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asMarkdown($model->hint) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($model->source)): ?>
            <h3><?= Yii::t('app', 'Source') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asMarkdown($model->source) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-3 problem-info">
        <div class="panel panel-default">
            <div class="panel-heading">Information</div>
            <!-- Table -->
            <table class="table">
                <tbody>
                <tr>
                    <td><?= Yii::t('app', 'Time Limit') ?></td>
                    <td><?= $model->time_limit ?> Second</td>
                </tr>
                <tr>
                    <td><?= Yii::t('app', 'Memory Limit') ?></td>
                    <td><?= $model->memory_limit ?> MB</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
