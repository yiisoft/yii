<?php
$this->pageTitle = 'Редактировать курс';
$this->breadcrumbs=array(
	'Курсы'=>array('aisana/coursesAdmin'),
	'Редактировать',
);
?>

<h1>Редактировать курс</h1>

<?php $this->renderPartial('_courseForm', array('model'=>$model)); ?>



