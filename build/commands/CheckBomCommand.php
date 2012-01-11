<?php
/**
 * CheckBomCommand
 *
 * Checks for BOM files in a directory. If --fix provided, removes BOM where found.
 * Use --path=my/path to run on non-framework directory.
 *
 * @author Alex Makarov <sam@rmcreative.ru>
 * @package system.build
 * @since 1.1.9
 */
class CheckBomCommand extends CConsoleCommand
{
	const BOM="\xEF\xBB\xBF";

	public function actionIndex($path=null,$fix=null)
	{
		if($path===null)
			$path=YII_PATH;

		echo "Checking $path for files with BOM.\n";

		$checkFiles=CFileHelper::findFiles($path,array(
			'exclude'=>array(
				'.svn',
			),
		));

		$detected=false;
		foreach($checkFiles as $file)
		{
			$fileObj=new SplFileObject($file);
			if(!$fileObj->eof() && false!==strpos($fileObj->fgets(),self::BOM))
			{
				if(!$detected)
				{
					echo "Detected BOM in:\n";
					$detected=true;
				}

				echo $file."\n";

				if($fix)
				{
					file_put_contents($file, substr(file_get_contents($file), 3));
				}
			}
		}
		if(!$detected)
		{
			echo "No files with BOM were detected.\n";
		}
		else if($fix)
		{
			echo "All files were fixed.\n";
		}
	}
}
