<?php
Yii::app()->clientScript->registerScript('gii.crud',"
$('#CrudCode_controller').change(function(){
	$(this).data('changed',$(this).val()!='');
});
$('#CrudCode_model').bind('keyup change', function(){
	var controller=$('#CrudCode_controller');
	if(!controller.data('changed')) {
		var id=new String($(this).val().match(/\\w*$/));
		if(id.length>0)
			id=id.substring(0,1).toLowerCase()+id.substring(1);
		controller.val(id);
	}
});
");
?>
<h1>Crud Generator</h1>

<p>This generator generates a controller and views that implement CRUD operations for the specified data model.</p>

<div class="form gii">

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php $form=$this->beginWidget('CActiveForm'); ?>

		<div class="row">
			<?php echo $form->labelEx($model,'model'); ?>
			<?php echo $form->textField($model,'model',array('size'=>65)); ?>
			<div class="tooltip">
				Model class is case-sensitive. It can be either a class name (e.g. <code>Post</code>)
			    or the path alias of the class file (e.g. <code>application.models.Post</code>).
			    Note that if the former, the class must be auto-loadable.
			</div>
			<?php echo $form->error($model,'model'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'controller'); ?>
			<?php echo $form->textField($model,'controller',array('size'=>65)); ?>
			<div class="tooltip">
				Controller ID is case-sensitive. CRUD controllers are often named after
				the model class name that they are dealing with. Below are some examples:
				<ul>
					<li><code>post</code> generates <code>PostController.php</code></li>
					<li><code>postTag</code> generates <code>PostTagController.php</code></li>
					<li><code>admin/user</code> generates <code>admin/UserController.php</code></li>
				</ul>
			</div>
			<?php echo $form->error($model,'controller'); ?>
		</div>

		<div class="row">
			<?php echo $form->labelEx($model,'baseControllerClass'); ?>
			<?php echo $form->textField($model,'baseControllerClass',array('size'=>65)); ?>
			<div class="tooltip">
				This is the class that the new CRUD controller class will extend from.
				Please make sure the class exists and can be autoloaded.
			</div>
			<?php echo $form->error($model,'baseControllerClass'); ?>
		</div>

		<?php $this->renderGenerator($model, $form); ?>

	<?php $this->endWidget(); ?>
</div>
