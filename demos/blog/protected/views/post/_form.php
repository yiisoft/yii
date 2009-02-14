<div class="form">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($post); ?>

<div class="row">
<?php echo CHtml::activeLabel($post,'title'); ?>
<?php echo CHtml::activeTextField($post,'title',array('size'=>65,'maxlength'=>128)); ?>
</div>
<div class="row">
<?php echo CHtml::activeLabel($post,'content'); ?>
<?php echo CHtml::activeTextArea($post,'content',array('rows'=>20, 'cols'=>50)); ?>
<p class="hint">
You may use <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown syntax</a>.
</p>
</div>
<div class="row">
<?php echo CHtml::activeLabel($post,'tags'); ?>
<?php echo CHtml::activeTextField($post,'tags',array('size'=>65)); ?>
<p class="hint">
Separate different tags with commas.
</p>
</div>
<div class="row">
<?php echo CHtml::activeLabel($post,'status'); ?>
<?php echo CHtml::activeDropDownList($post,'status',Post::model()->statusOptions); ?>
</div>

<div class="row action">
<?php echo CHtml::submitButton($update ? 'Save' : 'Create', array('name'=>'submitPost')); ?>

<?php echo CHtml::submitButton('Preview',array('name'=>'previewPost')); ?>
</div>

</form>
</div><!-- form -->

<?php if(isset($_POST['previewPost']) && !$post->hasErrors()): ?>
<h3>Preview</h3>
<div class="post">
  <div class="title"><?php echo CHtml::encode($post->title); ?></div>
  <div class="author">posted by <?php echo Yii::app()->user->name . ' on ' . date('F j, Y',$post->createTime); ?></div>
  <div class="content"><?php echo $post->contentDisplay; ?></div>
</div><!-- post preview -->
<?php endif; ?>