<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TaCs */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ta-cs-form">
    <div id="message"></div>

    <?php $form = ActiveForm::begin(['id' => $model->formName()]); ?>

    <?= $form->field($model, 'cs_id')->textInput() ?>

    <?= $form->field($model, 'id_group')->textInput() ?>

    <?= $form->field($model, 'maksud_perjalanan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'beban_instansi')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unit_id')->textInput() ?>

    <?= $form->field($model, 'sub_unit_id')->textInput() ?>

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