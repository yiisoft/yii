<div class="news-form">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'news-form',
		'enableAjaxValidation'=>false,
		'htmlOptions'=>array('enctype'=>'multipart/form-data'),
	)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>255, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'excerpt'); ?>
		<?php echo $form->textArea($model,'excerpt',array('rows'=>3, 'cols'=>50, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'excerpt'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'content'); ?>
		<?php echo $form->textArea($model,'content',array('rows'=>10, 'cols'=>50, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'content'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'image'); ?>
		<?php echo $form->textField($model,'image',array('size'=>60,'maxlength'=>500, 'class'=>'form-control', 'placeholder'=>'URL изображения')); ?>
		<?php echo $form->error($model,'image'); ?>
		<p class="help-text">Введите URL изображения или путь к файлу</p>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'imageFile'); ?>
		<?php echo $form->fileField($model,'imageFile', array('class'=>'form-control')); ?>
		<?php echo $form->error($model,'imageFile'); ?>
		<p class="help-text">Поддерживаемые форматы: JPG, PNG, GIF, WebP. Загруженный файл автоматически сохранится в `/uploads/news`.</p>
		<?php if(!$model->isNewRecord && !empty($model->image)): ?>
			<div class="image-preview">
				<img src="<?php echo strpos($model->image, 'http') === 0 ? $model->image : Yii::app()->request->baseUrl . $model->image; ?>" alt="Текущее изображение">
				<span>Текущее изображение</span>
			</div>
		<?php endif; ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'slug'); ?>
		<?php echo $form->textField($model,'slug',array('size'=>60,'maxlength'=>255, 'class'=>'form-control')); ?>
		<?php echo $form->error($model,'slug'); ?>
		<p class="help-text">Оставьте пустым для автоматической генерации из заголовка</p>
	</div>

	<div class="form-group">
		<?php echo $form->checkBox($model,'published'); ?>
		<?php echo $form->label($model,'published'); ?>
		<?php echo $form->error($model,'published'); ?>
	</div>

	<div class="form-actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class'=>'btn btn-primary')); ?>
		<?php echo CHtml::link('Отмена', array('aisana/newsAdmin'), array('class'=>'btn btn-secondary')); ?>
	</div>

	<?php $this->endWidget(); ?>

</div>

