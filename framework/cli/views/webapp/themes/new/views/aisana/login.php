<?php
$this->pageTitle = 'Вход в админ-панель';
?>

<div class="login-container">
	<div class="login-box">
		<h1>Вход в админ-панель</h1>
		
		<?php $form=$this->beginWidget('CActiveForm', array(
			'id'=>'login-form',
			'enableClientValidation'=>true,
			'clientOptions'=>array(
				'validateOnSubmit'=>true,
			),
		)); ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'username'); ?>
			<?php echo $form->textField($model,'username', array('class'=>'form-control')); ?>
			<?php echo $form->error($model,'username'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'password'); ?>
			<?php echo $form->passwordField($model,'password', array('class'=>'form-control')); ?>
			<?php echo $form->error($model,'password'); ?>
		</div>

		<div class="form-group">
			<?php echo $form->checkBox($model,'rememberMe'); ?>
			<?php echo $form->label($model,'rememberMe'); ?>
			<?php echo $form->error($model,'rememberMe'); ?>
		</div>

		<div class="form-group">
			<?php echo CHtml::submitButton('Войти', array('class'=>'btn btn-primary')); ?>
		</div>

		<?php $this->endWidget(); ?>
	</div>
</div>

