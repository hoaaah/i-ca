<?php

namespace app\controllers;

use Yii;
use app\models\AppBos;
use app\models\AppBosSearch;
use app\models\TruncateSchema;
use app\models\Version;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UpdateController implements the CRUD actions for AppBos model.
 */
class UpdateController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'rollback' => ['POST'],
                    'update' => ['POST'],
                ],
            ],
        ];
    }

    private $menu = 801;

    public function actionIndex()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $searchModel = new AppBosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'Tahun' => $Tahun,
        ]);
    }

    public function actionDb()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }

        $versionClass = new Version();
        $currentVersionClass = clone $versionClass;

        $latestPerformed = AppBos::find()->select('MAX(performed_at) AS performed_at')->one();
        $dbVersion = AppBos::findOne(['performed_at' => $latestPerformed->performed_at]);

        return $this->render('db', [
            'dbVersion' => $dbVersion,
            'Tahun' => $Tahun,
            'versionClass' => $versionClass,
            'currentVersionClass' => $currentVersionClass
        ]);
    }

    public function actionDelete()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $tahun = Yii::$app->session->get('tahun') ?? date('Y');

        $model = new TruncateSchema();

        if ($model->load(Yii::$app->request->post())) {
            // \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
            // return $model->truncateData();
            IF($model->truncateData()){
                // return $model->truncateData();
                $return = "";
                $return .= $this->setErrorMessage([0 => ['Penghapusan Data Berhasil']]);
                return $return;
            }ELSE{
                $return = "";
                $return .= $this->setErrorMessage($model->errors);
                return $return;
            }
        }
        //  else {
        //     return $this->renderAjax('_form', [
        //         'model' => $model,
        //     ]);
        // }

        return $this->render('delete', [
            'tahun' => $tahun,
            'model' => $model
        ]);
    }

    protected function setErrorMessage($errors){
        $return = '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
        foreach($errors as $data){
            $return .= $data['0'].'<br>';
        }
        $return .= '</div>';
        return $return;
    }

    public function actionRollback($version)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        $transaction = Yii::$app->db->beginTransaction();
        switch ($version) {
            case 1:
                $alertColor = "info";
                $result = "This Version didn't Support RollBack Update.";
                break; 
        
            default:
                $alertColor = "info";
                $result = "Update Success";
                break;
        }

        return "
        <div class=\"alert alert-$alertColor alert-dismissible\">
        <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>
        $result
        </div>      
        ";
    }

    public function actionUpdate($version)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $Tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $Tahun = DATE('Y');
        }
        // begin transaction
        // Transaction will be applied for every transaction in each update version
        $transaction = Yii::$app->db->beginTransaction();

        switch ($version) {
            case 1:
                try {
                    $executeInsert = Yii::$app->db->createCommand("
                        INSERT INTO app_bos VALUES ('ditta', 'v1.0.0', 1, NOW())
                    ")->execute();
                    $transaction->commit();
                    $alertColor = "info";
                    $result = "Update Success";
                } catch (\Exception $e) {
                    $alertColor = "warning";
                    $result = "Update Failed: ".$e;
                    $transaction->rollBack();
                    // throw $e;
                } catch (\Throwable $e) {
                    $alertColor = "warning";
                    $result = "Update Failed: ".$e;
                    $transaction->rollBack();
                    // throw $e;
                }
                break;
       
            default:
                $alertColor = "info";
                $result = "Update Success";
                break;
        }

        return "
        <div class=\"alert alert-$alertColor alert-dismissible\">
        <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>
        $result
        </div>      
        ";
    }

 
    protected function findModel($id)
    {
        if (($model = AppBos::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    protected function cekakses(){

        IF(Yii::$app->user->identity){
            $akses = \app\models\RefUserMenu::find()->where(['kd_user' => Yii::$app->user->identity->kd_user, 'menu' => $this->menu])->one();
            IF($akses){
                return true;
            }else{
                return false;
            }
        }ELSE{
            return false;
        }
    }  

}
