<div class="course-form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'course-form',
		'enableAjaxValidation'=>false,
	)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>4, 'cols'=>50, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'link'); ?>
		<?php echo $form->textField($model,'link',array('size'=>60,'maxlength'=>500, 'class'=>'form-control', 'placeholder'=>'https://example.com/course')); ?>
		<?php echo $form->error($model,'link'); ?>
		<p class="help-text">Введите полную ссылку на курс (например: https://example.com/course)</p>
	</div>

	<div class="form-group">
		<?php echo $form->checkBox($model,'published'); ?>
		<?php echo $form->label($model,'published'); ?>
		<?php echo $form->error($model,'published'); ?>
	</div>

	<div class="form-actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class'=>'btn btn-primary')); ?>
		<?php echo CHtml::link('Отмена', array('aisana/coursesAdmin'), array('class'=>'btn btn-secondary')); ?>
	</div>

	<?php $this->endWidget(); ?>

</div>



