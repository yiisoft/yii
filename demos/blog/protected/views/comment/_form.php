<div class="form">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($comment); ?>

<div class="row">
<?php echo CHtml::activeLabel($comment,'author'); ?>
<?php echo CHtml::activeTextField($comment,'author',array('size'=>65,'maxlength'=>128)); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($comment,'email'); ?>
<?php echo CHtml::activeTextField($comment,'email',array('size'=>65,'maxlength'=>128)); ?>
<p class="hint">
Your email address will not be published.
</p>
</div>
<div class="row">
<?php echo CHtml::activeLabel($comment,'url'); ?>
<?php echo CHtml::activeTextField($comment,'url',array('size'=>65,'maxlength'=>128)); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($comment,'content'); ?>
<?php echo CHtml::activeTextArea($comment,'content',array('rows'=>6, 'cols'=>50)); ?>
<p class="hint">
You may use <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown syntax</a>.
</p>
</div>

<?php if(Yii::app()->user->isGuest && extension_loaded('gd')): ?>
<div class="row">
	<?php echo CHtml::activeLabel($comment,'verifyCode'); ?>
	<div>
	<?php $this->widget('CCaptcha'); ?>
	<?php echo CHtml::activeTextField($comment,'verifyCode'); ?>
	</div>
	<p class="hint">Please enter the letters as they are shown in the image above.
	<br/>Letters are not case-sensitive.</p>
</div>
<?php endif; ?>

<div class="row action">
<?php echo CHtml::submitButton($buttonLabel,array('name'=>'submitComment')); ?>

<?php echo CHtml::submitButton('Preview',array('name'=>'previewComment')); ?>
</div>

</form>
</div><!-- form -->

<?php if(isset($_POST['previewComment']) && !$comment->hasErrors()): ?>
<h3>Preview</h3>
<div class="comment">
  <div class="author"><?php echo $comment->authorLink; ?> says:</div>
  <div class="time"><?php echo date('F j, Y \a\t h:i a',$comment->createTime); ?></div>
  <div class="content"><?php echo $comment->contentDisplay; ?></div>
</div><!-- post preview -->
<?php endif; ?>