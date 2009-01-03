<?php $this->pageTitle=Yii::app()->name . ' - Login'; ?>

<h2>Login</h2>

<div class="form">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($form); ?>

<div class="row">
<?php echo CHtml::activeLabel($form,'username'); ?>
<?php echo CHtml::activeTextField($form,'username') ?>
</div>

<div class="row">
<?php echo CHtml::activeLabel($form,'password'); ?>
<?php echo CHtml::activePasswordField($form,'password') ?>
<p class="hint">You may login with <b>demo/demo</b></p>
</div>

<div class="row action">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?> Remember me next time<br/>
<?php echo CHtml::submitButton('Login'); ?>
</div>

</form>
</div><!-- form -->