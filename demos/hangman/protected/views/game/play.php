<p>This is the game of Hangman. You must guess a word, a letter at a time.
If you make too many mistakes, you lose the game!</p>

<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::radioButtonList('level', null, $levels); ?>

<br/>
<?php echo CHtml::submitButton('Play!'); ?>

<?php if($error): ?>
<span style="color:red">You must choose a difficulty level!</span>
<?php endif; ?>

<?php echo CHtml::endForm(); ?>