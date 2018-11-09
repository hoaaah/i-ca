<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\spd\models\TaCsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Anggaran UM';
$this->params['breadcrumbs'][] = 'Penatausahaan';
$this->params['breadcrumbs'][] = 'SPD';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="nav-tabs-custom">
<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li id="rek-1-tab" role="presentation" class="active"><a href="#rek-1-content" aria-controls="rek-1-content" role="tab" data-toggle="tab"><i class="fa fa-home"></i> Usulan</a></li>
    <li id="rek-2-tab" role="presentation" class="disabled"><a href="#" aria-controls="rek-2-content" role="tab" data-toggle="tab"><i class="fa fa-download"></i> Tambahkan Data Cost Sheet</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="rek-1-content">
        <div class="ta-cs-index">

            <p>
                <?php if(!$dbcsConnection) 
                    echo Html::a('Tambah Usulan Cost Sheet/SPD', ['create'], [
                        'class' => 'btn btn-xs btn-success',
                        'data-toggle'=>"modal",
                        'data-target'=>"#myModal",
                        'data-title'=>"Tambah Usulan Cost Sheet/SPD",
                    ]);
                ?>
            </p>
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
                        'label' => 'Status Persetujuan',
                        'value' => function($model) use($tahapan){
                            $currentProgress = \app\models\TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $tahapan]);
                            if($currentProgress) return 'Telah disetujui';
                        }
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{setuju} {view} {hapussetuju}',
                        'noWrap' => true,
                        'vAlign'=>'top',
                        'visibleButtons' => [
                            'setuju' => function($model) use($tahapan){
                                $currentProgress = \app\models\TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $tahapan]);
                                if($currentProgress) return false;
                                return true;
                            },
                            'hapussetuju' => function($model) use($tahapan){
                                $currentProgress = \app\models\TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $tahapan]);
                                if($currentProgress) return true;
                                return false;
                            },
                            'update' => function($model) use($tahapan){
                                $currentProgress = \app\models\TaCsProgress::findOne(['ta_cs_id' => $model->id, 'tahapan' => $tahapan]);
                                if($currentProgress) return false;
                                return true;
                            },
                        ],
                        'buttons' => [
                                'setuju' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-check"></span>', $url,
                                        [  
                                            'title' => Yii::t('yii', 'Reviu'),
                                            'data-toggle'=>"modal",
                                            'data-target'=>"#myModal",
                                            'data-title'=> "Reviu",                                 
                                            // 'data-confirm' => "Yakin menghapus ini?",
                                            // 'data-method' => 'POST',
                                            // 'data-pjax' => 1
                                        ]);
                                },
                                'update' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url,
                                        [  
                                            'title' => Yii::t('yii', 'ubah'),
                                            'data-toggle'=>"modal",
                                            'data-target'=>"#myModal",
                                            'data-title'=> "Ubah",                                 
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
                                'hapussetuju' => function ($url, $model) {
                                    return Html::a('<span class="fa fa-times"></span>', $url,
                                        [  
                                            'title' => Yii::t('yii', 'Hapus Reviu'),
                                            // 'data-toggle'=>"modal",
                                            // 'data-target'=>"#myModal",
                                            // 'data-title'=> "Setuju",                                 
                                            'data-confirm' => "Yakin menghapus persetujuan?",
                                            'data-method' => 'POST',
                                            'data-pjax' => 1
                                        ]);
                                },                        
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="rek-2-content">...</div>
</div> 
</div>
<?php Modal::begin([
    'id' => 'myModal',
    'header' => '<h4 class="modal-title">Lihat lebih...</h4>',
    'options' => [
        'tabindex' => false // important for Select2 to work properly
    ], 
    'size' => 'modal-lg',
]);
 
echo '...';
 
Modal::end();
$this->registerJs(<<<JS
    $('#myModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var modal = $(this)
        var title = button.data('title') 
        var href = button.attr('href') 
        modal.find('.modal-title').html(title)
        modal.find('.modal-body').html('<i class=\"fa fa-spinner fa-spin\"></i>')
        $.post(href)
            .done(function( data ) {
                modal.find('.modal-body').html(data)
            });
        })
JS
);
$csUrl = \yii\helpers\Url::to(['datacs']);
// $this->registerJs(<<<JS
//     $("#rek-2-tab").on("click", function(e){
//         e.preventDefault()
//         // var href = $(this).attr('href');
//         var href = '$csUrl';
//         $('#rek-1-tab').removeClass('active');
//         $('#rek-2-tab').attr('class', 'active');
//         $('#rek-1-content').removeClass('active in');
//         $('#rek-2-content').addClass('active in');
//         $('#rek-2-content').html('<i class=\"fa fa-circle-o-notch fa-spin\"></i>');
//         $.post(href).done(function(data){
//             $('#rek-2-content').html(data);
//        });
//     })
// JS 
// );
?>