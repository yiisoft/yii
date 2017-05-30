<?php

namespace lib\models;

class BaseActiveRecord extends \CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function model($className=null)
    {
        if($className === null)
            $className = get_called_class();

        return \CActiveRecord::model($className);
    }

    /** @var array */
    private $mapAlias;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAlias($name)
    {
        if(array_key_exists($name, $this->mapAlias) == true)
            return $this->mapAlias[$name];

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function instantiate($attributes)
    {
        /** @var self $model */
        $model = parent::instantiate($attributes);

        $map = array();
        foreach($attributes as $name => $value)
        {
            if(substr($name, 0 ,2) == '$$')
            {
                $map[$name] = $value;
            }
        }

        if(empty($map) == false)
        {
            $model->mapAlias = $map;
        }

        return $model;
    }
}
