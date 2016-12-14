<?php
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;

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
                <?= FileInput::widget([
                    'options' => [
                        'accept' => 'image/*',
                        'multiple' => true
                    ],
                    'name' => 'imageFile',
                    'pluginOptions' => [
                        'allowedFileExtensions'=>['jpg', 'png'],
                        'previewFileType' => 'any',
                        'uploadUrl' => Url::to(['/site/upload']),
                        'maxFileCount' => 10,
                        'maxFileSize' => 5000
                    ],
                ]);
                ?>
            </div>

            <div class="row">
                <table class="table table-bordered upload-success">
                <?php foreach ($pictures as $picture): ?>
                    <tr>
                        <td><?= Html::img($picture->getImage()) ?></td>
                        <td>
                            <button id="rotate-image-<?= $picture->id ?>" type="button" class="btn btn-primary" onClick="rotate_image(<?= $picture->id ?>)">Rotate</button>
                        </td>
                        <td>
                            <button id="delete-image-<?= $picture->id ?>" type="button" class="btn btn-danger" onClick="delete_image(<?= $picture->id ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </table>
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
    $('.upload-success').prepend('<tr><td><img src="' + data.response.photo +'"></td>' +
    '<td><button id="rotate-image-' + data.response.id + '" type="button" class="btn btn-primary" onClick="rotate_image(' + data.response.id + ')">Rotate</button></td>' +
     '<td><button id="delete-image-' + data.response.id + '" type="button" class="btn btn-danger" onClick="delete_image(' + data.response.id + ')">Delete</button></td></tr>');
});
JS
    , \yii\web\View::POS_READY); ?>

<?php Yii::$app->view->registerJs(<<<JS
function delete_image(id) {
    $.ajax({
        url: '/site/delete-image',
        type: 'POST',
        data: 'id='+id,
        success: function (data) {
            $('#delete-image-'+id).parent().parent().remove();
        }
    });
}

function rotate_image(id) {
    $.ajax({
        url: '/site/rotate-image',
        type: 'POST',
        data: 'id='+id,
        success: function (data) {
            var result = '<img src="' + data +'">';
            $('#rotate-image-'+id).parent().prev('td').html(result);
        }
    });
}
JS
, \yii\web\View::POS_END); ?>