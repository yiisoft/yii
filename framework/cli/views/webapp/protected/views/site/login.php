<h1>Login</h1>

<div class="yiiForm">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($form); ?>

<div class="simple">
<?php echo CHtml::activeLabel($form,'username'); ?>
<?php echo CHtml::activeTextField($form,'username') ?>
</div>

<div class="simple">
<?php echo CHtml::activeLabel($form,'password'); ?>
<?php echo CHtml::activePasswordField($form,'password') ?>
<p class="hint">
Hint: You may login with <tt>demo/demo</tt> or <tt>admin/admin</tt>.
</p>
</div>

<?php if(extension_loaded('gd')): ?>
<div class="simple">
<?php echo CHtml::activeLabel($form,'verifyCode'); ?>
	<div>
	<?php $this->widget('CCaptcha'); ?>
	<br/>
	<?php echo CHtml::activeTextField($form,'verifyCode'); ?>
	</div>
	<p class="hint">Please enter the letters as they are shown in the image above.
	<br/>Letters are not case-sensitive.</p>
</div>
<?php endif; ?>

<div class="action">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?> Remember me next time<br/>
<?php echo CHtml::submitButton('Login'); ?>
</div>

</form>
</div><!-- yiiForm -->