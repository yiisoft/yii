<div class="yiiForm">

<p>
Fields with <span class="required">*</span> are required.
</p>

<?php echo "<?php echo CHtml::beginForm(); ?>\n"; ?>

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

<?php echo "<?php echo CHtml::endForm(); ?>\n"; ?>

</div><!-- yiiForm -->