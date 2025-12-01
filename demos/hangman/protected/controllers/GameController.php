<?php
/**
 * GameController class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */


/**
 * GameController implements the {@link https://en.wikipedia.org/wiki/Hangman_(game) Hangman game}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CController.php 131 2008-11-02 01:32:57Z qiang.xue $
 * @package demos.hangman
 * @since 1.0
 */
class GameController extends CController
{
	/**
	 * @var string sets the default action to be 'play'
	 */
	public $defaultAction='play';

	/**
	 * The 'play' action.
	 * In this action, users are asked to choose a difficulty level
	 * of the game.
	 */
	public function actionPlay()
	{
		static $levels=array(
			'10'=>'Easy game; you are allowed 10 misses.',
			'5'=>'Medium game; you are allowed 5 misses.',
			'3'=>'Hard game; you are allowed 3 misses.',
		);

		// if a difficulty level is correctly chosen
		if(isset($_POST['level']) && isset($levels[$_POST['level']]))
		{
			$this->word=$this->generateWord();
			$this->guessWord=str_repeat('_',strlen($this->word));
			$this->level=$_POST['level'];
			$this->misses=0;
			$this->setPageState('guessed',null);
			// show the guess page
			$this->render('guess');
		}
		else
		{
			$params=array(
				'levels'=>$levels,
				// if this is a POST request, it means the level is not chosen
				'error'=>Yii::app()->request->isPostRequest,
			);
			// show the difficulty level page
			$this->render('play',$params);
		}
	}

	/**
	 * The 'guess' action.
	 * This action is invoked each time when the user makes a guess.
	 */
	public function actionGuess()
	{
		// check to see if the letter is guessed correctly
		if(isset($_GET['g'][0]) && ($result=$this->guess($_GET['g'][0]))!==null)
			$this->render($result ? 'win' : 'lose');
		else // the letter is guessed correctly, but not win yet
		{
			$guessed=$this->getPageState('guessed',array());
			$guessed[$_GET['g'][0]]=true;
			$this->setPageState('guessed',$guessed,array());
			$this->render('guess');
		}
	}

	/**
	 * The 'guess' action.
	 * This action is invoked when the user gives up the game.
	 */
	public function actionGiveup()
	{
		$this->render('lose');
	}

	/**
	 * Checks to see if a letter is already guessed.
	 * @param string the letter
	 * @return boolean whether the letter is already guessed.
	 */
	public function isGuessed($letter)
	{
		$guessed=$this->getPageState('guessed',array());
		return isset($guessed[$letter]);
	}

	/**
	 * Generates a word to be guessed.
	 * @return string the word to be guessed
	 */
	protected function generateWord()
	{
		$wordFile=dirname(__FILE__).'/words.txt';
		$words=preg_split("/[\s,]+/",file_get_contents($wordFile));
		do
		{
			$i=rand(0,count($words)-1);
			$word=$words[$i];
		} while(strlen($word)<5 || !ctype_alpha($word));
		return strtoupper($word);
	}

	/**
	 * Checks to see if a letter is guessed correctly.
	 * @param string the letter
	 * @return mixed true if the word is guessed correctly, false
	 * if the user has used up all guesses and the word is guessed
	 * incorrectly, and null if the letter is guessed correctly but
	 * the whole word is guessed correctly yet.
	 */
	protected function guess($letter)
	{
		$word=$this->word;
		$guessWord=$this->guessWord;
		$pos=0;
		$success=false;
		while(($pos=strpos($word,$letter,$pos))!==false)
		{
			$guessWord[$pos]=$letter;
			$success=true;
			$pos++;
		}
		if($success)
		{
			$this->guessWord=$guessWord;
			if($guessWord===$word)
				return true;
		}
		else
		{
			$this->misses++;
			if($this->misses>=$this->level)
				return false;
		}
	}

	/**
	 * @return integer the difficulty level. This value is persistent
	 * during the whole game session.
	 */
	public function getLevel()
	{
		return $this->getPageState('level');
	}

	/**
	 * @param integer the difficulty level. This value is persistent
	 * during the whole game session.
	 */
	public function setLevel($value)
	{
		$this->setPageState('level',$value);
	}

	/**
	 * @return string the word to be guessed. This value is persistent
	 * during the whole game session.
	 */
	public function getWord()
	{
		return $this->getPageState('word');
	}

	/**
	 * @param string the word to be guessed. This value is persistent
	 * during the whole game session.
	 */
	public function setWord($value)
	{
		$this->setPageState('word',$value);
	}

	/**
	 * @return string the word being guessed. This value is persistent
	 * during the whole game session.
	 */
	public function getGuessWord()
	{
		return $this->getPageState('guessWord');
	}

	/**
	 * @param string the word being guessed. This value is persistent
	 * during the whole game session.
	 */
	public function setGuessWord($value)
	{
		$this->setPageState('guessWord',$value);
	}

	/**
	 * @return integer the number of misses. This value is persistent
	 * during the whole game session.
	 */
	public function getMisses()
	{
		return $this->getPageState('misses');
	}

	/**
	 * @param integer the number of misses. This value is persistent
	 * during the whole game session.
	 */
	public function setMisses($value)
	{
		$this->setPageState('misses',$value);
	}
}