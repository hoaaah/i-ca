<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TaCs */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ta Cs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ta-cs-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'cs_id',
            'id_group',
            'cs_n',
            'cs_no',
            'st_no',
            'st_tgl',
            'maksud_perjalanan',
            'beban_instansi',
            'kode_anggaran',
            'cs_tgl',
            'cs_pengaju_nip',
            'cs_pengaju_nama',
            'cs_pengaju_jabatan',
            'cs_setuju_1_tgl',
            'cs_setuju_1_nip',
            'cs_setuju_1_nama',
            'cs_setuju_1_jabatan',
            'cs_setuju_2_tgl',
            'cs_setuju_2_nip',
            'cs_setuju_2_nama',
            'cs_setuju_2_jabatan',
            'ttd_pengaju',
            'ttd_setuju_1',
            'ttd_setuju_2',
            'cs_anggaran',
            'cs_realisasi_lalu',
            'u_insert',
            'date_insert',
            'unit_id',
            'sub_unit_id',
            'id',
        ],
    ]) ?>

</div>
