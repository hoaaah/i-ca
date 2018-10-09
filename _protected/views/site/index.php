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
        'bordered' => true,
        'striped' => true,
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
            'datetimeFormat' => 'php:d/m/y H:i'
        ],
        'rowOptions' => function($model, $key, $index, $grid) {
            // if ($model['cs_id'] % 4){
            //     return ['class' => 'danger'];
            // }
            // if ($model['cs_id'] % 3){
            //     return ['class' => 'warning'];
            // }
            if ($model['cs_id'] % 2){
                return ['class' => 'warning'];
            }
            return ['class' => 'info'];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'cs_id',
                'format' => 'html',
                'value' => function($model)
                {
                    return Html::a($model['cs_id'], ['detail', 'id' => $model['id']]);
                }
            ],
            [
                'label' => 'No CS',
                'value' => function($model, $key, $index, $grid){
                    return $model['cs_no']." (".$model['cs_tgl'].")";
                }
            ],
            // 'maksud_perjalanan',
            [
                'attribute' => 'maksud_perjalanan',
                'format' => 'html',
                'value' => function($model)
                {
                    return Html::a($model['maksud_perjalanan'], ['detail', 'id' => $model['id']]);
                }
            ],
            [
                'label' => 'Tim',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'tim'
            ],
            [
                'label' => 'Proglap',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'proglap'
            ],
            [
                'label' => 'Keuangan',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'keu'
            ],
            [
                'label' => 'PPK',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'ppk'
            ],
            [
                'label' => 'KPA',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'kpa'
            ],
            [
                'label' => 'Proses SPD',
                'noWrap' => true,
                'format' => 'datetime',
                'value' => 'spd'
            ],
        ],
    ]); 
    ?>
</div>

<?php
// $this->registerCss(<<<CSS
//     .table-striped > tbody > tr:nth-child(2n+1) > td, .table-striped > tbody > tr:nth-child(2n+1) > th {
//     background-color: lightgray;
//     }
// CSS
// ); 
?>