<?php
/**
 * CldrCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CldrCommand converts the locale data from the {@link http://www.unicode.org/cldr/ CLDR project}
 * to PHP scripts so that they can be more easily used in PHP programming.
 *
 * The script respects locale inheritance so that the PHP data for a child locale
 * will contain all its parents' locale data if they are not specified in the child locale.
 * Therefore, to import the data for a locale, only the PHP script for that particular locale
 * needs to be included.
 *
 * Note, only the data relevant to number and date formatting are extracted.
 * Each PHP script file is named as the corresponding locale ID in lower case.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.build
 * @since 1.0
 */
class CldrCommand extends CConsoleCommand
{
	protected $pluralRules = array();

	public function getHelp()
	{
		return <<<EOD
USAGE
  build cldr <data-path>

DESCRIPTION
  This command converts the locale data from the CLDR project
  to PHP scripts so that they can be more easily used in PHP programming.

  The script respects locale inheritance so that the PHP data for
  a child locale will contain all its parent locale data if they are
  not specified in the child locale. Therefore, to import the data
  for a locale, only the PHP script for that particular locale needs
  to be included.

  Note, only the data relevant to number and date formatting are extracted.
  Each PHP script file is named as the corresponding locale ID in lower case.

  The resulting PHP scripts are created under the same directory that
  contains the original CLDR data.

PARAMETERS
 * data-path: required, the original CLDR data directory. This
   directory should contain "main" subdirectory with hundreds of XML files
   and "supplemental" subdirectory with "plurals.xml".

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
		{
			$cldrPath = dirname(__FILE__).'/../temp';
			$args[0] = $cldrPath.'/common';
			//$this->usageError('the CLDR data directory is not specified.');
		}
		if(!is_dir($basePath=$args[0]))
		{
			if (!mkdir($basePath, 0777, true))
			{
				$this->usageError("Directory '$basePath' can not be created.");
			}
			//$this->usageError("Directory '$basePath' does not exist.");
		}
		if(!is_dir($path=$basePath.DIRECTORY_SEPARATOR.'main'))
		{
			// look for zip file
			if(!is_file($zipFile=$cldrPath.'/core.zip')) {
				// download latest core.zip file
				$latestUrl = 'http://www.unicode.org/Public/cldr/latest/core.zip';
				$ch = curl_init($latestUrl);
				$fp = fopen($zipFile, "w");
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				if (curl_exec($ch)===FALSE)
				{
					$this->usageError("Failed to download from '$latestUrl'.");
				};
				curl_close($ch);
				fclose($fp);
			}
			// unzip file
			$zip = new ZipArchive;
			if ($zip->open($zipFile) === TRUE)
			{
				$zip->extractTo($cldrPath);
				$zip->close();
			}
			else
			{
				$this->usageError("Failed to unzip '$zipFile'.");
			}
		}

		if(!is_file($pluralFile=$basePath.DIRECTORY_SEPARATOR.'supplemental'.DIRECTORY_SEPARATOR.'plurals.xml'))
			$this->usageError("File '$pluralFile' does not exist.");

		// parse plural.xml before locale files
		$pluralXml = simplexml_load_file($pluralFile);
		$this->parsePluralRules($pluralXml);

		// collect XML files to be processed
		$options=array(
			'exclude'=>array('.gitignore'),
			'fileTypes'=>array('xml'),
			'level'=>0,
		);
		$files=CFileHelper::findFiles(realpath($path),$options);
		$sourceFiles=array();
		foreach($files as $file)
			$sourceFiles[basename($file)]=$file;

		// sort by file name so that inheritances can be processed properly
		ksort($sourceFiles);

		// process root first because it is inherited by all
		if(isset($sourceFiles['root.xml']))
		{
			$this->process($sourceFiles['root.xml']);
			unset($sourceFiles['root.xml']);

			foreach($sourceFiles as $sourceFile)
				$this->process($sourceFile);

			// clean up temporary files
			function rrmdir($path)
			{
				return is_file($path)?
					@unlink($path):
					array_map('rrmdir',glob($path.'/*'))==@rmdir($path);
			}
			rrmdir($cldrPath);
		}
		else
			die('Unable to find the required root.xml under CLDR "main" data directory.');
	}

	protected function process($path)
	{
		$source=basename($path);
		echo "processing $source...";

		$dir=dirname($path);
		$locale=substr($source,0,-4);
		$target=$locale.'.php';

		$i18nDataPath = dirname(__FILE__).'/../../framework/i18n/data';

		// retrieve parent data first
		if(($pos=strrpos($locale,'_'))!==false)
			$data=require($i18nDataPath.DIRECTORY_SEPARATOR.strtolower(substr($locale,0,$pos)).'.php');
		else if($locale!=='root')
			$data=require($i18nDataPath.DIRECTORY_SEPARATOR.'root.php');
		else
			$data=array();

		$xml=simplexml_load_file($path);

		$this->parseVersion($xml,$data);

		$this->parseNumberSymbols($xml,$data);
		$this->parseNumberFormats($xml,$data);
		$this->parseCurrencySymbols($xml,$data);

		$this->parseLanguages($xml,$data);
		$this->parseScripts($xml,$data);
		$this->parseTerritories($xml,$data);

		$this->parseMonthNames($xml,$data);
		$this->parseWeekDayNames($xml,$data);
		$this->parseEraNames($xml,$data);

		$this->parseDateFormats($xml,$data);
		$this->parseTimeFormats($xml,$data);
		$this->parseDateTimeFormat($xml,$data);
		$this->parsePeriodNames($xml,$data);

		$this->parseOrientation($xml,$data);

		$this->addPluralRules($data, $locale);

		$data=str_replace("\r",'',var_export($data,true));
		$locale=substr(basename($path),0,-4);
		$content=<<<EOD
/**
 * Locale data for '$locale'.
 *
 * This file is automatically generated by yiic cldr command.
 *
 * Copyright © 1991-2007 Unicode, Inc. All rights reserved.
 * Distributed under the Terms of Use in http://www.unicode.org/copyright.html.
 *
 * @copyright 2008-2013 Yii Software LLC (http://www.yiiframework.com/license/)
 */
return $data;
EOD;

		file_put_contents($i18nDataPath.DIRECTORY_SEPARATOR.strtolower($locale).'.php',"<?php\n".$content."\n");

		echo "done.\n";
	}

	protected function parseVersion($xml,&$data)
	{
		preg_match('/[\d\.]+/',(string)$xml->identity->version['number'],$matches);
		$data['version']=$matches[0];
	}

	protected function parseNumberSymbols($xml,&$data)
	{
		foreach($xml->xpath('/ldml/numbers/symbols/*') as $symbol)
		{
			$name=$symbol->getName();
			if(!isset($data['numberSymbols'][$name]) || (string)$symbol['draft']==='')
				$data['numberSymbols'][$name]=(string)$symbol;
		}
	}

	protected function parseNumberFormats($xml,&$data)
	{
		$pattern=$xml->xpath('/ldml/numbers/decimalFormats/decimalFormatLength/decimalFormat/pattern');
		if(isset($pattern[0]))
			$data['decimalFormat']=(string)$pattern[0];
		$pattern=$xml->xpath('/ldml/numbers/scientificFormats/scientificFormatLength/scientificFormat/pattern');
		if(isset($pattern[0]))
			$data['scientificFormat']=(string)$pattern[0];
		$pattern=$xml->xpath('/ldml/numbers/percentFormats/percentFormatLength/percentFormat/pattern');
		if(isset($pattern[0]))
			$data['percentFormat']=(string)$pattern[0];
		$pattern=$xml->xpath('/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern');
		if(isset($pattern[0]))
			$data['currencyFormat']=(string)$pattern[0];
	}

	protected function parseCurrencySymbols($xml,&$data)
	{
		$currencies=$xml->xpath('/ldml/numbers/currencies/currency');
		foreach($currencies as $currency)
		{
			if((string)$currency->symbol!='')
				$data['currencySymbols'][(string)$currency['type']]=(string)$currency->symbol;
		}
	}

	protected function parseLanguages($xml,&$data)
	{
		$languages=$xml->xpath('/ldml/localeDisplayNames/languages/language');
		foreach($languages as $language)
		{
			if((string)$language!='')
				$data['languages'][strtolower(str_replace('-','_',(string)$language['type']))]=(string)$language;
		}
	}

	protected function parseScripts($xml,&$data)
	{
		$scripts=$xml->xpath('/ldml/localeDisplayNames/scripts/script');
		foreach($scripts as $script)
		{
			if((string)$script!='')
				$data['scripts'][strtolower(str_replace('-','_',(string)$script['type']))]=(string)$script;
		}
	}

	protected function parseTerritories($xml,&$data)
	{
		$territories=$xml->xpath('/ldml/localeDisplayNames/territories/territory');
		foreach($territories as $territory)
		{
			if((string)$territory!='')
				$data['territories'][strtolower(str_replace('-','_',(string)$territory['type']))]=(string)$territory;
		}
	}

	protected function parseMonthNames($xml,&$data)
	{
		$monthTypes=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/months/monthContext[@type=\'format\']/monthWidth');
		if(is_array($monthTypes))
		{
			foreach($monthTypes as $monthType)
			{
				$names=array();
				foreach($monthType->xpath('month') as $month)
					$names[(string)$month['type']]=(string)$month;
				if($names!==array())
					foreach($names as $type=>$name)
						$data['monthNames'][(string)$monthType['type']][$type]=$name;
			}
		}

		if(!isset($data['monthNames']['abbreviated']))
			$data['monthNames']['abbreviated']=$data['monthNames']['wide'];

		$monthTypes=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/months/monthContext[@type=\'stand-alone\']/monthWidth');
		if(is_array($monthTypes))
		{
			foreach($monthTypes as $monthType)
			{
				$names=array();
				foreach($monthType->xpath('month') as $month)
					$names[(string)$month['type']]=(string)$month;
				if($names!==array())
					foreach($names as $type=>$name)
						$data['monthNamesSA'][(string)$monthType['type']][$type]=$name;
			}
		}
	}

	protected function parseWeekDayNames($xml,&$data)
	{
		static $mapping=array(
			'sun'=>0,
			'mon'=>1,
			'tue'=>2,
			'wed'=>3,
			'thu'=>4,
			'fri'=>5,
			'sat'=>6,
		);
		$dayTypes=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/days/dayContext[@type=\'format\']/dayWidth');
		if(is_array($dayTypes))
		{
			foreach($dayTypes as $dayType)
			{
				$names=array();
				foreach($dayType->xpath('day') as $day)
					$names[$mapping[(string)$day['type']]]=(string)$day;
				if($names!==array())
					$data['weekDayNames'][(string)$dayType['type']]=$names;
			}
		}

		if(!isset($data['weekDayNames']['abbreviated']))
			$data['weekDayNames']['abbreviated']=$data['weekDayNames']['wide'];

		$dayTypes=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/days/dayContext[@type=\'stand-alone\']/dayWidth');
		if(is_array($dayTypes))
		{
			foreach($dayTypes as $dayType)
			{
				$names=array();
				foreach($dayType->xpath('day') as $day)
					$names[$mapping[(string)$day['type']]]=(string)$day;
				if($names!==array())
					$data['weekDayNamesSA'][(string)$dayType['type']]=$names;
			}
		}
	}

	protected function parsePeriodNames($xml,&$data)
	{
		$am=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/dayPeriods/dayPeriodContext[@type=\'format\']/dayPeriodWidth[@type=\'wide\']/dayPeriod[@type=\'am\']');
		if(is_array($am) && isset($am[0]))
			$data['amName']=(string)$am[0];
		$pm=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/dayPeriods/dayPeriodContext[@type=\'format\']/dayPeriodWidth[@type=\'wide\']/dayPeriod[@type=\'pm\']');
		if(is_array($pm) && isset($pm[0]))
			$data['pmName']=(string)$pm[0];
	}

	protected function parseEraNames($xml,&$data)
	{
		$era=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/eras/eraAbbr');
		if(is_array($era) && isset($era[0]))
		{
			foreach($era[0]->xpath('era') as $e)
				$data['eraNames']['abbreviated'][(string)$e['type']]=(string)$e;
		}

		$era=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/eras/eraNames');
		if(is_array($era) && isset($era[0]))
		{
			foreach($era[0]->xpath('era') as $e)
				$data['eraNames']['wide'][(string)$e['type']]=(string)$e;
		}
		else if(!isset($data['eraNames']['wide']))
			$data['eraNames']['wide']=$data['eraNames']['abbreviated'];

		$era=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/eras/eraNarrow');
		if(is_array($era) && isset($era[0]))
		{
			foreach($era[0]->xpath('era') as $e)
				$data['eraNames']['narrow'][(string)$e['type']]=(string)$e;
		}
		else if(!isset($data['eraNames']['narrow']))
			$data['eraNames']['narrow']=$data['eraNames']['abbreviated'];
	}

	protected function parseDateFormats($xml,&$data)
	{
		$types=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/dateFormats/dateFormatLength');
		if(is_array($types))
		{
			foreach($types as $type)
			{
				$pattern=$type->xpath('dateFormat/pattern');
				$data['dateFormats'][(string)$type['type']]=(string)$pattern[0];
			}
		}
	}

	protected function parseTimeFormats($xml,&$data)
	{
		$types=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/timeFormats/timeFormatLength');
		if(is_array($types))
		{
			foreach($types as $type)
			{
				$pattern=$type->xpath('timeFormat/pattern');
				$data['timeFormats'][(string)$type['type']]=(string)$pattern[0];
			}
		}
	}

	protected function parseDateTimeFormat($xml,&$data)
	{
		$types=$xml->xpath('/ldml/dates/calendars/calendar[@type=\'gregorian\']/dateTimeFormats/dateTimeFormatLength');
		if(is_array($types) && isset($types[0]))
		{
			$picked = $types[0];
			foreach($types as $element) {
				$attrs = $element->attributes();
				if($attrs['type'] == 'medium')
				{
					$picked = $element;
					break;
				}
			}
			$pattern=$picked->xpath('dateTimeFormat/pattern');
			$data['dateTimeFormat']=(string)$pattern[0];
		}
	}

	protected function parseOrientation($xml,&$data)
	{
		$orientation=$xml->xpath('/ldml/layout/orientation[@characters=\'right-to-left\']');
		if(!empty($orientation))
			$data['orientation']='rtl';
		else if(!isset($data['orientation']))
			$data['orientation']='ltr';
	}

	/**
	 * @see http://cldr.unicode.org/index/cldr-spec/plural-rules
	 */
	protected function parsePluralRules($xml)
	{
		echo "Processing plural.xml...";
		$patterns = array(
			'/\s+is\s+not\s+/i'=>'!=', //is not
			'/\s+is\s+/i'=>'==', //is
			'/n\s+mod\s+(\d+)/i'=>'fmod(n,$1)', //mod (CLDR's "mod" is "fmod()", not "%")
			'/^(.*?)\s+not\s+(?:in|within)\s+(\d+)\.\.(\d+)/i'=>'($1<$2||$1>$3)', //not in, not within
			'/^(.*?)\s+within\s+(\d+)\.\.(\d+)/i'=>'($1>=$2&&$1<=$3)', //within
			'/^(.*?)\s+in\s+(\d+)\.\.(\d+)/i'=>'($1>=$2&&$1<=$3&&fmod($1,1)==0)', //in
		);
		foreach($xml->plurals->pluralRules as $node)
		{
			$attributes=$node->attributes();
			$locales=explode(' ',$attributes['locales']);
			$rules=array();

			if(!empty($node->pluralRule))
			{
				foreach($node->pluralRule as $rule)
				{
					$expr_or=preg_split('/\s+or\s+/i', $rule);
					foreach ($expr_or as $key_or => $val_or)
					{
						$expr_and=preg_split('/\s+and\s+/i', $val_or);
						$expr_and=preg_replace(array_keys($patterns), array_values($patterns), $expr_and);
						$expr_or[$key_or]=implode('&&', $expr_and);
					}
					$rules[]=implode('||', $expr_or);
				}
				//append last rule to match "other"
				$rules[] = 'true';
				foreach ($locales as $locale)
				{
					$this->pluralRules[$locale] = $rules;
				}
			}

		}
		echo "Done.\n";
	}

	protected function addPluralRules(&$data, $locale)
	{
		if (!empty($this->pluralRules[$locale]))
			$data['pluralRules']=$this->pluralRules[$locale];
	}
}
