<?php
/**
 * TranslationsCommand class file.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * TranslationsCommand handles tasks related to framework translations.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @package system.build
 * @since 1.1.11
 */
class TranslationsCommand extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
This command handles tasks related to framework translations.

USAGE
  build translations report --sourcePath=<path> --translationPath=<path>

PARAMETERS
  * sourcePath: required, the directory where the original documentation files are.
  * translationPath: required, the directory where the translated documentation files are.
  * title: optional, custom report title

EXAMPLES
  * build translations report --sourcePath="../docs/guide" --translationPath="../docs/guide/ru" --title="Russian guide translation report" > report_guide_ru.html
  * build translations report --sourcePath="../docs/blog" --translationPath="../docs/blog/ru" --title="Russian blog translation report" > report_blog_ru.html
  * build translations report --sourcePath="../framework/views" --translationPath="../framework/views/ru" --title="Russian framework views translation report" > report_views_ru.html

EOD;
	}

	public function actionIndex()
	{
		echo $this->getHelp();
	}

	/**
	 * Generates summary report for given translation and original directories
	 *
	 * @param string $sourcePath the directory where the original documentation files are
	 * @param string $translationPath the directory where the translated documentation files are
	 * @param string $title custom title to use for report
	 */
	public function actionReport($sourcePath, $translationPath, $title = 'Translation report')
	{
		$sourcePath=trim($sourcePath, '/\\');
		$translationPath=trim($translationPath, '/\\');

		$results = array();

		$dir = new DirectoryIterator($sourcePath);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
				$translatedFilePath = $translationPath.'/'.$fileinfo->getFilename();
				$sourceFilePath = $sourcePath.'/'.$fileinfo->getFilename();

				$errors = $this->checkFiles($translatedFilePath);
				$diff = empty($errors) ? $this->getDiff($translatedFilePath, $sourceFilePath) : '';
				if(!empty($diff)) {
					$errors[] = 'Translation outdated.';
				}

				$result = array(
					'errors' => $errors,
					'diff' => $diff,
				);

				$results[$fileinfo->getFilename()] = $result;
			}
		}

		// checking if there are obsolete translation files
		$dir = new DirectoryIterator($translationPath);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
				$translatedFilePath = $translationPath.'/'.$fileinfo->getFilename();

				$errors = $this->checkFiles(null, $translatedFilePath);
				if(!empty($errors)) {
					$results[$fileinfo->getFilename()]['errors'] = $errors;
				}
			}
		}

		$this->renderFile(dirname(__FILE__).'/translations/report_html.php', array(
			'results' => $results,
			'sourcePath' => $sourcePath,
			'translationPath' => $translationPath,
			'title' => $title,
		));
	}

	/**
	 * Checks for files existence
	 *
	 * @param string $translatedFilePath
	 * @param string $sourceFilePath
	 * @return array errors
	 */
	protected function checkFiles($translatedFilePath = null, $sourceFilePath = null)
	{
		$errors = array();
		if($translatedFilePath!==null && !file_exists($translatedFilePath)) {
			$errors[] = 'Translation does not exist.';
		}

		if($sourceFilePath!==null && !file_exists($sourceFilePath)) {
			$errors[] = 'Source does not exist.';
		}

		return $errors;
	}

	/**
	 * Getting DIFF from git
	 *
	 * @param string $translatedFilePath path pointing to translated file
	 * @param string $sourceFilePath path pointing to original file
	 * @return string DIFF
	 */
	protected function getDiff($translatedFilePath, $sourceFilePath)
	{
		$lastTranslationHash = shell_exec('git log -1 --format=format:"%H" -- '.$translatedFilePath);
		return shell_exec('git diff '.$lastTranslationHash.'..HEAD -- '.$sourceFilePath);
	}

	/**
	 * Adds all necessary HTML tags and classes to diff output
	 *
	 * @param string $diff DIFF
	 * @return string highlighted DIFF
	 */
	protected function highlightDiff($diff)
	{
		$lines = explode("\n", $diff);
		foreach ($lines as $key => $val) {
			if (mb_substr($val,0,1,'utf-8') === '@') {
				$lines[$key] = '<span class="info">'.CHtml::encode($val).'</span>';
			}
			else if (mb_substr($val,0,1,'utf-8') === '+') {
				$lines[$key] = '<ins>'.CHtml::encode($val).'</ins>';
			}
			else if (mb_substr($val,0,1,'utf-8') === '-') {
				$lines[$key] = '<del>'.CHtml::encode($val).'</del>';
			}
			else {
				$lines[$key] = CHtml::encode($val);
			}
		}

		return implode("\n", $lines);
	}
}
