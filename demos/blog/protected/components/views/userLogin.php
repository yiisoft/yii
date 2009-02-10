<?php echo CHtml::form(); ?>
<div class="row">
<?php echo CHtml::activeLabel($form,'username'); ?>
<br/>
<?php echo CHtml::activeTextField($form,'username') ?>
<?php echo CHtml::error($form,'username'); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($form,'password'); ?>
<br/>
<?php echo CHtml::activePasswordField($form,'password') ?>
<?php echo CHtml::error($form,'password'); ?>
</div>
<div class="row">
<?php echo CHtml::activeCheckBox($form,'rememberMe'); ?>
<?php echo CHtml::activeLabel($form,'rememberMe'); ?>
</div>
<div class="row">
<?php echo CHtml::submitButton('Login'); ?>
<p class="hint">You may login with <b>demo/demo</b></p>
</div>
</form>