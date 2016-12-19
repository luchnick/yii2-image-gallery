<?php
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Image Gallery';
?>
<div class="site-index">
    <div class="body-content">
        <div class="jumbotron">
            <h2>Image Gallery</h2>
        </div>
        <?php if (!Yii::$app->user->isGuest): ?>
        <h3><?= Yii::t('app', 'Hello, {user}!', ['user' => Yii::$app->user->identity->username]) ?></h3>
            <div class="row file-uploaded">
                <?php $form = ActiveForm::begin([
                'options'=>[
                    'encytype'=>'multipart/form-data'
                ],
                ]); ?>
                <?= $form->field($model, 'imageFiles[]')->widget(FileInput::classname(), [
                    'options' => [
                        'accept' => 'image/*',
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        'allowedFileExtensions'=>['jpg', 'png'],
                        'previewFileType' => 'any',
                        'uploadUrl' => Url::to(['upload-image', Yii::$app->request->queryParams]),
                        'maxFileCount' => 10,
                        'maxFileSize' => 5000,
                        'minImageHeight' => 250,
                        'minImageWidth' => 500
                    ],
                ])->label(false);
                ?>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="row table-pictures">
                <?= $this->render('content/table', compact('pictures', 'pages')); ?>
            </div>
        <?php else: ?>
            <div class="row text-center">
                <h2>Please login or sign up to add images</h2>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php Yii::$app->view->registerJs(<<<JS
$('.file-uploaded').on('fileuploaded', function(event, data, previewId, index) {
    $('.table-pictures').html(data.response);
});
JS
    , \yii\web\View::POS_READY); ?>

<?php Yii::$app->view->registerJs(<<<JS
function delete_image(id) {
    $.ajax({
        url: '/site/delete-image'+location.pathname,
        type: 'POST',
        data: 'id='+id,
        success: function (data) {
            $('.table-pictures').html(data);
        }
    });
}

function rotate_image(id) {
    $.ajax({
        url: '/site/rotate-image',
        type: 'POST',
        data: 'id='+id,
        success: function (data) {
            $('#rotate-image-'+id).parent().prev('td').html(data);
        }
    });
}
JS
, \yii\web\View::POS_END); ?>