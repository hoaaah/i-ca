<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\detail\DetailView;
use app\models\TaCsProgress;

/**
 * find persetujuan in each phase
 */

 $tahapTim = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_TIM]);
 $tahapP3a = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_P3A]);
 $tahapKeu = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_KEU]);
 $tahapPpk = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_PPK]);
 $tahapKpa = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_KPA]);
 $tahapSpd = TaCsProgress::findOne(['ta_cs_id'=> $model->id, 'tahapan' => TaCsProgress::TAHAP_SPD]);
 

/* @var $this yii\web\View */

// $this->title = Yii::t('app', Yii::$app->name);
$this->title = "Dashboard";

?>
<div class="site-index">
    <div class="row">
        <div class="col-md-12">
            <?= DetailView::widget([
                'model'=>$model,
                'condensed'=>true,
                'bootstrap' => true,
                'hover'=>true,
                'enableEditMode' =>false,
                'mode'=>DetailView::MODE_VIEW,
                'panel'=>[
                    'heading'=>'CS ID # ' . $model->cs_id,
                    'type'=>DetailView::TYPE_INFO,
                ],
                'attributes'=>[
                    'cs_no',
                    'maksud_perjalanan',
                    'st_tgl:date'
                ]
            ]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">Monitoring Dokumen Usulan Belanja Perjalanan Dinas</div>
                <div class="panel-body">        
                    <div style="display:inline-block;width:100%;overflow-y:auto;">
                        <ul class="timeline timeline-horizontal">
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapTim ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapTim ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">Bidang/Tim</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapTim['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis litro abertis. Consetis faiz elementum girarzis, nisi eros gostis.</p> -->
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapP3a ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapP3a ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">Program dan Pelaporan</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapP3a['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis faiz elementum girarzis, nisi eros gostis.</p> -->
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapKeu ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapKeu ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">Keuangan</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapKeu['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis litro abertis. Consetis adipisci. Mé faiz elementum girarzis, nisi eros gostis.</p> -->
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapPpk ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapPpk ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">PPK / Ka TU</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapPpk['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis litro abertis. Consetis adipiscings elitis. Pra lá , depois divoltis porris, paradis. Paisis, filhis, espiritis santis.</p> -->
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapKpa ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapKpa ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">KPA / Ka Unit</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapKpa['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis litro abertis. Consetis adipiscings elitis. Pra lá , depois divoltis porris, paradis. Paisis, filhis, espiritis santis.</p> -->
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-badge <?= $tahapSpd ? "success" : "danger" ?>"><i class="glyphicon glyphicon-<?= $tahapSpd ? "check" : "remove" ?>"></i></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h4 class="timeline-title">Persetujuan SPD</h4>
                                        <p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?= date('Y-m-d h:i', strtotime($tahapSpd['time'])) ?? "No Data" ?></small></p>
                                    </div>
                                    <div class="timeline-body">
                                        <!-- <p>Mussum ipsum cacilds, vidis litro abertis. Consetis adipiscings elitis. Pra lá , depois divoltis porris, paradis. Paisis, filhis, espiritis santis.</p> -->
                                    </div>
                                </div>
                            </li>
                            
                        </ul>
                    </div>
                </div>
            </div>                    
        </div>
    </div>    
</div>

<?php
$this->registerCss(<<<CSS
@/* Timeline */
.timeline,
.timeline-horizontal {
  list-style: none;
  padding: 10px;
  position: relative;
}
.timeline:before {
  top: 40px;
  bottom: 0;
  position: absolute;
  content: " ";
  width: 3px;
  background-color: #eeeeee;
  left: 50%;
  margin-left: -1.5px;
}
.timeline .timeline-item {
  margin-bottom: 20px;
  position: relative;
}
.timeline .timeline-item:before,
.timeline .timeline-item:after {
  content: "";
  display: table;
}
.timeline .timeline-item:after {
  clear: both;
}
.timeline .timeline-item .timeline-badge {
  color: #fff;
  width: 54px;
  height: 54px;
  line-height: 52px;
  font-size: 22px;
  text-align: center;
  position: absolute;
  top: 18px;
  left: 50%;
  margin-left: -25px;
  background-color: #7c7c7c;
  border: 3px solid #ffffff;
  z-index: 100;
  border-top-right-radius: 50%;
  border-top-left-radius: 50%;
  border-bottom-right-radius: 50%;
  border-bottom-left-radius: 50%;
}
.timeline .timeline-item .timeline-badge i,
.timeline .timeline-item .timeline-badge .fa,
.timeline .timeline-item .timeline-badge .glyphicon {
  top: 2px;
  left: 0px;
}
.timeline .timeline-item .timeline-badge.primary {
  background-color: #1f9eba;
}
.timeline .timeline-item .timeline-badge.info {
  background-color: #5bc0de;
}
.timeline .timeline-item .timeline-badge.success {
  background-color: #59ba1f;
}
.timeline .timeline-item .timeline-badge.warning {
  background-color: #d1bd10;
}
.timeline .timeline-item .timeline-badge.danger {
  background-color: #ba1f1f;
}
.timeline .timeline-item .timeline-panel {
  position: relative;
  width: 46%;
  float: left;
  right: 16px;
  border: 1px solid #c0c0c0;
  background: #ffffff;
  border-radius: 2px;
  padding: 20px;
  -webkit-box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
}
.timeline .timeline-item .timeline-panel:before {
  position: absolute;
  top: 26px;
  right: -16px;
  display: inline-block;
  border-top: 16px solid transparent;
  border-left: 16px solid #c0c0c0;
  border-right: 0 solid #c0c0c0;
  border-bottom: 16px solid transparent;
  content: " ";
}
.timeline .timeline-item .timeline-panel .timeline-title {
  margin-top: 0;
  color: inherit;
}
.timeline .timeline-item .timeline-panel .timeline-body > p,
.timeline .timeline-item .timeline-panel .timeline-body > ul {
  margin-bottom: 0;
}
.timeline .timeline-item .timeline-panel .timeline-body > p + p {
  margin-top: 5px;
}
.timeline .timeline-item:last-child:nth-child(even) {
  float: right;
}
.timeline .timeline-item:nth-child(even) .timeline-panel {
  float: right;
  left: 16px;
}
.timeline .timeline-item:nth-child(even) .timeline-panel:before {
  border-left-width: 0;
  border-right-width: 14px;
  left: -14px;
  right: auto;
}
.timeline-horizontal {
  list-style: none;
  position: relative;
  padding: 20px 0px 20px 0px;
  display: inline-block;
}
.timeline-horizontal:before {
  height: 3px;
  top: auto;
  bottom: 26px;
  left: 56px;
  right: 0;
  width: 100%;
  margin-bottom: 20px;
}
.timeline-horizontal .timeline-item {
  display: table-cell;
  height: 280px;
  width: 20%;
  /* min-width: 252px; */
  max-width: 170px;
  float: none !important;
  padding-left: 0px;
  padding-right: 20px;
  margin: 0 auto;
  vertical-align: bottom;
}
.timeline-horizontal .timeline-item .timeline-panel {
  top: auto;
  bottom: 64px;
  display: inline-block;
  float: none !important;
  left: 0 !important;
  right: 0 !important;
  width: 100%;
  margin-bottom: 20px;
}
.timeline-horizontal .timeline-item .timeline-panel:before {
  top: auto;
  bottom: -16px;
  left: 28px !important;
  right: auto;
  border-right: 16px solid transparent !important;
  border-top: 16px solid #c0c0c0 !important;
  border-bottom: 0 solid #c0c0c0 !important;
  border-left: 16px solid transparent !important;
}
.timeline-horizontal .timeline-item:before,
.timeline-horizontal .timeline-item:after {
  display: none;
}
.timeline-horizontal .timeline-item .timeline-badge {
  top: auto;
  bottom: 0px;
  left: 43px;
}
CSS
); 
?>