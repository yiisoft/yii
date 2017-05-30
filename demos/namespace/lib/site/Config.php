<?php

namespace lib\site;

final class Config
{
    /**
     * @param array  $arrFile
     *
     * @return array
     */
    public static function load($arrFile)
    {
        $map = array();

        foreach($arrFile as $file)
        {
            if(file_exists($file) == false)
                continue;

            $mapFile = include($file);
            if(is_array($mapFile) == false)
                continue;

            $map = array_replace_recursive($map, $mapFile);
        }

        return $map;
    }
}
