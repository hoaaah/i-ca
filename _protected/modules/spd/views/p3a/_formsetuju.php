<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\TaCs */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ta-cs-form">
    <div id="message"></div>

    <?php $form = ActiveForm::begin(['id' => $model->formName()]); ?>
    
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($taCs, 'maksud_perjalanan')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4"> 
            <?= $form->field($taCs, 'cs_id')->textInput(['disabled' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($taCs, 'id_group')->textInput(['disabled' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($taCs, 'beban_instansi')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>
    </div>

    <?php if(!Yii::$app->user->identity->unit_id): ?>

    <div class="row">
        <div class="col-md-6">
            <?php echo $form->field($taCs, 'unit_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(\app\models\RefUnit::find()->orderBy('id')->asArray()->all(), 'id', 'nama_unit'),
                'pluginOptions' => ['allowClear' => true, 'disabled' => true],
            ]); ?>
        </div>
        <div class="col-md-6">
            <?php echo $form->field($taCs, 'sub_unit_id')->widget(DepDrop::class, [
                'data'=> ArrayHelper::map(\app\models\RefSubUnit::find()->orderBy('sub_unit_id')->asArray()->all(), 'sub_unit_id', 'nama_sub_unit'),
                'options' => ['placeholder' => 'Select Sub Unit'],
                'type' => DepDrop::TYPE_SELECT2,
                'select2Options'=>['pluginOptions'=>['allowClear'=>true, 'disabled' => true]],
                'pluginOptions'=>[
                    'depends'=>['tacs-unit_id'],
                    'url' => Url::to(['/subunit']),
                    'loadingText' => 'Loading Sub Unit ...',
                ]
            ]);
            ?>
        </div>
    </div>

    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'time')->textInput(['maxlength' => true, 'readonly' => true]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $script = <<<JS
$('form#{$model->formName()}').on('beforeSubmit',function(e)
{
    var \$form = $(this);
    $.post(
        \$form.attr("action"), //serialize Yii2 form 
        \$form.serialize()
    )
        .done(function(result){
            if(result == 1)
            {
                $("#myModal").modal('hide'); //hide modal after submit
                //$(\$form).trigger("reset"); //reset form to reuse it to input
                $.pjax.reload({container:'#ta-cs-pjax'});
            }else
            {
                $("#message").html(result);
            }
        }).fail(function(){
            console.log("server error");
        });
    return false;
});

JS;
$this->registerJs($script);
?>