<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo "<?php echo CHtml::form(); ?>\n"; ?>

<?php echo "<?php echo CHtml::errorSummary(\${$modelVar}); ?>\n"; ?>

<?php foreach($columns as $name=>$column): ?>
<div class="simple">
<?php echo "<?php echo CHtml::activeLabelEx(\${$modelVar},'$name'); ?>\n"; ?>
<?php echo "<?php echo ".$this->generateInputField($model,$modelVar,$column)."; ?>\n"; ?>
</div>
<?php endforeach; ?>

<div class="action">
<?php echo "<?php echo CHtml::submitButton(\$update ? 'Save' : 'Create'); ?>\n"; ?>
</div>

</form>
</div><!-- yiiForm -->