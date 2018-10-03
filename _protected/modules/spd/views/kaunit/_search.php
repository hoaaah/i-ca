<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\spd\models\TaCsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ta-cs-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'cs_id')->textInput(
                ['class' => 'form-control input-sm pull-right','placeholder' => Yii::t('app', 'cs_id')])->label(false) ?>

    <?= $form->field($model, 'id_group')->textInput(
                ['class' => 'form-control input-sm pull-right','placeholder' => Yii::t('app', 'id_group')])->label(false) ?>

    <?= $form->field($model, 'cs_n')->textInput(
                ['class' => 'form-control input-sm pull-right','placeholder' => Yii::t('app', 'cs_n')])->label(false) ?>

    <?= $form->field($model, 'cs_no')->textInput(
                ['class' => 'form-control input-sm pull-right','placeholder' => Yii::t('app', 'cs_no')])->label(false) ?>

    <?= $form->field($model, 'st_no')->textInput(
                ['class' => 'form-control input-sm pull-right','placeholder' => Yii::t('app', 'st_no')])->label(false) ?>

    <?php // echo $form->field($model, 'st_tgl') ?>

    <?php // echo $form->field($model, 'maksud_perjalanan') ?>

    <?php // echo $form->field($model, 'beban_instansi') ?>

    <?php // echo $form->field($model, 'kode_anggaran') ?>

    <?php // echo $form->field($model, 'cs_tgl') ?>

    <?php // echo $form->field($model, 'cs_pengaju_nip') ?>

    <?php // echo $form->field($model, 'cs_pengaju_nama') ?>

    <?php // echo $form->field($model, 'cs_pengaju_jabatan') ?>

    <?php // echo $form->field($model, 'cs_setuju_1_tgl') ?>

    <?php // echo $form->field($model, 'cs_setuju_1_nip') ?>

    <?php // echo $form->field($model, 'cs_setuju_1_nama') ?>

    <?php // echo $form->field($model, 'cs_setuju_1_jabatan') ?>

    <?php // echo $form->field($model, 'cs_setuju_2_tgl') ?>

    <?php // echo $form->field($model, 'cs_setuju_2_nip') ?>

    <?php // echo $form->field($model, 'cs_setuju_2_nama') ?>

    <?php // echo $form->field($model, 'cs_setuju_2_jabatan') ?>

    <?php // echo $form->field($model, 'ttd_pengaju') ?>

    <?php // echo $form->field($model, 'ttd_setuju_1') ?>

    <?php // echo $form->field($model, 'ttd_setuju_2') ?>

    <?php // echo $form->field($model, 'cs_anggaran') ?>

    <?php // echo $form->field($model, 'cs_realisasi_lalu') ?>

    <?php // echo $form->field($model, 'u_insert') ?>

    <?php // echo $form->field($model, 'date_insert') ?>

    <?php // echo $form->field($model, 'unit_id') ?>

    <?php // echo $form->field($model, 'sub_unit_id') ?>

    <?php // echo $form->field($model, 'id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
