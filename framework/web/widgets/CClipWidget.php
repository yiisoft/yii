+<?php
/**
 * CClipWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CClipWidget records its content and makes it available elsewhere.
 *
 * Content rendered between its {@link init()} and {@link run()} calls are saved
 * as a clip in the controller. The clip is named after the widget ID.
 *
 * See {@link CBaseController::beginClip} and {@link CBaseController::endClip}
 * for a shortcut usage of CClipWidget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CClipWidget extends CWidget
{
	/**
	 * @var boolean whether to render the clip content in place. Defaults to false,
	 * meaning the captured clip will not be displayed.
	 */
	public $renderClip=false;

	/**
	 * @var boolean whether to append clip content to existing clip. Defaults to false,
	 * meaning that current clip content will be overwritten if a key already exists with this clip.
	 */
	public $appendToClip=false;

	/**
	 * @var boolean whether to prepend clip content to existing clip. Defaults to false,
	 * meaning that current clip content will be overwritten if a key already exists with this clip.
	 */
	public $prependToClip=false;

	/**
	 * Starts recording a clip.
	 */
	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Ends recording a clip.
	 * This method stops output buffering and saves the rendering result as a named clip in the controller.
	 */
	public function run()
	{
		$clips=$this->getController()->getClips();
		$clip=ob_get_clean();

		if($this->renderClip)
			echo $clip;

		if($this->appendToClip)
			$clips->add($this->getId(),$clips->itemAt($this->getId()).$clip);

		if($this->prependToClip)
			$clips->add($this->getId(),$clips->itemAt($clip.$this->getId()))

		if($this->appendToClip!==true && $prependToClip!==true)
			$this->getController()->getClips()->add($this->getId(),$clip);
	}
}