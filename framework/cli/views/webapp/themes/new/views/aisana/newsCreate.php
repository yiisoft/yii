<?php
$this->pageTitle = 'Создать новость';
$this->breadcrumbs=array(
	'Новости'=>array('aisana/newsAdmin'),
	'Создать',
);
?>

<h1>Создать новость</h1>

<?php $this->renderPartial('../news/_form', array('model'=>$model)); ?>

