<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TaCs */

$this->title = 'Update Ta Cs: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Ta Cs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ta-cs-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
