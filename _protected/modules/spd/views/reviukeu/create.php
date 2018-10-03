<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\TaCs */

$this->title = 'Create Ta Cs';
$this->params['breadcrumbs'][] = ['label' => 'Ta Cs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ta-cs-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
