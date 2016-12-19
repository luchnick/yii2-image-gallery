<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<?= LinkPager::widget([
	'pagination' => $pages,
]); ?>
<table class="table table-bordered upload-success">
	<?php foreach ($pictures as $picture): ?>
		<tr>
			<td><?= Html::img($picture->getImage(), ['height' => '200'])  ?></td>
			<td>
				<button id="rotate-image-<?= $picture->id ?>" type="button" class="btn btn-primary" onClick="rotate_image(<?= $picture->id ?>)">Rotate</button>
			</td>
			<td>
				<button id="delete-image-<?= $picture->id ?>" type="button" class="btn btn-danger" onClick="delete_image(<?= $picture->id ?>)">Delete</button>
			</td>
		</tr>
	<?php endforeach; ?>
</table>