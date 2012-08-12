<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
if (is_array($this->tableSchema->primaryKey)){
	/* for composite primary keys, id is separated by the "|" character */
	$strFields = '';
	foreach($this->tableSchema->primaryKey as $nameField){
		$strFields .= '$model->'.$nameField.'.\'|\'.';
	}
	if ($strFields){
		$strFields = substr($strFields, 0, -5);
	}
}else{
	$strFields = '$model->'.$this->tableSchema->primaryKey;
}
?>
<?php echo "<?php\n"; ?>
/* @var $this <?php echo $this->getControllerClass(); ?> */
/* @var $model <?php echo $this->getModelClass(); ?> */

<?php
$nameColumn=$this->guessNameColumn($this->tableSchema->columns);
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	\$model->{$nameColumn}=>array('view','id'=>$strFields),
	'Update',
);\n";
?>

$this->menu=array(
	array('label'=>'List <?php echo $this->modelClass; ?>', 'url'=>array('index')),
	array('label'=>'Create <?php echo $this->modelClass; ?>', 'url'=>array('create')),
	array('label'=>'View <?php echo $this->modelClass; ?>', 'url'=>array('view', 'id'=><?php echo $strFields; ?>)),
	array('label'=>'Manage <?php echo $this->modelClass; ?>', 'url'=>array('admin')),
);
?>

<h1>Update <?php echo $this->modelClass." <?php echo $strFields; ?>"; ?></h1>

<?php echo "<?php echo \$this->renderPartial('_form', array('model'=>\$model)); ?>"; ?>