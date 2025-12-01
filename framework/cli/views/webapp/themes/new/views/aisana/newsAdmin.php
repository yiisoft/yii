<?php
$this->pageTitle = 'Управление новостями';
?>

<div class="news-admin">
	<h1>Управление новостями</h1>
	
	<div class="admin-actions">
		<?php echo CHtml::link('Создать новость', array('aisana/newsCreate'), array('class'=>'btn btn-primary')); ?>
	</div>

	<?php $this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'news-grid',
		'dataProvider'=>$model->search(),
		'filter'=>$model,
		'columns'=>array(
			'id',
			'title',
			array(
				'name'=>'published',
				'value'=>'$data->published ? "Да" : "Нет"',
				'filter'=>array('0'=>'Нет', '1'=>'Да'),
			),
			'created_at',
			array(
				'class'=>'CButtonColumn',
				'template'=>'{view} {update} {delete}',
				'viewButtonUrl'=>'Yii::app()->createUrl("aisana/newsView", array("slug"=>$data->slug))',
				'updateButtonUrl'=>'Yii::app()->createUrl("aisana/newsUpdate", array("id"=>$data->id))',
				'deleteButtonUrl'=>'Yii::app()->createUrl("aisana/newsDelete", array("id"=>$data->id))',
			),
		),
	)); ?>
</div>

