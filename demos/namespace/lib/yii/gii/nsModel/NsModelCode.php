<?php

Yii::import('system.gii.generators.model.ModelCode');

class NsModelCode extends ModelCode
{
    public $dbName;

    public function init()
    {
        parent::init();

        $this->modelPath = 'lib.models';
        $this->baseClass = 'lib\models\BaseActiveRecord';
    }

    public function requiredTemplates()
    {
        return array(
            'model-dao.php',
            'model-domain.php',
        );
    }

    public function rules()
    {
        $arrRule = parent::rules();
        $arrRule[] = array('dbName', 'filter', 'filter'=>'trim');
        $arrRule[] = array('dbName', 'required');
        $arrRule[] = array('dbName', 'sticky');
        return $arrRule;
    }

    public function prepare()
    {
        if(($pos=strrpos($this->tableName,'.'))!==false)
        {
            $schema=substr($this->tableName,0,$pos);
            $tableName=substr($this->tableName,$pos+1);
        }
        else
        {
            $schema='';
            $tableName=$this->tableName;
        }
        if($tableName[strlen($tableName)-1]==='*')
        {
            $tables=Yii::app()->{$this->connectionId}->schema->getTables($schema);
            if($this->tablePrefix!='')
            {
                foreach($tables as $i=>$table)
                {
                    if(strpos($table->name,$this->tablePrefix)!==0)
                        unset($tables[$i]);
                }
            }
        }
        else
            $tables=array($this->getTableSchema($this->tableName));

        $this->files=array();
        $templatePath=$this->templatePath;
        $this->relations=$this->generateRelations();

        $path = $this->modelPath.'.'.$this->dbName;

        foreach($tables as $table)
        {
            $tableName = $this->removePrefix($table->name);

            $domainClassName = $this->generateClassName($table->name);
            $domainNamespace = str_replace('.', '\\', $path).'\\domain';
            $daoClassName    = '_'.$domainClassName;
            $daoNamespace    = str_replace('.', '\\', $path).'\\dao';

            $daoCodeFile = new CCodeFile(
                Yii::getPathOfAlias($path).'/dao/'.$daoClassName.'.php',
                $this->render($templatePath.'/model-dao.php', array(
                    'tableName'      => $schema==='' ? $tableName : $schema.'.'.$tableName,
                    'modelNamespace' => $daoNamespace,
                    'modelClass'     => $daoClassName,
                    'columns'        => $table->columns,
                    'labels'         => $this->generateLabels($table),
                    'rules'          => $this->generateRules($table),
                    'relations'      => isset($this->relations[$daoClassName]) ? $this->relations[$daoClassName] : array(),
                    'connectionId'   => $this->connectionId,
                ))
            );
            $this->files[]= $daoCodeFile;

            if($daoCodeFile->operation == CCodeFile::OP_NEW)
            {
                $domainCodeFile = new CCodeFile(
                    Yii::getPathOfAlias($path).'/domain/'.$domainClassName.'.php',
                    $this->render($templatePath.'/model-domain.php', array(
                        'modelNamespace' => $domainNamespace,
                        'modelClass'     => $domainClassName,
                        'superClass'     => $daoNamespace.'\\'.$daoClassName,
                    ))
                );
                $this->files[] = $domainCodeFile;
            }
        }
    }
}
