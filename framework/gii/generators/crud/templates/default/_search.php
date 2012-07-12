<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php echo "<?php /* @var \$this {$this->getControllerClass()} */ ?>\n"; ?>
<?php echo "<?php /* @var \$model {$this->getModelClass()} */ ?>\n\n"; ?>
<div class="wide form">

<?php echo "<?php /* @var \$form CActiveForm */ ?>\n"; ?>
<?php echo "<?php \$form=\$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl(\$this->route),
	'method'=>'get',
)); ?>\n"; ?>

<?php foreach($this->tableSchema->columns as $column): ?>
<?php
	$field=$this->generateInputField($this->modelClass,$column);
	if(strpos($field,'password')!==false)
		continue;
?>
	<div class="row">
		<?php echo "<?php echo \$form->label(\$model,'{$column->name}'); ?>\n"; ?>
		<?php echo "<?php echo ".$this->generateActiveField($this->modelClass,$column)."; ?>\n"; ?>
	</div>

<?php endforeach; ?>
	<div class="row buttons">
		<?php echo "<?php echo CHtml::submitButton('Search'); ?>\n"; ?>
	</div>

<?php echo "<?php \$this->endWidget(); ?>\n"; ?>

</div><!-- search-form -->