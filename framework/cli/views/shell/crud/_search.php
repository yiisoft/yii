<?php
/**
 * This is the template for generating the form view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 */
?>
<div class="wide form search-form" style="display:none">

<?php echo "<?php echo CHtml::beginForm('','get'); ?>\n"; ?>

	<?php echo "<?php echo CHtml::hiddenField('r',\$this->route); ?>\n"; ?>

<?php foreach($columns as $column): ?>
<?php
	$field=$this->generateInputField($modelClass,$column);
	if(strpos($field,'password')!==false)
		continue;
?>
	<div class="row">
		<?php echo "<?php echo CHtml::activeLabel(\$model,'{$column->name}'); ?>\n"; ?>
		<?php echo "<?php echo ".$this->generateInputField($modelClass,$column)."; ?>\n"; ?>
	</div>

<?php endforeach; ?>
	<div class="row buttons">
		<?php echo "<?php echo CHtml::submitButton('Search'); ?>\n"; ?>
	</div>

<?php echo "<?php echo CHtml::endForm(); ?>\n"; ?>

</div><!-- search-form -->