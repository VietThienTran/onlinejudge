<?php

namespace app\modules\admin\controllers;

use app\models\ContestProblem;
use app\models\Discuss;
use app\models\ProblemSearch;
use Yii;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\AccessRule;
use app\models\User;
use app\models\Problem;
use app\models\Solution;
use app\modules\admin\models\UploadForm;

class ProblemController extends Controller
{
    public $enableCsrfValidation = false;
    public $layout = 'main';
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [
                            User::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ProblemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->isPost) {
            $keys = Yii::$app->request->post('keylist');
            $action = Yii::$app->request->get('action');
            foreach ($keys as $key) {
                if ($action == 'delete') {
                    $model = $this->findModel($key);
                    try {
                        $this->makeDirEmpty(Yii::$app->params['judgeProblemDataPath'] . $model->id);
                        rmdir(Yii::$app->params['judgeProblemDataPath'] . $model->id);
                    } catch (\ErrorException $e) {
                        Yii::$app->session->setFlash('error', 'Failed to delete: ' . $e->getMessage());
                        return $this->redirect(['index']);
                    }
                    $model->delete();
                } else {
                    foreach ($keys as $key) {
                        Yii::$app->db->createCommand()->update('{{%problem}}', [
                            'status' => $action
                        ], ['id' => $key])->execute();
                    }
                }
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
    
    public function actionDeletefile($id, $name)
    {
        $model = $this->findModel($id);
        $name = basename($name);
        if ($name == 'in') {
            $files = $model->getDataFiles();
            foreach ($files as $file) {
                if (strpos($file['name'], '.in')) {
                    @unlink(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $file['name']);
                }
            }
        } else if ($name == 'out') {
            $files = $model->getDataFiles();
            foreach ($files as $file) {
                if (strpos($file['name'], '.out')) {
                    @unlink(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $file['name']);
                }
                if (strpos($file['name'], '.ans')) {
                    @unlink(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $file['name']);
                }
            }
        } else if (strpos($name, '.in') || strpos($name, '.out') || strpos($name, '.ans')) {
            @unlink(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $name);
        }
        return $this->redirect(['test-data', 'id' => $model->id]);
    }

    public function actionViewfile($id, $name)
    {
        $model = $this->findModel($id);
        $name = basename($name);
        if (strpos($name, '.in') || strpos($name, '.out') || strpos($name, '.ans')) {
            $filepath = Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $name;
            $fp = fopen($filepath, 'r');
            echo '<pre>';
            while (!feof($fp)) {
                $content = fread($fp, 1024);
                echo $content;
            }
            echo '</pre>';
            fclose($fp);
        }
        die;
    }

    public function actionView($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);
        $model->setSamples();

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionSolution($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            return $this->refresh();
        }

        return $this->render('solution', [
            'model' => $model,
        ]);
    }

    public function actionImport()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->problemFile = UploadedFile::getInstance($model, 'problemFile');
            if ($model->upload()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Imported Successfully'));
            }
            return $this->refresh();
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Problem();

        $model->time_limit = 1;
        $model->memory_limit = 128;
        $model->status = $model::STATUS_HIDDEN;
        $model->spj = 0;

        if ($model->load(Yii::$app->request->post())) {
            $sample_input = [$model->sample_input, $model->sample_input_2, $model->sample_input_3];
            $sample_output = [$model->sample_output, $model->sample_output_2, $model->sample_output_3];
            $model->sample_input = serialize($sample_input);
            $model->sample_output = serialize($sample_output);
            $model->created_by = Yii::$app->user->id;
            try {
                if ($model->save()) {
                    mkdir(Yii::$app->params['judgeProblemDataPath'] . $model->id);
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Creation failed.');
                }
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', 'Creation failed. ID conflict.');
            }
        }
        $model->setSamples();

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCreateFromPolygon()
    {
        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post('polygon_problem_id');
            $fromId = Yii::$app->request->post('polygon_problem_id_from');
            $toId = Yii::$app->request->post('polygon_problem_id_to');
            if (!empty($id)) {
                if ($this->synchronizeProblemFromPolygon($id)) {
                    Yii::$app->session->setFlash('success', $id . ' created Successfully.');
                } else {
                    Yii::$app->session->setFlash('error', $id . ' no such problem.');
                }
            } else if (!empty($fromId) && !empty($toId)) {
                $fromId = intval($fromId);
                $toId = intval($toId);
                for ($i = $fromId; $i <= $toId; $i++) {
                    $this->synchronizeProblemFromPolygon($i);
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            } else {
                Yii::$app->session->setFlash('error', 'Please fill out the form');
            }
            return $this->redirect(['index']);
        }
        return $this->render('create_from_polygon');
    }

    public function actionUpdate($id)
    {
        $this->layout = 'problem';
        $id = intval($id);
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $sample_input = [$model->sample_input, $model->sample_input_2, $model->sample_input_3];
            $sample_output = [$model->sample_output, $model->sample_output_2, $model->sample_output_3];
            $model->sample_input = serialize($sample_input);
            $model->sample_output = serialize($sample_output);
            $oldID = $id;
            $newID = $model->id;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save() && $oldID != $newID) {
                    $dataOldName = Yii::$app->params['judgeProblemDataPath'] . $oldID;
                    $dataNewName = Yii::$app->params['judgeProblemDataPath'] . $newID;
                    rename($dataOldName, $dataNewName);
                    Solution::updateAll(['problem_id' => $newID], ['problem_id' => $oldID]);
                    ContestProblem::updateAll(['problem_id' => $newID], ['problem_id' => $oldID]);
                    Discuss::updateAll(['entity_id' => $newID], ['entity_id' => $oldID, 'entity' => Discuss::ENTITY_PROBLEM]);
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (ErrorException $e) {
                Yii::$app->session->setFlash('error', 'Update failed: Unable to move data directory');
                $transaction->rollBack();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Update failed: ID conflict');
            }
            $model->id = $oldID;
        }
        $model->setSamples();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionTestData($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $ext = substr(strrchr($_FILES["file"]["name"], '.'), 1);
            if ($ext != 'in' && $ext != 'out' && $ext != 'ans') {
                throw new BadRequestHttpException($ext);
            }
            $fileContent = file_get_contents($_FILES["file"]["tmp_name"]);
            file_put_contents($_FILES["file"]["tmp_name"], preg_replace("(\r\n)","\n", $fileContent));
            @move_uploaded_file($_FILES["file"]["tmp_name"], Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $_FILES["file"]["name"]);
        }
        return $this->render('test_data', [
            'model' => $model
        ]);
    }

    public function actionDownloadData($id)
    {
        $model = $this->findModel($id);
        $filename = Yii::$app->params['judgeProblemDataPath'] . $model->id;
        $zipName = '/tmp/' . time() . $id . '.zip';
        if (!file_exists($filename)) {
            return false;
        }
        $zipArc = new \ZipArchive();
        if (!$zipArc->open($zipName, \ZipArchive::CREATE)) {
            return false;
        }
        $res = $zipArc->addGlob("{$filename}/*", GLOB_BRACE, ['remove_all_path' => true]);
        $zipArc->close();
        if (!$res) {
            return false;
        }
        if (!file_exists($zipName)) {
            return false;
        }
        Yii::$app->response->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) { unlink($event->data); }, $zipName);
        return Yii::$app->response->sendFile($zipName, $model->id . '-' . $model->title . '.zip');
    }

    public function actionVerify($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);
        $solutions = (new Query())->select('id, result, created_at, memory, time, language, code_length')
            ->from('{{%solution}}')
            ->where(['problem_id' => $id, 'status' => Solution::STATUS_TEST])
            ->limit(10)
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $newSolution = new Solution();
        $newSolution->language = Yii::$app->user->identity->language;

        if ($newSolution->load(Yii::$app->request->post())) {
            $newSolution->problem_id = $id;
            $newSolution->status = Solution::STATUS_TEST;
            if ($newSolution->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            } else {
                Yii::$app->session->setFlash('error', 'Please select a language');
            }
            return $this->refresh();
        }
        return $this->render('verify', [
            'solutions' => $solutions,
            'newSolution' => $newSolution,
            'model' => $model
        ]);
    }

    public function actionSpj($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);

        $dataPath = Yii::$app->params['judgeProblemDataPath'] . $model->id;
        $spjContent = '';
        if (file_exists($dataPath . '/spj.cc')) {
            $spjContent = file_get_contents($dataPath . '/spj.cc');
        } else if (file_exists($dataPath . '/spj.c')) {
            $spjContent = file_get_contents($dataPath . '/spj.c');
        }
        if (Yii::$app->request->isPost) {
            $spjContent = Yii::$app->request->post('spjContent');
            if (!is_dir($dataPath)) {
                mkdir($dataPath);
            }
            $fp = fopen($dataPath . '/spj.cc',"w");
            fputs($fp, $spjContent);
            fclose($fp);
            putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
            $cmd = "/usr/bin/g++ -fno-asm -std=c++17 -O2 {$dataPath}/spj.cc -o {$dataPath}/spj -I" . Yii::getAlias('@app/libraries');
            exec($cmd . ' 2>&1', $compileInfo, $compileRes);
            if ($compileRes) {
                Yii::$app->session->setFlash('error', 'Compile failed: ' . implode("\n", $compileInfo));
            } else {
                Yii::$app->session->setFlash('success', 'Saved successfully.');
            }
            return $this->refresh();
        }

        return $this->render('spj', [
            'model' => $model,
            'spjContent' => $spjContent
        ]);
    }

    public function actionSubtask($id)
    {
        $this->layout = 'problem';
        $model = $this->findModel($id);

        $dataPath = Yii::$app->params['judgeProblemDataPath'] . $model->id;
        $subtaskContent = '';

        if (file_exists($dataPath . '/config')) {
            $subtaskContent = file_get_contents($dataPath . '/config');
        }
        if (Yii::$app->request->isPost) {
            $spjContent = Yii::$app->request->post('subtaskContent');
            if (!is_dir($dataPath)) {
                mkdir($dataPath);
            }
            $fp = fopen($dataPath . '/config',"w");
            fputs($fp, $spjContent);
            fclose($fp);
        }
        return $this->render('subtask', [
            'model' => $model,
            'subtaskContent' => $subtaskContent
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $problemTestDataPath = Yii::$app->params['judgeProblemDataPath'] . $model->id;
        if(file_exists($problemTestDataPath)){
            try {
                $this->makeDirEmpty($problemTestDataPath);
                rmdir($problemTestDataPath);
            } catch (\ErrorException $e) {
                Yii::$app->session->setFlash('error', 'Failed to delete: ' . $e->getMessage());
                return $this->redirect(['index']);
            }
        }
        $model->delete();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully deleted'));
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Problem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while ( false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }

    protected function makeDirEmpty($dir)
    {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->makeDirEmpty($fullpath);
                }
            }
        }
        closedir($dh);
    }

    protected function synchronizeProblemFromPolygon($id)
    {
        $id = intval($id);
        $polygonProblem = Yii::$app->db->createCommand('SELECT * FROM {{%polygon_problem}} WHERE id=:id', [':id' => $id])->queryOne();
        if (!empty($polygonProblem)) {
            $in = Yii::$app->db->createCommand('SELECT id FROM {{%problem}} WHERE polygon_problem_id=:id', [':id' => $id])->queryColumn();
            $problem = new Problem();
            if (!empty($in)) {
                $problem = Problem::findOne(['polygon_problem_id' => $id]);
                try {
                    $this->makeDirEmpty(Yii::$app->params['judgeProblemDataPath'] . $problem->id);
                } catch (\ErrorException $e) {
                    $e->getMessage();
                    return false;
                }
            }
            $problem->title = $polygonProblem['title'];
            $problem->description = $polygonProblem['description'];
            $problem->input = $polygonProblem['input'];
            $problem->output = $polygonProblem['output'];
            $problem->sample_input = $polygonProblem['sample_input'];
            $problem->sample_output = $polygonProblem['sample_output'];
            $problem->spj = $polygonProblem['spj'];
            $problem->hint = $polygonProblem['hint'];
            $problem->memory_limit = $polygonProblem['memory_limit'];
            $problem->time_limit = $polygonProblem['time_limit'];
            $problem->created_by = $polygonProblem['created_by'];
            $problem->solution = $polygonProblem['solution'];
            $problem->tags = $polygonProblem['tags'];
            $problem->status = Problem::STATUS_HIDDEN;
            $problem->polygon_problem_id = $id;
            $problem->save();

            $this->copyDir(Yii::$app->params['polygonProblemDataPath'] . $polygonProblem['id'], Yii::$app->params['judgeProblemDataPath'] . $problem->id);
            
            $dataPath = Yii::$app->params['judgeProblemDataPath'] . $problem->id;
            exec("chmod +x {$dataPath}/spj");
            return true;
        }
        return false;
    }
}
