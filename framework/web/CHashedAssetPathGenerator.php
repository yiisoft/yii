<?php
class CHashedAssetPathGenerator implements IAssetPathGenerator
{
	public $hashByName=false;
	/**
	 * @param $assetPath
	 * @return string
	 */
	public function generatePath($assetPath)
	{
		$path = $this->hashByName ? basename($assetPath) : dirname($assetPath).filemtime($assetPath);
		return sprintf('%x',crc32($path . Yii::getVersion()));
	}

}
