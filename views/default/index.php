<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;

/**
 * @var \shopium\mod\yml\models\SettingsForm $model
 */
$form = ActiveForm::begin([]);
?>
    <div class="card">
        <div class="card-header">
            <h5><?= $this->context->pageName ?></h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'name')->hint($model::t('HINT_NAME')); ?>
            <?= $form->field($model, 'company')->hint($model::t('HINT_COMPANY')); ?>
            <?= $form->field($model, 'url')->hint($model::t('HINT_URL')); ?>
        </div>
        <div class="card-footer text-center">
            <?= $model->submitButton(); ?>
            <?= Html::a(Yii::t('yml/default', 'VIEW_FILE'), ['/yandex-market.xml'], ['class' => 'btn btn-outline-primary', 'target' => '_blank']); ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>