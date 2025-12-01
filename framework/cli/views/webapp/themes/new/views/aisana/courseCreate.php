<?php
$this->pageTitle = 'Создать курс';
$this->breadcrumbs=array(
	'Курсы'=>array('aisana/coursesAdmin'),
	'Создать',
);
?>

<h1>Создать курс</h1>

<?php $this->renderPartial('_courseForm', array('model'=>$model)); ?>



