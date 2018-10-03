<?php

namespace app\modules\spd\controllers;

use Yii;
use app\models\TaCs;
use app\models\TaCsProgress;
use app\models\TCs;
use app\modules\spd\models\TaCsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;

/**
 * UsulanController implements the CRUD actions for TaCs model.
 */
class ReviukeuController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    private $menu = 503;
    private $tahapan = TaCsProgress::TAHAP_KEU;

    /**
     * Lists all TaCs models.
     * @return mixed
     * if user have unit/sub_unit id then we will filter $dataProvider with unit_id and sub_unit_id
     */
    public function actionIndex()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $dbcsConnection = true;
        
        try {
            $aGroup = \app\models\AGroup::find()->all();
        } catch (\Exception $e) {
            $dbcsConnection = false;
            // die($e->getMessage());
        }

        $searchModel = new TaCsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('date_insert DESC');
        
        if(Yii::$app->user->identity->unit_id) $dataProvider->query->andWhere(['unit_id' => Yii::$app->user->identity->unit_id]);
        if(Yii::$app->user->identity->sub_unit_id) $dataProvider->query->andWhere(['sub_unit_id' => Yii::$app->user->identity->sub_unit_id]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tahun' => $tahun,
            'dbcsConnection' => $dbcsConnection,
            'tahapan' => $this->tahapan,
        ]);
    }

    /**
     * renderAjax from index
     * temporarily we will only show data with like condition, because dataCs didn't contain consistent id for unit/sub_unit
     */
    public function actionDatacs()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $dbcsConnection = true;
        
        try {
            $aGroup = \app\models\AGroup::find()->all();
        } catch (\Exception $e) {
            $dbcsConnection = false;
            return $this->renderAjax('connectionerror', [
                'message' =>  $e->getMessage()
            ]);
        }
        
        $unit = Yii::$app->params['unit'];

        $dataProvider = new ActiveDataProvider([
            'query' => TCs::find()->where("ID_GROUP IN (SELECT ID_GROUP FROM A_GROUP WHERE NAMA_GROUP LIKE '%$unit%' ) AND DATE_INSERT  LIKE '$tahun%'"),
            'sort'=> ['defaultOrder' => ['date_insert'=>SORT_DESC]],
            'pagination' => ['pageSize' => 50]
        ]);

        return $this->renderAjax('_datacs', [
            'dataProvider' => $dataProvider,
            'tahun' => $tahun,
            'dbcsConnection' => $dbcsConnection,
        ]);

    }

    /**
     * Displays a single TaCs model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }   
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionAmbil($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }
        $dataCs = null;
        try {
            $dataCs = TCs::findOne(['cs_id' => $id]);
        } catch (\Exception $e) {
            return $this->renderAjax('connectionerror', [
                'message' =>  $e->getMessage()
            ]);
        }
        
        $model = TaCs::findOne(['cs_id' => $id]);
        if(!$model)
        {
            $model = new TaCs([
                // 'attributes' => $dataCs->getAttributes()
            ]);
        }
        $model->attributes = $dataCs->getAttributes();

        if(Yii::$app->user->identity->unit_id) $model->unit_id = Yii::$app->user->identity->unit_id;
        if(Yii::$app->user->identity->sub_unit_id) $model->sub_unit_id = Yii::$app->user->identity->sub_unit_id;

        $transaction = Yii::$app->db->beginTransaction();

        if ($model->load(Yii::$app->request->post())) {
            IF($model->save()){
                $progress = TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $this->tahapan]);
                if(!$progress){
                    $progress = new TaCsProgress();
                    $progress->ta_cs_id = $model->id;
                }
                $progress->cs_id = $model->cs_id;
                $progress->tahapan = $this->tahapan;
                $progress->time = new \yii\db\Expression('NOW()');

                try{
                    $progress->save();
                    $transaction->commit();
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    $return = "";
                    if($progress->errors) $return .= $this->setErrorMessage($progress->errors);
                    return $return;
                }

                return 1;
            }ELSE{
                $return = "";
                if($model->errors) $return .= $this->setErrorMessage($model->errors);
                return $return;
            }
        } else {
            return $this->renderAjax('_ambil', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Create Setuju to progress.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionSetuju($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }     

        $taCs = TaCs::findOne(['id' => $id]);

        $dbcsConnection = true;
        $dataCs = null;
        try {
            $dataCs = TCs::findOne(['cs_id' => $taCs->cs_id]);
        } catch (\Exception $e) {
            $dbcsConnection = false;

        }   

        $progressTahapanSebelumnya = TaCsProgress::findOne(['ta_cs_id' => $id, 'tahapan' => ($this->tahapan -1)]);

        if(!$progressTahapanSebelumnya) return "Tahapan sebelumnya belum disetujui/direviu, silakan tunggu reviu dokumen.";

        if($dbcsConnection == true) $taCs->attributes = $dataCs->getAttributes();

        $model = TaCsProgress::findOne(['ta_cs_id' => $id, 'tahapan' => $this->tahapan]);
        if(!$model){
            $model = new TaCsProgress();
            $model->ta_cs_id = $taCs->id;
        }
        $model->cs_id = $taCs->cs_id;
        $model->tahapan = $this->tahapan;
        $model->time = DATE('Y-m-d h:i:s');

        if ($model->load(Yii::$app->request->post())) {
            $model->time = new \yii\db\Expression('NOW()');
            IF($model->save()){
                $taCs->save();
                return 1;
            }ELSE{
                $return = "";
                if($model->errors) $return .= $this->setErrorMessage($model->errors);
                return $return;
            }
        } else {
            return $this->renderAjax('_formsetuju', [
                'model' => $model,
                'taCs' => $taCs,
            ]);
        }
    }        

    /**
     * Creates a new TaCs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $model = new TaCs();

        if ($model->load(Yii::$app->request->post())) {
            IF($model->save()){
                return 1;
            }ELSE{
                $return = "";
                if($model->errors) $return .= $this->setErrorMessage($model->errors);
                return $return;
            }
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TaCs model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            IF($model->save()){
                return 1;
            }ELSE{
                $return = "";
                if($model->errors) $return .= $this->setErrorMessage($model->errors);
                return $return;
            }
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TaCs model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * Before delete we check progress first, if already progress with tahapan bigger than current $this->tahapan than we will not delete it
     */
    public function actionDelete($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $model = $this->findModel($id);
        $progress = TaCsProgress::find()->select(['MAX(tahapan) AS tahapan'])->where(['ta_cs_id' => $model->id])->one();
        if($progress['tahapan'] <= $this->tahapan)
        {
            $model->delete();
        }else{
            Yii::$app->getSession()->setFlash('warning',  'Sudah diproses di tahap selanjutnya, tidak dapat dihapus.');
        }
        

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionHapussetuju($id)
    {
        IF($this->cekakses() !== true){
            Yii::$app->getSession()->setFlash('warning',  'Anda tidak memiliki hak akses');
            return $this->redirect(Yii::$app->request->referrer);
        }    
        IF(Yii::$app->session->get('tahun'))
        {
            $tahun = Yii::$app->session->get('tahun');
        }ELSE{
            $tahun = DATE('Y');
        }

        $model = $this->findModel($id);
        $progress = TaCsProgress::find()->select(['MAX(tahapan) AS tahapan'])->where(['ta_cs_id' => $model->id])->one();
        if($progress['tahapan'] <= $this->tahapan)
        {
            $progressToDelete = TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $this->tahapan]);
            $progressToDelete->delete();
        }else{
            Yii::$app->getSession()->setFlash('warning',  'Sudah diproses di tahap selanjutnya, tidak dapat dihapus.');
        }
        

        return $this->redirect(Yii::$app->request->referrer);
    }

    protected function setErrorMessage($errors){
        $return = '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
        foreach($errors as $data){
            $return .= '<ol>'.$data['0'].'</ol>';
        }
        $return .= '</div>';
        return $return;
    }    

    /**
     * Finds the TaCs model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TaCs the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TaCs::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    protected function cekakses(){

        // IF(Yii::$app->user->identity){
        //     $akses = \app\models\RefUserMenu::find()->where(['kd_user' => Yii::$app->user->identity->kd_user, 'menu' => $this->menu])->one();
        //     IF($akses){
        //         return true;
        //     }else{
        //         return false;
        //     }
        // }ELSE{
        //     return false;
        // }

        return true;
    }  

}
