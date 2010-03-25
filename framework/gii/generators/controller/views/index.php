<h1>Controller Generator</h1>

<p>This generator helps you to quickly generate a new controller class,
one or several controller actions and their corresponding views.</p>

<div class="form gii">

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php $form=$this->beginWidget('CActiveForm'); ?>

		<div class="row">
			<?php echo $form->labelEx($model,'controller'); ?>
			<?php echo $form->textField($model,'controller',array('size'=>65)); ?>
			<div class="tooltip">
				Controller ID is case-sensitive. Below are some examples:
				<ul>
					<li>"post" generates "PostController.php"</li>
					<li>"postTag" generates "PostTagController.php"</li>
					<li>"admin/user" generates "admin/UserController.php"</li>
				</ul>
			</div>
			<?php echo $form->error($model,'controller'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'baseClass'); ?>
			<?php echo $form->textField($model,'baseClass',array('size'=>65)); ?>
			<div class="tooltip">
				This is the class that the new controller class will extend from.
				Please make sure the class exists and can be autoloaded.
			</div>
			<?php echo $form->error($model,'baseClass'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'actions'); ?>
			<?php echo $form->textField($model,'actions',array('size'=>65)); ?>
			<div class="tooltip">
				Action IDs are case-insensitive. Separate multiple action IDs with commas or spaces.
			</div>
			<?php echo $form->error($model,'actions'); ?>
		</div>

		<?php $this->renderGenerator($model); ?>

	<?php $this->endWidget(); ?>
</div>
