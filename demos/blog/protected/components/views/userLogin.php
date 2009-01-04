<?php echo CHtml::form(); ?>
<div class="row">
<?php echo CHtml::activeLabel($user,'username'); ?>
<br/>
<?php echo CHtml::activeTextField($user,'username') ?>
<?php echo CHtml::error($user,'username'); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($user,'password'); ?>
<br/>
<?php echo CHtml::activePasswordField($user,'password') ?>
<?php echo CHtml::error($user,'password'); ?>
</div>
<div class="row">
<?php echo CHtml::activeCheckBox($user,'rememberMe'); ?>
<?php echo CHtml::label('Remember me next time',CHtml::getActiveId($user,'rememberMe')); ?>
</div>
<div class="row">
<?php echo CHtml::submitButton('Login'); ?>
<p class="hint">You may login with <b>demo/demo</b></p>
</div>
</form>