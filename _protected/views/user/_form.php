<?php
use app\rbac\models\AuthItem;
use kartik\password\PasswordInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="user-form">

    <?php $form = ActiveForm::begin(['id' => 'form-user']); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($user, 'username')->textInput(
                ['placeholder' => Yii::t('app', 'Create username'), 'autofocus' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($user, 'email')->input('email', ['placeholder' => Yii::t('app', 'Enter e-mail')]) ?>
        </div>
        <div class="col-md-4">

            <?php if ($user->scenario === 'create'): ?>

                <?= $form->field($user, 'password')->widget(PasswordInput::classname(), 
                    ['options' => ['placeholder' => Yii::t('app', 'Create password')]]) ?>

            <?php else: ?>

                <?= $form->field($user, 'password')->widget(PasswordInput::classname(),
                        ['options' => ['placeholder' => Yii::t('app', 'Biarkan kosong jika tetap')]]) ?> 

            <?php endif ?>
        </div>
    </div>
    <div class="row">

        <div class="col-md-4">
            <?php
                echo $form->field($user, 'unit_id')->widget(Select2::classname(), [
                    'data' => $user->unitArray,
                    'options' => ['placeholder' => $user->scenario === 'create' ? 'Pilih Unit ...' : 'Biarkan kosong jika tetap'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-4">
            <?php               
                echo $form->field($user, 'sub_unit_id')->widget(Select2::classname(), [
                    'data' => $user->subUnitArray,
                    'options' => ['placeholder' => 'Sub Unit ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
            ?>
        </div>

    </div>

    <div class="row">
        <div class="col-md-6">

            <?= $form->field($user, 'status')->dropDownList($user->statusList) ?>

            <?php foreach (AuthItem::getRoles() as $item_name): ?>
                <?php $roles[$item_name->name] = $item_name->name ?>
            <?php endforeach ?>
            <?= $form->field($user, 'item_name')->dropDownList($roles) ?>

        </div>
    </div>

    <div class="form-group">     
        <?= Html::submitButton($user->isNewRecord ? Yii::t('app', 'Create') 
            : Yii::t('app', 'Update'), ['class' => $user->isNewRecord 
            ? 'btn btn-success' : 'btn btn-primary']) ?>

        <?= Html::a(Yii::t('app', 'Cancel'), ['user/index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
 
</div>
