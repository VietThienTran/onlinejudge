<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\models\Contest;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$problems = $model->problems;
?>
<div class="contest-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <p>
        <?php if ($model->scenario == Contest::SCENARIO_OFFLINE): ?>
        <?= Html::a(Yii::t('app', 'Source Print Queue'), ['/print', 'id' => $model->id], ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
        <?php endif; ?>
        <?= Html::a(Yii::t('app', 'Clarification'), ['clarify', 'id' => $model->id], ['class' => 'btn btn-warning', 'target' => '_blank']) ?>
        <?= Html::a(Yii::t('app', 'Submit records'), ['status', 'id' => $model->id], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
    </p>
    <p>
        <?= Html::a(Yii::t('app', 'Calculate rating'), ['rated', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('app', 'Contest User'), ['register', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Editorial'), ['editorial', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <p>
        <?= Html::a(Yii::t('app', 'Print Problem'), ['print', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
        <?= Html::a(Yii::t('app', 'Print Rank'), ['rank', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']) ?>
        <?= Html::a('Standing', ['/contest/standing2', 'id' => $model->id], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
    </p>
    <?php if ($model->scenario == Contest::SCENARIO_OFFLINE): ?>
        <?php Modal::begin([
            'header' => '<h3>'.Yii::t('app','Scroll Scoreboard').'</h3>',
            'toggleButton' => ['label' => Yii::t('app', 'Scroll Scoreboard'), 'class' => 'btn btn-success'],
        ]); ?>
        <?= Html::beginForm(['contest/scroll-scoreboard', 'id' => $model->id], 'get', ['target' => '_blank']) ?>
        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Number of gold medals'), 'gold') ?>
            <?= Html::textInput('gold', round($model->getContestUserCount() * 0.1), ['class' => 'form-control']) ?>
        </div>
        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Number of silver medals'), 'silver') ?>
            <?= Html::textInput('silver', round($model->getContestUserCount() * 0.2), ['class' => 'form-control']) ?>
        </div>
        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Number of bronze medals'), 'bronze') ?>
            <?= Html::textInput('bronze', round($model->getContestUserCount() * 0.3), ['class' => 'form-control']) ?>
        </div>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Open the About page'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= Html::endForm(); ?>
        <?php Modal::end(); ?>
    <?php endif; ?>
    <hr>
    <h3>
        <?= Yii::t('app', 'Information') ?>
        <small><?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?></small>
    </h3>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'start_time',
            'end_time',
            'lock_board_time',
            'description:html',
            [
                'label' => Yii::t('app', 'Scenario'),
                'value' => $model->scenario == Contest::SCENARIO_ONLINE ? Yii::t('app', 'Online') : Yii::t('app', 'Offline')
            ]
        ],
    ]) ?>

    <hr>
    <h3>
        <?= Yii::t('app', 'Announcements') ?>
        <?php Modal::begin([
            'header' => '<h3>'.Yii::t('app','Make an announcement').'</h3>',
            'toggleButton' => ['label' => Yii::t('app', 'Create'), 'class' => 'btn btn-success'],
        ]); ?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($newAnnouncement, 'content')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>

        <?php Modal::end(); ?>
    </h3>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $announcements,
        'columns' => [
            'content:ntext',
            'created_at:datetime',
        ],
    ]) ?>

    <hr>
    <h3>
        <?= Yii::t('app', 'Problems') ?>
    </h3>
    <?php Modal::begin([
        'header' => '<h3>'.Yii::t('app','Set topic source').'</h3>',
        'toggleButton' => ['label' => 'Set the source for all the following problems', 'class' => 'btn btn-success'],
    ]); ?>
    <?= Html::beginForm(['contest/set-problem-source', 'id' => $model->id]) ?>
    <div class="form-group">
        <?= Html::label(Yii::t('app', 'Source'), 'problem_id') ?>
        <?= Html::textInput('source', $model->title,['class' => 'form-control']) ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
    <?php Modal::end(); ?>

    <?php Modal::begin([
        'header' => '<h3>'.Yii::t('app','Set all the following topics to display in the homepage').'</h3>',
        'toggleButton' => ['label' => 'Set the status of the topic to be displayed in the homepage', 'class' => 'btn btn-success'],
    ]); ?>
    <?= Html::beginForm(['contest/set-problem-status', 'id' => $model->id]) ?>
    <div class="form-group">
        <?= Html::label(Yii::t('app', 'Status'), 'status') ?>
        <label class="radio-inline">
            <input type="radio" name="status" value="<?= \app\models\Problem::STATUS_VISIBLE ?>">
            <?= Yii::t('app', 'Visible') ?>
        </label>
        <label class="radio-inline">
            <input type="radio" name="status" value="<?= \app\models\Problem::STATUS_HIDDEN ?>">
            <?= Yii::t('app', 'Hidden') ?>
        </label>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
    <?php Modal::end(); ?>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th width="70px">#</th>
                <th width="120px">Problem ID</th>
                <th><?= Yii::t('app', 'Problem Name') ?></th>
                <th width="200px"><?= Yii::t('app', 'Operation') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($problems as $key => $p): ?>
                <tr>
                    <th><?= Html::a(chr(65 + $key), ['/admin/problem/view', 'id' => $p['problem_id']]) ?></th>
                    <th><?= Html::a($p['problem_id'], ['/admin/problem/view', 'id' => $p['problem_id']]) ?></th>
                    <td><?= Html::a(Html::encode($p['title']), ['/admin/problem/view', 'id' => $p['problem_id']]) ?></td>
                    <th>
                        <?php Modal::begin([
                            'header' => '<h3>'. Yii::t('app','Modify') . ' : ' . chr(65 + $key) . '</h3>',
                            'toggleButton' => ['label' => Yii::t('app','Modify'), 'class' => 'btn btn-success'],
                        ]); ?>

                        <?= Html::beginForm(['contest/updateproblem', 'id' => $model->id]) ?>

                        <div class="form-group">
                            <?= Html::label(Yii::t('app', 'Current Problem ID'), 'problem_id') ?>
                            <?= Html::textInput('problem_id', $p['problem_id'],['class' => 'form-control', 'readonly' => 1]) ?>
                        </div>

                        <div class="form-group">
                            <?= Html::label(Yii::t('app', 'New Problem ID'), 'new_problem_id') ?>
                            <?= Html::textInput('new_problem_id', $p['problem_id'],['class' => 'form-control']) ?>
                        </div>

                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                        </div>
                        <?= Html::endForm(); ?>

                        <?php Modal::end(); ?>
                        <?= Html::a(Yii::t('app', 'Delete'), [
                                'deleteproblem',
                                'id' => $model->id,
                                'pid' => $p['problem_id']
                            ],[
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                'method' => 'post',
                            ],
                        ]) ?>
                    </th>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th></th>
                <th></th>
                <th>
                    <?php Modal::begin([
                        'header' => '<h3>' . Yii::t('app','Add a problem') . '</h3>',
                        'toggleButton' => ['label' => Yii::t('app','Add a problem'), 'class' => 'btn btn-success'],
                    ]); ?>

                    <?= Html::beginForm(['contest/addproblem', 'id' => $model->id]) ?>

                    <div class="form-group">
                        <?= Html::label(Yii::t('app', 'Problem ID'), 'problem_id') ?>
                        <?= Html::textInput('problem_id', '',['class' => 'form-control']) ?>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= Html::endForm(); ?>

                    <?php Modal::end(); ?>
                </th>
                <th></th>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Information').'</h3>',
    'options' => ['id' => 'modal-info'],
    'size' => Modal::SIZE_LARGE
]); ?>
<div id="modal-content">
</div>
<?php Modal::end(); ?>
<?php
$js = "
$('[data-click=modal]').click(function() {
    $.ajax({
        url: $(this).attr('href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#modal-content').html(html);
            $('#modal-info').modal('show');
        }
    });
});
";
$this->registerJs($js);
?>
