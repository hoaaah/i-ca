<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\spd\models\TaCsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usulan Cost Sheet/SPD';
$this->params['breadcrumbs'][] = 'Penatausahaan';
$this->params['breadcrumbs'][] = 'Cost Sheet/SPD';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="ta-cs-index">

    <?= GridView::widget([
        'id' => 'ta-cs',    
        'dataProvider' => $dataProvider,
        'export' => false, 
        'responsive'=>true,
        'hover'=>true,     
        'resizableColumns'=>true,
        'panel'=>['type'=>'primary', 'heading'=>$this->title],
        'responsiveWrap' => false,        
        'toolbar' => [
            [
                // 'content' => $this->render('_search', ['model' => $searchModel, 'tahun' => $tahun]),
            ],
        ],       
        'pager' => [
            'firstPageLabel' => 'Awal',
            'lastPageLabel'  => 'Akhir'
        ],
        'pjax'=>true,
        'pjaxSettings'=>[
            'options' => ['id' => 'ta-cs-pjax', 'timeout' => 5000],
        ],        
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'cs_id',
            'cs_no',
            'cs_tgl:date',
            'maksud_perjalanan',
            'beban_instansi',

            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{ambil}',
                'noWrap' => true,
                'vAlign'=>'top',
                'buttons' => [
                        'ambil' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url,
                                [  
                                    'title' => Yii::t('yii', 'ubah'),
                                    'data-toggle'=>"modal",
                                    'data-target'=>"#myModal",
                                    'data-title'=> "Ambil Data Cost Sheet ".$model->cs_id,                                 
                                    // 'data-confirm' => "Yakin menghapus ini?",
                                    // 'data-method' => 'POST',
                                    // 'data-pjax' => 1
                                ]);
                        },
                        'view' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url,
                                [  
                                    'title' => Yii::t('yii', 'lihat'),
                                    'data-toggle'=>"modal",
                                    'data-target'=>"#myModal",
                                    'data-title'=> "Lihat",
                                ]);
                        },                        
                ]
            ],
        ],
    ]); ?>
</div>
  
<?php 
$csUrl = \yii\helpers\Url::to(['datacs']);
$this->registerJs(<<<JS
    $("#rek-2-tab").on("click", function(e){
        e.preventDefault()
        // var href = $(this).attr('href');
        var href = '$csUrl';
        $('#rek-1-tab').removeClass('active');
        $('#rek-2-tab').attr('class', 'active');
        $('#rek-1-content').removeClass('active in');
        $('#rek-2-content').addClass('active in');
        $('#rek-2-content').html('<i class=\"fa fa-circle-o-notch fa-spin\"></i>');
        $.post(href).done(function(data){
            $('#rek-2-content').html(data);
        });
    })
JS
);
?>