<h1>Form Generator</h1>

<p>This generator helps you to quickly generate a new form view for your model.</p>

<div class="form gii">

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php $form=$this->beginWidget('CActiveForm'); ?>

		<div class="row">
			<?php echo $form->labelEx($model,'modelClass'); ?>
			<?php echo $form->textField($model,'modelClass', array('size'=>65)); ?>
			<div class="tooltip">
				Model class. This can be either the name of the model class 
				(e.g. 'ContactForm') or the path alias of the model class file 
				(e.g. 'application.models.ContactForm'). The former can be used 
				only if the class can be autoloaded. Below are some examples:
				<ul>
					<li>"ContactForm"</li>
					<li>"application.models.user.SignupForm"</li>
				</ul>
			</div>
			<?php echo $form->error($model,'modelClass'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'viewName'); ?>
			<?php echo $form->textField($model,'viewName', array('size'=>65)); ?>
			<div class="tooltip">
				View name: the name of the view to be generated. This should be the path 
				alias of the view script. Below are some examples:
				<ul>
					<li>"application.views.site.contact"</li>
					<li>"module.views.default.search"</li>
				</ul>
			</div>
			<?php echo $form->error($model,'viewName'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'scenario'); ?>
			<?php echo $form->textField($model,'scenario', array('size'=>65)); ?>
			<div class="tooltip">
				Model class. This can be either the name of the model class 
				(e.g. 'ContactForm') or the path alias of the model class file 
				(e.g. 'application.models.ContactForm'). The former can be used 
				only if the class can be autoloaded. Below are some examples:
				<ul>
					<li>"ContactForm"</li>
					<li>"application.models.user.SignupForm"</li>
				</ul>
			</div>
			<?php echo $form->error($model,'scenario'); ?>
		</div>
		

		<?php $this->renderGenerator($model, $form); ?>

	<?php $this->endWidget(); ?>
</div>
