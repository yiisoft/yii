<h2>Update Post <?php echo $post->id; ?></h2>

<div class="actionBar">
[<?php echo CHtml::link('Post List',array('list')); ?>]
[<?php echo CHtml::link('New Post',array('create')); ?>]
</div>

<div class="yiiForm">
<?php echo CHtml::form(); ?>

<?php echo CHtml::errorSummary($post); ?>

<div class="simple">
<?php echo CHtml::activeLabel($post,'title'); ?>
<?php echo CHtml::activeTextField($post,'title',array('size'=>60,'maxlength'=>128)); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabel($post,'create_time'); ?>
<?php echo CHtml::activeTextField($post,'create_time'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabel($post,'author_id'); ?>
<?php echo CHtml::activeTextField($post,'author_id'); ?>
</div>
<div class="simple">
<?php echo CHtml::activeLabel($post,'content'); ?>
<?php echo CHtml::activeTextArea($post,'content',array('rows'=>6, 'cols'=>50)); ?>
</div>

<div class="action">
<?php echo CHtml::submitButton('Save'); ?>
</div>

</form>
</div><!-- yiiForm -->