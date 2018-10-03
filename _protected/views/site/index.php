<?php
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */

// $this->title = Yii::t('app', Yii::$app->name);
$this->title = "Dashboard";

?>
<div class="site-index">
    <?php 
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'export' => false, 
        'responsive'=>true,
        'hover'=>true,     
        'resizableColumns'=>true,
        'panel'=>['type'=>'primary', 'heading'=>$this->title],
        'responsiveWrap' => false,        
        'toolbar' => [
            [
                // 'content' => $this->render('_search', ['model' => $searchModel]),
            ],
        ],       
        'pager' => [
            'firstPageLabel' => 'Awal',
            'lastPageLabel'  => 'Akhir'
        ],
        'pjax'=>true,
        'pjaxSettings'=>[
            'options' => ['id' => 'sphawal-pjax', 'timeout' => 5000],
        ],  
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
            'datetimeFormat' => 'php:m/d/Y H:i:s'
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'cs_id',
            'cs_no',
            'cs_tgl:date',
            'maksud_perjalanan',
            'beban_instansi',
            [
                'label' => 'Tim',
                'format' => 'datetime',
                'value' => 'tim'
            ],
            [
                'label' => 'Proglap',
                'format' => 'datetime',
                'value' => 'proglap'
            ],
            [
                'label' => 'Keuangan',
                'format' => 'datetime',
                'value' => 'keu'
            ],
            [
                'label' => 'PPK',
                'format' => 'datetime',
                'value' => 'PPK'
            ],
            [
                'label' => 'KPA',
                'format' => 'datetime',
                'value' => 'kpa'
            ],
            [
                'label' => 'Proses SPD',
                'format' => 'datetime',
                'value' => 'spd'
            ],
        ],
    ]); 
    ?>
</div>

