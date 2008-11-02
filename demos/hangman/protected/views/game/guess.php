<h2>Please make a guess</h2>

<h3 style="letter-spacing: 4px;"><?php echo $this->guessWord; ?></h3>

<p>You have made <?php echo $this->misses; ?> bad guesses out of a maximum of <?php echo $this->level; ?>.</p>

<?php echo CHtml::statefulForm(); ?>

<p>Guess:
<?php
for($i=ord('A');$i<=ord('Z');++$i)
{
	if(!$this->isGuessed(chr($i)))
		echo "\n".CHtml::linkButton(chr($i),array('submit'=>array('guess','g'=>chr($i))));
}
?>
</p>

<p><?php echo CHtml::linkButton('Give up?',array('submit'=>array('giveup'))); ?></p>

</form>
