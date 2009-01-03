<?php $this->pageTitle=Yii::app()->name . ' - Contact Us'; ?>

<h2>About</h2>

<?php if(Yii::app()->user->hasFlash('contact')): ?>
<div class="confirmation">
<?php echo Yii::app()->user->getFlash('contact'); ?>
</div>
<?php else: ?>

<p>
If you have any questions, please fill out the following form to contact me. Thank you.
</p>

<div class="form">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($contact); ?>

<div class="row">
<?php echo CHtml::activeLabel($contact,'name'); ?>
<?php echo CHtml::activeTextField($contact,'name'); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($contact,'email'); ?>
<?php echo CHtml::activeTextField($contact,'email'); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($contact,'subject'); ?>
<?php echo CHtml::activeTextField($contact,'subject',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($contact,'body'); ?>
<?php echo CHtml::activeTextArea($contact,'body',array('rows'=>6, 'cols'=>50)); ?>
</div>

<?php if(extension_loaded('gd')): ?>
<div class="row">
	<?php echo CHtml::activeLabel($contact,'verifyCode'); ?>
	<div>
	<?php $this->widget('CCaptcha'); ?>
	<?php echo CHtml::activeTextField($contact,'verifyCode'); ?>
	</div>
	<p class="hint">Please enter the letters as they are shown in the image above.
	<br/>Letters are not case-sensitive.</p>
</div>
<?php endif; ?>

<div class="row action">
<?php echo CHtml::submitButton('Submit'); ?>
</div>

</form>
</div><!-- yiiForm -->
<?php endif; ?>