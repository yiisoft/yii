<h1>Module Generator</h1>

<p>This generator helps you to generate the skeleton code needed by a Yii module.</p>

<div class="form gii">

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php $form=$this->beginWidget('CActiveForm'); ?>

		<div class="row">
			<?php echo $form->labelEx($model,'moduleID'); ?>
			<?php echo $form->textField($model,'moduleID',array('size'=>65)); ?>
			<div class="tooltip">
				Module ID is case-sensitive. It should only contain word characters.
				The generated module class will be named after the module ID.
				For example, a module ID <code>forum</code> will generate the module class
				<code>ForumModule</code>.
			</div>
			<?php echo $form->error($model,'moduleID'); ?>
		</div>

		<?php $this->renderGenerator($model, $form); ?>

	<?php $this->endWidget(); ?>
</div>
