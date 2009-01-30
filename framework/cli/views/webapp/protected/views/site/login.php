<?php $this->pageTitle=Yii::app()->name . ' - Login'; ?>

<h1>Login</h1>

<div class="yiiForm">
<p>
Fields with <span class="required">*</span> are required.
</p>
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($form); ?>

<div class="simple">
<?php echo CHtml::activeLabelEx($form,'username'); ?>
<?php echo CHtml::activeTextField($form,'username') ?>
</div>

<div class="simple">
<?php echo CHtml::activeLabelEx($form,'password'); ?>
<?php echo CHtml::activePasswordField($form,'password') ?>
<p class="hint">
Hint: You may login with <tt>demo/demo</tt> or <tt>admin/admin</tt>.
</p>
</div>

<div class="action">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?>
<?php echo CHtml::label('Remember me next time',CHtml::getActiveId($form,'rememberMe')); ?>
<br/>
<?php echo CHtml::submitButton('Login'); ?>
</div>

</form>
</div><!-- yiiForm -->