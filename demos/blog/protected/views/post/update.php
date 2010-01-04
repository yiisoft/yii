<?php
$this->breadcrumbs=array(
	$model->title=>$model->url,
	'Update',
);
?>

<h1>Update <i><?php echo CHtml::encode($model->title); ?></i></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>