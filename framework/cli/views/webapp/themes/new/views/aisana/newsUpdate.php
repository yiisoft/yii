<?php
$this->pageTitle = 'Редактировать новость';
$this->breadcrumbs=array(
	'Новости'=>array('aisana/newsAdmin'),
	'Редактировать',
);
?>

<h1>Редактировать новость: <?php echo CHtml::encode($model->title); ?></h1>

<?php $this->renderPartial('../news/_form', array('model'=>$model)); ?>

