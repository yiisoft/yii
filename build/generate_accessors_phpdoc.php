<?php

$nFiles = 0;
$nFilesTotal = 0;
$nClasses = 0;
$nClassesTotal = 0;

file_put_contents(
    dirname(__FILE__) . '\phpdoc.txt',
    getPhpDocForDir('D:\Web\libs-dev\yii\framework') . getPhpDocStats());
    //getPhpDocForDir('D:\Web\libs\yii\framework') . getPhpDocStats());
//echo getPhpDocForDir('D:\Web\libs\yii\framework');
//echo getPhpDocForDir('D:\Web\libs\yii\framework\caching');
//echo getPhpDocForFile('D:\Web\libs\yii\framework\base\CModel.php');

function getPhpDocStats()
{
    global $nFiles, $nFilesTotal, $nClasses, $nClassesTotal;

    return "\n\nComments for $nClasses classes in $nFiles files (processed $nClassesTotal classes in $nFilesTotal files)\n";
}

function getPhpDocForDir($dirName)
{
    global $nFiles, $nFilesTotal;

    $phpdocDir = "";
    $files = new RegexIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirName)
        ), '#^.+\.php$#i', RecursiveRegexIterator::GET_MATCH);
    foreach ($files as $file) {
        $phpdocFile = getPhpDocForFile($file[0]);
        if ($phpdocFile != "") {
            $phpdocDir .= "\n[ " . $file[0] . " ]\n";
            $phpdocDir .= $phpdocFile;
            $nFiles++;
        }
        $nFilesTotal++;
    }
    return $phpdocDir;
}

function getPhpDocForFile($fileName)
{
    global $nClasses, $nClassesTotal;

    $phpdoc = "";
    $file = str_replace("\r", "", str_replace("\t", "    ", file_get_contents($fileName, true)));
    $classes = match('#\n(?:abstract )?class (?<name>\w+) extends .+\{(?<content>.+)\n\}(\n|$)#', $file);

    foreach ($classes as &$class) {
        $gets = match(
            '#\* @return (?<type>\w+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
            '[\s\n]{2,}public function (?<kind>get)(?<name>\w+)\((?:,? ?\$\w+ ?= ?[^,]+)*\)#',
            $class['content']);
        $sets = match(
            '#\* @param (?<type>\w+) \$\w+(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
            '[\s\n]{2,}public function (?<kind>set)(?<name>\w+)\(\$\w+(?:, ?\$\w+ ?= ?[^,]+)*\)#',
            $class['content']);
        $acrs = array_merge($gets, $sets);
        //print_r($acrs); continue;

        $props = array();
        foreach ($acrs as &$acr) {
            $acr['name'] = camelCase($acr['name']);
            $acr['comment'] = trim(preg_replace('#(^|\n)\s+\*\s?#', '$1 * ', $acr['comment']));
            $props[$acr['name']][$acr['kind']] = array(
                'type' => $acr['type'],
                'comment' => fixSentence($acr['comment']),
            );
        }

        /*foreach ($props as $propName => &$prop) // I don't like write-only props...
            if (!isset($prop['get']))
                unset($props[$propName]);*/

        if (count($props) > 0) {
            $phpdoc .= "\n" . $class['name'] . ":\n";
            $phpdoc .= " *\n";
            foreach ($props as $propName => &$prop) {
                $phpdoc .= ' * @';
                /*if (isset($prop['get']) && isset($prop['set'])) // Few IDEs support complex syntax
                    $phpdoc .= 'property';
                elseif (isset($prop['get']))
                    $phpdoc .= 'property-read';
                elseif (isset($prop['set']))
                    $phpdoc .= 'property-write';*/
                $phpdoc .= 'property';
                $phpdoc .= ' ' . getPropParam($prop, 'type') . " $$propName " . getPropParam($prop, 'comment') . "\n";
            }
            $phpdoc .= " *\n";
            $nClasses++;
        }
        $nClassesTotal++;
    }
    return $phpdoc;
}

function match($pattern, $subject)
{
    $sets = array();
    preg_match_all($pattern . 'suU', $subject, $sets, PREG_SET_ORDER);
    foreach ($sets as &$set)
        foreach ($set as $i => $match)
            if (is_numeric($i) /*&& $i != 0*/)
                unset($set[$i]);
    return $sets;
}

function camelCase($str)
{
    return strtolower(substr($str, 0, 1)) . substr($str, 1);
}

function fixSentence($str)
{
    if ($str == '')
        return '';
    return strtoupper(substr($str, 0, 1)) . substr($str, 1) . ($str[strlen($str) - 1] != '.' ? '.' : '');
}

function getPropParam($prop, $param)
{
    return isset($prop['get']) ? $prop['get'][$param] : $prop['set'][$param];
}

?>
