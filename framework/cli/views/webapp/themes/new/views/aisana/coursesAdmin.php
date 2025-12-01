<?php
$this->pageTitle = 'Управление курсами';
?>

<div class="courses-admin">
	<h1>Управление курсами</h1>
	
	<div class="admin-actions">
		<?php echo CHtml::link('Создать курс', array('aisana/courseCreate'), array('class'=>'btn btn-primary')); ?>
	</div>

	<?php $this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'courses-grid',
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
				'template'=>'{update} {delete}',
				'updateButtonUrl'=>'Yii::app()->createUrl("aisana/courseUpdate", array("id"=>$data->id))',
				'deleteButtonUrl'=>'Yii::app()->createUrl("aisana/courseDelete", array("id"=>$data->id))',
			),
		),
	)); ?>
</div>



